<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZohoContactController;
use App\Http\Controllers\ZohoSalesOrderController;
use App\Http\Controllers\ZohoPurchaseOrderController;

Route::get('/zoho/inventory/organizationaddress', [ZohoContactController::class, 'getOrganizationAddress']);
Route::get('/zoho/inventory/customerslist', [ZohoContactController::class, 'getCustomersList']);
Route::get('/zoho/inventory/vendorslist', [ZohoContactController::class, 'getVendorsList']);
Route::get('/zoho/inventory/customers/addressid', [ZohoContactController::class, 'getContactAddressId']);
Route::post('/zoho/inventory/customers/create', [ZohoContactController::class, 'createCustomer']);

Route::get('/zoho/inventory/nextordernumber', [ZohoSalesOrderController::class, 'getNextOrderNumber']);
Route::get('/zoho/inventory/productslist', [ZohoSalesOrderController::class, 'getProductslist']);
Route::post('/zoho/inventory/salesorders/create', [ZohoSalesOrderController::class, 'createSalesOrder']);

Route::get('/zoho/inventory/nextpurchasenumber', [ZohoPurchaseOrderController::class, 'getNextPurchaseOrderNumber']);
Route::post('/zoho/inventory/purchaseorders/create', [ZohoPurchaseOrderController::class, 'createPurchaseOrder']);


