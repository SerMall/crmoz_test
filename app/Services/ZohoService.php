<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZohoService
{
    private function getZohoAccessToken()
    {
        // Check token in cached
        if (Cache::has('zoho_access_token')) {
            return Cache::get('zoho_access_token');
        }

        // Refresh token
        $response = Http::asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
            'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
            'client_id' => env('ZOHO_CLIENT_ID'),
            'client_secret' => env('ZOHO_CLIENT_SECRET'),
            'grant_type' => 'refresh_token'
        ]);

        $accessToken = $response->json('access_token');

        if ($accessToken) {
            // Set token in cached
            Cache::put('zoho_access_token', $accessToken, now()->addMinutes(55));
            return $accessToken;
        }

        return null;
    }

    public function getOrCreateAccount(array $data)
    {
        $accessToken = $this->getZohoAccessToken();

        // Search existing account by name
        $searchUrl = env('ZOHO_API_BASE_URL') . '/Accounts/search?criteria=(Account_Name:equals:' . urlencode($data['account_name']) . ')';

        $searchResponse = Http::withToken($accessToken)->get($searchUrl);

        if ($searchResponse->ok() && !empty($searchResponse['data'][0]['id'])) {
            return $searchResponse['data'][0]['id'];
        }

        // Create new account
        $createResponse = Http::withToken($accessToken)->post(env('ZOHO_API_BASE_URL') . '/Accounts', [
            'data' => [[
                'Account_Name' => $data['account_name'],
                'Website' => $data['account_website'] ?? '',
                'Phone' => $data['account_phone'] ?? '',
            ]],
        ]);

        return $createResponse['data'][0]['details']['id'] ?? null;
    }

    public function createDeal(array $data, string $accountId)
    {
        $accessToken = $this->getZohoAccessToken();

        return Http::withToken($accessToken)->post(env('ZOHO_API_BASE_URL') . '/Deals', [
            'data' => [[
                'Deal_Name' => $data['deal_name'],
                'Stage' => $data['deal_stage'],
                'Closing_Date' => $data['deal_closing_date'],
                'Account_Name' => ['id' => $accountId],
            ]],
        ]);
    }

    public function getStagesList()
    {
        $accessToken = $this->getZohoAccessToken();

        $url = env('ZOHO_API_BASE_URL') . '/settings/fields?module=Deals';
        $fieldsResponse = Http::withToken($accessToken)->get($url);

        if (!$fieldsResponse->ok()) {
            throw new \Exception('Failed to get fields');
        }

        $fields = $fieldsResponse->json('fields');

        foreach ($fields as $field) {
            if ($field['field_label'] === 'Stage') {

                return collect($field['pick_list_values'])
                    ->pluck('display_value', 'actual_value')
                    ->toArray();
            }
        }

        return [];
    }
}
