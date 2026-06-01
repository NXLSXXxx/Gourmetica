<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class CulqiService
{
    protected $publicKey;
    protected $privateKey;
    protected $baseUrl = 'https://api.culqi.com/v2';

    public function __construct()
    {
        $this->publicKey = Setting::get('culqi_public_key');
        $this->privateKey = Setting::get('culqi_private_key');
    }

    public function createCharge($token, $amount, $email, $orderId)
    {
        $response = Http::withToken($this->privateKey)
            ->post($this->baseUrl . '/charges', [
                'amount' => $amount * 100, // Culqi uses cents
                'currency_code' => 'PEN',
                'email' => $email,
                'source_id' => $token,
                'description' => 'Pedido #' . $orderId . ' en Gourmetica',
                'metadata' => [
                    'order_id' => $orderId
                ]
            ]);

        return $response->json();
    }
}
