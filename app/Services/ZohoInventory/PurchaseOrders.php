<?php

namespace App\Services\ZohoInventory;

use App\Services\ZohoInventory\InventoryConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurchaseOrders extends InventoryConnection
{
    /**
     * @return string
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function nextPurchaseOrderNumberRequest(): string
    {
        $accessToken = $this->getZohoInventoryAccessToken();

        $response = Http::withToken($accessToken)
            ->get(env('ZOHO_INVENTORY_API_BASE_URL') . '/purchaseorders', [
                'organization_id' => env('ZOHO_INVENTORY_ORGANIZATION_ID'),
                'sort_by' => 'created_time',
                'sort_order' => 'D',
                'limit' => 1
            ]);

        $latestPurchaseOrder = $response['purchaseorders'][0] ?? null;
        $nextPurchaseOrderNumber = 'PO-00001'; // default
        if ($latestPurchaseOrder
            && preg_match('/PO-(\d+)/', $latestPurchaseOrder['purchaseorder_number'], $matches)
        ) {
            $next = (int)$matches[1] + 1;
            $nextPurchaseOrderNumber = 'PO-' . str_pad($next, 5, '0', STR_PAD_LEFT);
        }

        return $nextPurchaseOrderNumber;
    }

    /**
     * @param $data
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function purchaseOrderCreateRequest($data)
    {
        $accessToken = $this->getZohoInventoryAccessToken();
        // Create new Purchase Order
        $url = env('ZOHO_INVENTORY_API_BASE_URL')
            . '/purchaseorders?organization_id='
            . env('ZOHO_INVENTORY_ORGANIZATION_ID');

        $createResponse = Http::withToken($accessToken)->post($url, [
            'vendor_id' => $data['vendor_id'],
            'delivery_customer_id' => $data['delivery_customer_id'],
            'delivery_org_address_id' => $data['delivery_org_address_id'],
            "purchaseorder_number" => $data['purchaseorder_number'],
            'date' => $data['date'],
            'line_items' => $data['items'],
        ]);

        return [
            'code' => $createResponse['code'] ?? '',
            'message' => $createResponse['message'] ?? '',
            'purchaseorder' => $createResponse['purchaseorder'] ?? ''
        ];
    }

//    public function getPurchaseOrderByIdRequest()
//    {
//        $accessToken = $this->getZohoInventoryAccessToken();
//
//        // 795826000000073055
//        // 795826000000077168    customer
//        // 795826000000077194    organiz
//
//
//        $response = Http::withToken($accessToken)
//            ->get(env('ZOHO_INVENTORY_API_BASE_URL') . '/purchaseorders'
//                . '/795826000000077194' . '?organization_id='
//            . env('ZOHO_INVENTORY_ORGANIZATION_ID'));
//
//        die($response);
//
//        return $nextPurchaseOrderNumber;
//    }
}
