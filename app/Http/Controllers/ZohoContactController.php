<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoInventory\Customers;
use Illuminate\Support\Facades\Log;

class ZohoContactController extends Controller
{
    /**
     * @param Customers $customers
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizationAddress(Customers $customers)
    {
        try {
            $organization = $customers->getOrganizationAddressRequest();
            return response()->json([
                'data' => $organization
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Customers $customers
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomersList(Customers $customers)
    {
        try {
            $customersList = $customers->getCustomers();
            return response()->json([
                'data' => $customersList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param Customers $customers
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContactAddressId(Request $request, Customers $customers)
    {
        $contactId = $request->get('contact_id');
        if (!$contactId) {
            return response()->json(['error' => 'Missing contact_id'], 422);
        }

        try {
            $contactAddressId = $customers->getContactAddressIdRequest($contactId);
            return response()->json([
                'data' => $contactAddressId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param Customers $customers
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCustomer(Request $request, Customers $customers)
    {
        try {
            $data = $request->all();
            $newCustomerResponse = $customers->customerCreateRequest($data);

            return response()->json([
                'data' => $newCustomerResponse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Customers $customers
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVendorsList(Customers $customers)
    {
        try {
            $customersList = $customers->getVendors();

            return response()->json([
                'data' => $customersList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

//    public function getCustomersById(Customers $customers)
//    {
//        try {
//            $customersList = $customers->getCustomersByIdRequest();
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
