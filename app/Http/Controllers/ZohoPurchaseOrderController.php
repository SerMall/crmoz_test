<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoInventory\PurchaseOrders;
use Illuminate\Support\Facades\Log;

class ZohoPurchaseOrderController extends Controller
{
    /**
     * @param PurchaseOrders $purchaseOrders
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextPurchaseOrderNumber(PurchaseOrders $purchaseOrders)
    {
        try {
            $nextPurchaseOrderNumber = $purchaseOrders->nextPurchaseOrderNumberRequest();

            return response()->json([
                'data' => $nextPurchaseOrderNumber
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param PurchaseOrders $purchaseOrders
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPurchaseOrder(Request $request, PurchaseOrders $purchaseOrders)
    {
        try {
            $data = $request->all();
            $newPurchaseOrderResponse = $purchaseOrders->purchaseOrderCreateRequest($data);

            return response()->json([
                'data' => $newPurchaseOrderResponse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

//    public function getPurchaseOrdersById(PurchaseOrders $purchaseOrders)
//    {
//        try {
//            $customersList = $purchaseOrders->getPurchaseOrderByIdRequest();
//            return response()->json([
//                'data' => $customersList
//            ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
}
