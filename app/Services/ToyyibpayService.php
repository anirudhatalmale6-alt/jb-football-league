<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ToyyibpayService
{
    private $secretKey;
    private $categoryCode;
    private $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.toyyibpay.secret_key');
        $this->categoryCode = config('services.toyyibpay.category_code');
        $this->baseUrl = config('services.toyyibpay.sandbox')
            ? 'https://dev.toyyibpay.com'
            : 'https://toyyibpay.com';
    }

    public function createBill($data)
    {
        $billData = [
            'userSecretKey' => $this->secretKey,
            'categoryCode' => $this->categoryCode,
            'billName' => $data['name'],
            'billDescription' => $data['description'],
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $data['amount'] * 100,
            'billReturnUrl' => $data['return_url'],
            'billCallbackUrl' => $data['callback_url'],
            'billExternalReferenceNo' => $data['reference'],
            'billTo' => $data['payer_name'],
            'billEmail' => $data['payer_email'],
            'billPhone' => $data['payer_phone'] ?? '',
        ];

        $response = Http::asForm()->post($this->baseUrl . '/index.php/api/createBill', $billData);

        if ($response->successful()) {
            $result = $response->json();
            if (isset($result[0]['BillCode'])) {
                return [
                    'success' => true,
                    'billcode' => $result[0]['BillCode'],
                    'url' => $this->baseUrl . '/' . $result[0]['BillCode'],
                ];
            }
        }

        return ['success' => false, 'error' => 'Failed to create bill'];
    }

    public function getBillTransactions($billCode)
    {
        $response = Http::asForm()->post($this->baseUrl . '/index.php/api/getBillTransactions', [
            'billCode' => $billCode,
        ]);

        return $response->successful() ? $response->json() : null;
    }
}
