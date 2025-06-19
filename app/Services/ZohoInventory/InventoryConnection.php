<?php

namespace App\Services\ZohoInventory;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InventoryConnection
{
    /**
     * @return array|false|mixed
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getZohoInventoryAccessToken()
    {
        // Check token in cached
        if (Cache::has('zoho_inventory_access_token')) {
            return Cache::get('zoho_inventory_access_token');
        }
        // Refresh token
        $response = Http::asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
            'refresh_token' => env('ZOHO_INVENTORY_REFRESH_TOKEN'),
            'client_id' => env('ZOHO_INVENTORY_CLIENT_ID'),
            'client_secret' => env('ZOHO_INVENTORY_CLIENT_SECRET'),
            'grant_type' => 'refresh_token'
        ]);

        $accessToken = $response->json('access_token');
        if ($accessToken) {
            // Set token in cached
            Cache::put('zoho_inventory_access_token', $accessToken, now()->addMinutes(55));
            return $accessToken;
        }

        return false;
    }
}
