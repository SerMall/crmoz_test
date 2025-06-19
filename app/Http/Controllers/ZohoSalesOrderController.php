<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Services\ZohoInventory\Customers;
use App\Services\ZohoInventory\SalesOrders;
use App\Services\ZohoInventory\Products;
use Illuminate\Support\Facades\Log;

class ZohoSalesOrderController extends Controller
{
    /**
     * @param SalesOrders $salesOrders
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextOrderNumber(SalesOrders $salesOrders)
    {
        try {
            $nextOrderNumber = $salesOrders->nextOrderNumberRequest();

            return response()->json([
                'data' => $nextOrderNumber
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Products $products
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsList(Products $products)
    {
        try {
            $productsList = $products->getProducts();
            return response()->json([
                'data' => $productsList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param SalesOrders $salesOrders
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSalesOrder(Request $request, SalesOrders $salesOrders)
    {
        try {
            $data = $request->all();
            $newOrderResponse = $salesOrders->salesOrderCreateRequest($data);

            return response()->json([
                'data' => $newOrderResponse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
