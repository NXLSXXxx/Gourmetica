<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IzipayService
{
    protected string $shopId;
    protected string $password;
    protected string $publicKey;
    protected string $hmacSha256;
    protected string $baseUrl;

    public function __construct()
    {
        $this->shopId     = Setting::get('izipay_shop_id', env('IZIPAY_SHOP_ID', ''));
        $this->password   = Setting::get('izipay_password', env('IZIPAY_PASSWORD', ''));
        $this->publicKey  = Setting::get('izipay_public_key', env('IZIPAY_PUBLIC_KEY', ''));
        $this->hmacSha256 = Setting::get('izipay_hmac_sha256', env('IZIPAY_HMAC_SHA256', ''));

        // Sandbox vs Production URL
        $isSandbox = Setting::get('izipay_sandbox', env('IZIPAY_SANDBOX', '1')) === '1';
        $this->baseUrl = 'https://api.micuentaweb.pe';
    }

    /**
     * Genera un formToken para el checkout embebido de IZIPAY.
     *
     * @param float  $amount    Monto total en soles (ej: 29.90)
     * @param string $email     Correo del comprador
     * @param int    $orderId   ID del pedido (para referencia)
     * @return array ['success' => bool, 'formToken' => string|null, 'message' => string]
     */
    public function createFormToken(float $amount, string $email, int $orderId): array
    {
        if (empty($this->shopId) || empty($this->password)) {
            return [
                'success'   => false,
                'formToken' => null,
                'message'   => 'Credenciales IZIPAY no configuradas.',
            ];
        }

        // IZIPAY maneja montos en céntimos (1 sol = 100)
        $amountInCents = (int) round($amount * 100);

        $payload = [
            'amount'          => $amountInCents,
            'currency'        => 'PEN',
            'orderId'         => 'GOURMETICA-' . $orderId,
            'customer'        => [
                'email' => $email,
            ],
        ];

        try {
            $response = Http::withBasicAuth($this->shopId, $this->password)
                ->timeout(20)
                ->post($this->baseUrl . '/api-payment/V4/Charge/CreatePayment', $payload);

            $body = $response->json();

            if ($response->successful() && isset($body['answer']['formToken'])) {
                return [
                    'success'   => true,
                    'formToken' => $body['answer']['formToken'],
                    'message'   => 'OK',
                ];
            }

            $errorMsg = $body['answer']['errorMessage']
                ?? $body['errorMessage']
                ?? 'Error desconocido al generar token IZIPAY.';

            Log::error('IzipayService::createFormToken error', [
                'status'   => $response->status(),
                'response' => $body,
            ]);

            return [
                'success'   => false,
                'formToken' => null,
                'message'   => $errorMsg,
            ];
        } catch (\Throwable $e) {
            Log::error('IzipayService::createFormToken exception', ['error' => $e->getMessage()]);

            return [
                'success'   => false,
                'formToken' => null,
                'message'   => 'No se pudo conectar con IZIPAY: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica la firma HMAC-SHA256 del IPN/respuesta de IZIPAY.
     * Protege contra falsificaciones en el callback del pago.
     *
     * @param array  $data      Datos recibidos del IPN (kr-answer, etc.)
     * @param string $signature La firma recibida (kr-hash)
     * @return bool
     */
    public function verifySignature(array $data, string $signature): bool
    {
        if (empty($this->hmacSha256)) {
            return false;
        }

        $content = '';
        ksort($data);
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'kr-')) {
                $content .= $value;
            }
        }

        $expectedSignature = hash_hmac('sha256', $content, $this->hmacSha256);
        return hash_equals($expectedSignature, $signature);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}
