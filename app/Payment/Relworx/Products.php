<?php

namespace App\Payment\Relworx;

use App\Models\Relworx\Product;
use App\Utils\Logger;
use Illuminate\Support\Facades\Http;


class Products
{
    private const PRODUCT_URL = "https://payments.relworx.com/api/products";
    private const PRICE_LIST_URL = "https://payments.relworx.com/api/products/price-list";
    private const CHOICE_LIST_URL = "https://payments.relworx.com/api/products/choice-list";
    private const VALIDATE_PRODUCT_URL = "https://payments.relworx.com/api/products/validate";
    private const PURCHASE_PRODUCT_URL = "https://payments.relworx.com/api/products/purchase";
    private const DISBURSE_FUNDS_URL = "https://payments.relworx.com/api/mobile-money/send-payment";
    public string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.relworx.api_key');
    }

    /**
     * Get all products from Relworx
     * 
     * @return array
     */
    public function getProducts()
    {
        $response = Http::asJson()->withHeaders(
            [
                'Accept' => 'application/vnd.relworx.v2',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
        )->get(self::PRODUCT_URL)
            ->json();
        
        Logger::info($response);

        if (isset($response['products']) && is_array($response['products'])) {
            foreach ($response['products'] as $product) {
                Product::updateOrCreate(
                    [
                        'code' => $product['code'],
                    ],
                    [
                        'name' => $product['name'],
                        'category' => $product['category'],
                        'has_price_list' => $product['has_price_list'],
                        'has_choice_list' => $product['has_choice_list'],
                        'billable' => $product['billable']
                    ]
                );
            }
        }
    }

    /**
     * Get the price list for a product
     * 
     * @param string $code
     * @return array
     */
    public function getPriceList(string $code)
    {
        $response = Http::asJson()->withHeaders(
            [
                'Accept' => 'application/vnd.relworx.v2',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
        )->get(self::PRICE_LIST_URL . '?code=' . $code)
            ->json();

        Logger::info($response);
        
        return $response;
    }

    /**
     * Get the choice list for a product
     * 
     * @param string $code
     * @return array
     */
    public function getChoiceList(string $code)
    {
        $response = Http::asJson()->withHeaders(
            [
                'Accept' => 'application/vnd.relworx.v2',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
        )->get(self::CHOICE_LIST_URL . '?code=' . $code)
            ->json();

        Logger::info($response);

        return $response;
    }

    /**
     * Validate a product
     * 
     * @param array $params
     * @return array
     */
    public function validateProduct(array $params)
    {
        $response = Http::asJson()->withHeaders(
            [
                'Accept' => 'application/vnd.relworx.v2',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
        )->post(self::VALIDATE_PRODUCT_URL, [
            'account_no' => $params['account_no'],
            'reference' => $params['reference'],
            'msisdn' => $params['msisdn'],
            'amount' => $params['amount'],
            'product_code' => $params['product_code'],
            'contact_phone' => $params['contact_phone'],
            'location_id' => $params['location_id']
        ])->json();
        Logger::info($response);
        return $response;
    }

    /**
     * Purchase a product
     * 
     * @param array $params
     * @return array
     */
    public function purchaseProduct(array $params)
    {
        $response = Http::asJson()->withHeaders(
            [
                'Accept' => 'application/vnd.relworx.v2',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
        )->post(self::PURCHASE_PRODUCT_URL, $params)->json();
        Logger::info($response);
        return $response;
    }

      /**
     * Disburse funds
     *
     * @param string $reference
     * @param string $msisdn
     * @param int $amount
     * @return array
     */
    public function disburseFunds(array $params)
    {
        $params = [
            'account_no' => $params['account_no'],
            'reference' => $params['reference'],
            'msisdn' => $params['msisdn'],
            'currency' => "UGX",
            'amount' => $params['amount'],
            'description' => "Disbursement"
        ];
        return Http::asJson()->withHeaders(
            [
                'Accept' => 'application/vnd.relworx.v2',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
        )->post(self::DISBURSE_FUNDS_URL, $params)->json();
        Logger::info($response);
        return $response;
    }
}