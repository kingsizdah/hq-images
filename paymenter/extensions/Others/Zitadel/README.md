# SSO login extension

Replaces the local login form with an external OIDC provider using the authorization
code flow with PKCE.

## Provider setup

Create a web application in your OIDC provider with:

- Redirect URI: `https://<your-host>/auth/zitadel/callback`
- Post Logout URI: `https://<your-host>/`
- Grant type: Authorization Code
- Response type: Code
- Scopes: `openid profile email`

For single logout, the extension stores the ID token and adds
`GET /auth/zitadel/logout`. It logs out locally, then calls the provider's
`end_session_endpoint` with `id_token_hint` and the post-logout URI. The logout
button is intercepted only once the issuer and client ID are configured; before
that, the built-in local logout remains unchanged.

## Extension settings

| Field | Meaning |
|---|---|
| Issuer URL | Base URL of the provider; discovery is read from `/.well-known/openid-configuration`. |
| Client ID / secret | Credentials of the OIDC application. The secret is stored encrypted. |
| Create unknown users | Creates a local account on first successful sign-in, using the email claim. |
| Force SSO | Hides the local email/password form on the login page. |

On callback the ID token is verified against the provider's JWKS, and `iss`, `aud`
and `nonce` are checked before a session is created.
