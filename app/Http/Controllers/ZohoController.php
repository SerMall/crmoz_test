<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoService;
use Illuminate\Support\Facades\Log;

class ZohoController extends Controller
{
    public function createDealAndAccount(Request $request, ZohoService $zoho)
    {
        $request->validate([
            'deal_name' => 'required|string',
            'deal_stage' => 'required|string',
            'deal_closing_date' => 'required|date',
            'account_name' => 'required|string',
        ]);

        try {
            $accountId = $zoho->getOrCreateAccount($request->only([
                'account_name', 'account_website', 'account_phone'
            ]));

            if (!$accountId) {
                return response()->json(['error' => 'Deal or account did not create.'], 500);
            }

            $dealResponse = $zoho->createDeal($request->only(['deal_name', 'deal_stage', 'deal_closing_date']), $accountId);

            if (!$dealResponse->successful()) {
                return response()->json(['error' => 'Deal creation failed.'], 500);
            }

            return response()->json(['message' => 'Deal and account created successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unexpected error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get List of Stages for Deal
     *
     * @param ZohoService $zoho
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDealStagesList(ZohoService $zoho)
    {
        try {
            $stages = $zoho->getStagesList();
            return response()->json([
                'data' => $stages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
