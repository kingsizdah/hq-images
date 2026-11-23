<?php

namespace Paymenter\Extensions\Others\Zitadel;

use App\Actions\Auth\Login;
use App\Attributes\ExtensionMeta;
use App\Classes\Extension\Extension;
use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

#[ExtensionMeta(
    name: 'Zitadel SSO',
    description: 'Sign in with an external OIDC provider using the authorization code flow with PKCE.',
    version: '1.0.0',
    author: 'Community',
    icon: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHJlY3Qgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzRmNDZlNSIvPjxwYXRoIGQ9Ik03IDEyaDEwTTEyIDd2MTAiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48L3N2Zz4='
)]
class Zitadel extends Extension
{
    private const CACHE_KEY = 'zitadel_oidc_discovery';

    public function boot()
    {
        View::addNamespace('zitadel-sso', __DIR__ . '/views');
        require __DIR__ . '/routes.php';

        Event::listen('auth.login', function () {
            return [[
                'view' => view('zitadel-sso::button', [
                    'forceSso' => (bool) $this->config('force_sso'),
                ])->render(),
                'priority' => 10,
            ]];
        });

        Event::listen('footer', function () {
            if (! $this->config('issuer') || ! $this->config('client_id')) {
                return [];
            }

            return [[
                'view' => view('zitadel-sso::logout-hook')->render(),
                'priority' => 10,
            ]];
        });
    }

    /**
     * @param  array  $values
     * @return array
     */
    public function getConfig($values = [])
    {
        return [
            [
                'name' => 'issuer',
                'label' => 'Issuer URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://auth.example.com',
                'description' => 'Base URL of the OIDC provider.',
            ],
            [
                'name' => 'client_id',
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'client_secret',
                'label' => 'Client secret',
                'type' => 'text',
                'encrypted' => true,
                'required' => true,
            ],
            [
                'name' => 'auto_create',
                'label' => 'Create unknown users',
                'type' => 'checkbox',
                'default' => true,
                'description' => 'Create a local account on first successful sign-in.',
            ],
            [
                'name' => 'force_sso',
                'label' => 'Force SSO',
                'type' => 'checkbox',
                'default' => false,
                'description' => 'Hide the local email/password form on the login page.',
            ],
        ];
    }

    private function discovery(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addDay(), function () {
            $issuer = rtrim((string) $this->config('issuer'), '/');
            $response = Http::timeout(15)->get($issuer . '/.well-known/openid-configuration');

            if (! $response->successful() || ! is_array($response->json())) {
                throw new \RuntimeException('Could not load OIDC discovery document.');
            }

            return $response->json();
        });
    }

    public function redirect()
    {
        try {
            $discovery = $this->discovery();
        } catch (\Throwable $e) {
            return $this->loginError('SSO is not configured correctly.');
        }

        // Stateless flow: the state carries a signed nonce, and the PKCE verifier
        // is derived from it. Nothing has to survive in the PHP session.
        $nonce = Str::random(32);
        $signature = hash_hmac('sha256', $nonce, (string) config('app.key'));
        $state = $nonce . '.' . $signature;
        $verifier = hash_hmac('sha256', 'verifier:' . $nonce, (string) config('app.key'));
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $query = http_build_query([
            'client_id' => $this->config('client_id'),
            'redirect_uri' => route('zitadel.callback'),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ]);

        return redirect($discovery['authorization_endpoint'] . '?' . $query);
    }

    public function callback(Request $request)
    {
        if (! $request->filled('code') || ! $request->filled('state')) {
            return $this->loginError('Invalid SSO response.');
        }

        $parts = explode('.', (string) $request->input('state'), 2);
        if (count($parts) !== 2 || ! hash_equals(
            hash_hmac('sha256', $parts[0], (string) config('app.key')),
            $parts[1]
        )) {
            return $this->loginError('Invalid SSO state.');
        }
        $nonce = $parts[0];
        $verifier = hash_hmac('sha256', 'verifier:' . $nonce, (string) config('app.key'));

        $discovery = $this->discovery();

        $response = Http::asForm()->timeout(20)->post($discovery['token_endpoint'], [
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
            'redirect_uri' => route('zitadel.callback'),
            'client_id' => $this->config('client_id'),
            'client_secret' => $this->config('client_secret'),
            'code_verifier' => $verifier,
        ]);

        if (! $response->successful()) {
            $body = $response->json();
            Log::warning('Zitadel SSO: token exchange failed', [
                'status' => $response->status(),
                'error' => $body['error'] ?? null,
                'description' => $body['error_description'] ?? null,
            ]);

            return $this->loginError('SSO token exchange failed.');
        }

        $tokens = $response->json();
        Session::put('zitadel_id_token', $tokens['id_token'] ?? null);

        try {
            $jwks = Http::timeout(15)->get($discovery['jwks_uri'])->json();
            $headers = null; // firebase/php-jwt v7 takes this by reference
            $claims = JWT::decode($tokens['id_token'], JWK::parseKeySet($jwks), $headers);
        } catch (\Throwable $e) {
            Log::warning('Zitadel SSO: ID token verification failed', ['message' => $e->getMessage()]);

            return $this->loginError('Invalid SSO identity token.');
        }

        $issuer = rtrim((string) $this->config('issuer'), '/');
        if (($claims->iss ?? null) !== $issuer || ($claims->nonce ?? null) !== $nonce) {
            Log::warning('Zitadel SSO: iss/nonce mismatch', [
                'expected_iss' => $issuer,
                'actual_iss' => $claims->iss ?? null,
                'nonce_match' => ($claims->nonce ?? null) === $nonce,
            ]);

            return $this->loginError('Invalid SSO identity token.');
        }

        $audience = is_array($claims->aud ?? null) ? $claims->aud : [$claims->aud ?? null];
        if (! in_array($this->config('client_id'), $audience, true)) {
            return $this->loginError('Invalid SSO audience.');
        }

        $email = strtolower((string) ($claims->email ?? ''));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Zitadel SSO: no email claim', ['claims' => array_keys((array) $claims)]);

            return $this->loginError('SSO response did not include an email address.');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            if (! $this->config('auto_create')) {
                return $this->loginError('This account is not registered.');
            }

            $user = User::create([
                'first_name' => $claims->given_name ?? 'User',
                'last_name' => $claims->family_name ?? '',
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);
        }

        if ($user->tfa_secret) {
            Session::put('2fa', [
                'user_id' => $user->id,
                'remember' => true,
                'expires' => now()->addMinutes(5),
            ]);

            return redirect()->route('2fa');
        }

        (new Login)->execute($user, true);

        return redirect()->intended(route('home'));
    }

    private function loginError(string $message)
    {
        Log::warning('Zitadel SSO: ' . $message);

        return redirect()->route('login', [
            'sso_error' => 1,
            'sso_message' => $message,
        ]);
    }

    public function logout()
    {
        // Capture before the local logout invalidates the session.
        $idToken = Session::get('zitadel_id_token');

        (new \App\Actions\Auth\Logout)->execute();

        // Not configured (or native login) — keep the original logout behaviour.
        if (! $idToken || ! $this->config('issuer') || ! $this->config('client_id')) {
            return redirect('/');
        }

        try {
            $endpoint = $this->discovery()['end_session_endpoint'] ?? null;
        } catch (\Throwable $e) {
            return redirect('/');
        }

        if (! $endpoint) {
            return redirect('/');
        }

        $query = http_build_query([
            'id_token_hint' => $idToken,
            'post_logout_redirect_uri' => route('home'),
            'client_id' => $this->config('client_id'),
        ]);

        return redirect($endpoint . '?' . $query);
    }
}
