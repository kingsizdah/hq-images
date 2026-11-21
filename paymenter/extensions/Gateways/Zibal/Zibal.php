<?php

namespace Paymenter\Extensions\Gateways\Zibal;

use App\Attributes\ExtensionMeta;
use App\Classes\Extension\Gateway;
use App\Helpers\ExtensionHelper;
use App\Models\Invoice;
use App\Models\InvoiceTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

#[ExtensionMeta(
    name: 'Zibal',
    description: 'Accept payments through the Zibal payment gateway.',
    version: '1.0.0',
    author: 'Community',
    url: 'https://help.zibal.ir',
    icon: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHJlY3Qgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzA1OTY2OSIvPjxwYXRoIGQ9Ik02IDEyaDEyTTEyIDZ2MTIiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48L3N2Zz4='
)]
class Zibal extends Gateway
{
    private const REQUEST_URL = 'https://gateway.zibal.ir/v1/request';
    private const VERIFY_URL = 'https://gateway.zibal.ir/v1/verify';
    private const START_URL = 'https://gateway.zibal.ir/start/';

    public function boot()
    {
        require __DIR__ . '/routes.php';
    }

    /**
     * Get the configuration fields shown in the admin panel.
     *
     * @param  array  $values
     * @return array
     */
    public function getConfig($values = [])
    {
        return [
            [
                'name' => 'merchant',
                'label' => 'Merchant ID',
                'type' => 'text',
                'encrypted' => true,
                'required' => true,
            ],
            [
                'name' => 'sandbox',
                'label' => 'Sandbox mode',
                'type' => 'checkbox',
                'required' => false,
                'description' => 'Uses the shared test merchant. No real charge is made.',
            ],
        ];
    }

    private function merchant(): string
    {
        return $this->config('sandbox') ? 'zibal' : (string) $this->config('merchant');
    }

    private function post(string $url, array $payload): array
    {
        // Direct outbound connection. Zibal validates the caller's IP, so this
        // gateway must never be routed through a proxy.
        $response = Http::timeout(30)->acceptJson()->asJson()->post($url, $payload);

        if (! $response->successful()) {
            throw new Exception('Zibal HTTP error: ' . $response->status());
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new Exception('Zibal returned an invalid response.');
        }

        return $json;
    }

    /**
     * Start a payment and return the redirect URL.
     *
     * @param  float  $total
     * @return string
     */
    public function pay(Invoice $invoice, $total)
    {
        $amount = (int) round((float) $total);

        $result = $this->post(self::REQUEST_URL, [
            'merchant' => $this->merchant(),
            'amount' => $amount,
            'callbackUrl' => route('extensions.gateways.zibal.callback', $invoice),
            'orderId' => (string) $invoice->id,
            'description' => 'Invoice #' . $invoice->id,
        ]);

        if (($result['result'] ?? null) !== '100' || empty($result['trackId'])) {
            $message = $result['message'] ?? $result['result'] ?? 'unknown error';

            throw new Exception('Zibal request failed: ' . (is_array($message) ? implode(', ', $message) : $message));
        }

        return self::START_URL . $result['trackId'];
    }

    /**
     * Handle the customer returning from Zibal, verify, and record the payment.
     */
    public function callback(Request $request, Invoice $invoice)
    {
        $trackId = $request->input('trackId');
        $success = $request->input('success');

        if ($success != '1' || ! $trackId) {
            return redirect()->route('invoices.show', $invoice)->with('notification', [
                'type' => 'error',
                'message' => 'Payment was not completed.',
            ]);
        }

        // Avoid recording the same transaction twice if the callback is hit again.
        if (InvoiceTransaction::where('transaction_id', $trackId)->exists()) {
            return redirect()->route('invoices.show', $invoice);
        }

        try {
            $result = $this->post(self::VERIFY_URL, [
                'merchant' => $this->merchant(),
                'trackId' => $trackId,
            ]);
        } catch (Exception $e) {
            return redirect()->route('invoices.show', $invoice)->with('notification', [
                'type' => 'error',
                'message' => 'Payment verification failed.',
            ]);
        }

        if (! in_array($result['result'] ?? null, ['100', '201'], true)) {
            return redirect()->route('invoices.show', $invoice)->with('notification', [
                'type' => 'error',
                'message' => 'Payment verification failed.',
            ]);
        }

        $amount = isset($result['amount']) ? (int) $result['amount'] : (int) round((float) $invoice->total);

        ExtensionHelper::addPayment($invoice->id, 'Zibal', $amount, null, $trackId);

        return redirect()->route('invoices.show', $invoice)->with('notification', [
            'type' => 'success',
            'message' => 'Payment received.',
        ]);
    }
}
