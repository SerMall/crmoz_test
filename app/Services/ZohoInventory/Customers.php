<?php

namespace App\Services\ZohoInventory;

use App\Services\ZohoInventory\InventoryConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Customers extends InventoryConnection
{
    /**
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    private function getAllContacts(): array
    {
        $accessToken = $this->getZohoInventoryAccessToken();

        $url = env('ZOHO_INVENTORY_API_BASE_URL')
            . '/contacts?organization_id='
            . env('ZOHO_INVENTORY_ORGANIZATION_ID');
        $customerResponse = Http::withToken($accessToken)->get($url);

        if (!$customerResponse->ok()) {
            throw new \Exception('Failed to get fields');
        }

        return $customerResponse->json('contacts');
    }

    /**
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getCustomers(): array
    {
        $contacts = $this->getAllContacts();
        if (!empty($contacts)) {
            return collect($contacts)
                ->filter(fn($contact) => ($contact['contact_type'] === 'customer'))
                ->values()
                ->toArray();
        }
        return [];
    }

    /**
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getVendors(): array
    {
        $contacts = $this->getAllContacts();
        if (!empty($contacts)) {
            return collect($contacts)
                ->filter(fn($contact) => ($contact['contact_type'] === 'vendor'))
                ->values()
                ->toArray();
        }
        return [];
    }

    /**
     * @param $contactId
     * @return array|mixed
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getContactAddressIdRequest($contactId)
    {
//        $contactId = '795826000000065026';
        $accessToken = $this->getZohoInventoryAccessToken();

        $url = env('ZOHO_INVENTORY_API_BASE_URL')
            . '/contacts/' . $contactId . '/address?organization_id='
            . env('ZOHO_INVENTORY_ORGANIZATION_ID');
        $customerAddressResponse = Http::withToken($accessToken)->get($url);

        if (!$customerAddressResponse->ok()) {
            throw new \Exception('Failed to get fields');
        }

        return $customerAddressResponse->json('contacts');
    }

    /**
     * @param $data
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function customerCreateRequest($data)
    {
        $accessToken = $this->getZohoInventoryAccessToken();
        $url = env('ZOHO_INVENTORY_API_BASE_URL')
            . '/contacts?organization_id='
            . env('ZOHO_INVENTORY_ORGANIZATION_ID');

        $createResponse = Http::withToken($accessToken)->post($url, [
            'contact_name' => $data['contact_name'],
            'customer_sub_type' => $data['customer_sub_type'],
            'company_name' => $data['company_name'],
            'contact_persons' => [
                [
                    'first_name' => $data['contact_persons'][0]['first_name'],
                    'last_name' => $data['contact_persons'][0]['last_name'],
                    'email' => $data['contact_persons'][0]['email'],

                ]
            ]
        ]);

        return [
            'code' => $createResponse['code'] ?? false,
            'message' => $createResponse['message'] ?? '',
            'contact' => $createResponse['contact'] ?? '',
        ];
    }

    /**
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getOrganization()
    {
        $accessToken = $this->getZohoInventoryAccessToken();

        $url = env('ZOHO_INVENTORY_API_BASE_URL')
            . '/organizations/' . env('ZOHO_INVENTORY_ORGANIZATION_ID') . '?organization_id='
            . env('ZOHO_INVENTORY_ORGANIZATION_ID');
        $organizationResponse = Http::withToken($accessToken)->get($url);

        if (!$organizationResponse->ok()) {
            throw new \Exception('Failed to get fields');
        }

        return $organizationResponse->json('organization');
    }

    public function getOrganizationAddressRequest()
    {
        $organization = $this->getOrganization();

        return $organization['address'];
    }

//    public function getCustomersByIdRequest(): array
//    {
//        $accessToken = $this->getZohoInventoryAccessToken();
////795826000000076158 - individ
////795826000000076140 - buisy
//        $url = env('ZOHO_INVENTORY_API_BASE_URL')
//            . '/contacts/' . '795826000000076158' . '?organization_id='
//            . env('ZOHO_INVENTORY_ORGANIZATION_ID');
//        $customerResponse = Http::withToken($accessToken)->get($url);
//
//        die($customerResponse);
//
//        if (!$customerResponse->ok()) {
//            throw new \Exception('Failed to get fields');
//        }
//
//        return $customerResponse->json('contacts');
//    }


}
