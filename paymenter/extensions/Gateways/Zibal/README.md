# Zibal gateway for Paymenter

A minimal Paymenter gateway extension for [Zibal](https://help.zibal.ir). It sends the
customer to the Zibal hosted checkout and verifies the result on return.

## Configuration

| Field | Meaning |
|---|---|
| Merchant ID | Merchant token from the Zibal dashboard. Stored encrypted. |
| Sandbox mode | Uses Zibal's shared test merchant (`zibal`) instead of the configured one. |

Invoices must use **IRR** as the currency; Zibal amounts are whole Rials, and the
extension rounds the invoice total to an integer before submitting it.

## Network

The gateway calls `https://gateway.zibal.ir` directly from the host running
Paymenter. Do not set `HTTP_PROXY` / `HTTPS_PROXY` for this service — Zibal matches
the calling IP address, so proxied requests will be rejected.

## Flow

1. `pay()` POSTs to `/v1/request` with merchant, amount, callback URL and order ID,
   then redirects to `https://gateway.zibal.ir/start/<trackId>`.
2. Zibal returns the customer to `GET /extensions/gateways/zibal/callback/{invoice}`
   with `success` and `trackId`.
3. `callback()` POSTs to `/v1/verify`, records the payment via `ExtensionHelper`,
   and redirects back to the invoice. Duplicate callbacks are ignored by checking
   for an existing transaction with the same track ID.
