<?php

namespace App\Services\ZohoInventory;

use App\Services\ZohoInventory\InventoryConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesOrders extends InventoryConnection
{
    /**
     * @param $data
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function salesOrderCreateRequest($data)
    {
        $accessToken = $this->getZohoInventoryAccessToken();
        // Create new Sales Order
        $url = env('ZOHO_INVENTORY_API_BASE_URL')
            . '/salesorders?organization_id='
            . env('ZOHO_INVENTORY_ORGANIZATION_ID');

        $createResponse = Http::withToken($accessToken)->post($url, [
            'contact_id' => $data['contact_id'],
            'customer_id' => $data['contact_id'],
            "salesorder_number" => $data['order_number'],
            'date' => $data['order_date'],
            'line_items' => $data['items'],
        ]);

//        Log::info(' --- RESPONSE after create order $createResponse ----------------');
//        Log::info($createResponse);

        return [
            'code' => $createResponse['code'] ?? '',
            'message' => $createResponse['message'] ?? '',
            'salesorder' => $createResponse['salesorder'] ?? ''
        ];
    }

    /**
     * @return string
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function nextOrderNumberRequest(): string
    {
        $accessToken = $this->getZohoInventoryAccessToken();

        $response = Http::withToken($accessToken)
            ->get(env('ZOHO_INVENTORY_API_BASE_URL') . '/salesorders', [
                'organization_id' => env('ZOHO_INVENTORY_ORGANIZATION_ID'),
                'sort_by' => 'created_time',
                'sort_order' => 'D',
                'limit' => 1
            ]);

        $latestOrder = $response['salesorders'][0] ?? null;
        $nextOrderNumber = 'SO-00001'; // default

        if ($latestOrder && preg_match('/SO-(\d+)/', $latestOrder['salesorder_number'], $matches)) {
            $next = (int)$matches[1] + 1;
            $nextOrderNumber = 'SO-' . str_pad($next, 5, '0', STR_PAD_LEFT);
        }

        return $nextOrderNumber;
    }
}
