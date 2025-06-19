<?php

namespace App\Services\ZohoInventory;

use App\Services\ZohoInventory\InventoryConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Products extends InventoryConnection
{
    /**
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getProducts(): array
    {
        $accessToken = $this->getZohoInventoryAccessToken();

        $url = env('ZOHO_INVENTORY_API_BASE_URL')
            . '/items?organization_id='
            . env('ZOHO_INVENTORY_ORGANIZATION_ID');
        $productResponse = Http::withToken($accessToken)->get($url);

        if (!$productResponse->ok()) {
            throw new \Exception('Failed to get fields');
        }

        $products = $productResponse->json('items');
        if (!empty($products)) {
            return collect($products)
                ->toArray();
        }

        return [];
    }
}
