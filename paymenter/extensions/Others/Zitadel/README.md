# SSO login extension

Replaces the local login form with an external OIDC provider using the authorization
code flow with PKCE.

## Provider setup

Create a web application in your OIDC provider with:

- Redirect URI: `https://<your-host>/auth/zitadel/callback`
- Grant type: Authorization Code
- Response type: Code
- Scopes: `openid profile email`

## Extension settings

| Field | Meaning |
|---|---|
| Issuer URL | Base URL of the provider; discovery is read from `/.well-known/openid-configuration`. |
| Client ID / secret | Credentials of the OIDC application. The secret is stored encrypted. |
| Create unknown users | Creates a local account on first successful sign-in, using the email claim. |
| Force SSO | Hides the local email/password form on the login page. |

On callback the ID token is verified against the provider's JWKS, and `iss`, `aud`
and `nonce` are checked before a session is created.
