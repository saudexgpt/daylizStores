<?php

// use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// api to fetch all registered products for external requests
// $router->get('hello-message', function () {
//     return response()->json(['message' => 'Hello World'], 200);
// });
// $router->get('get-articles', [ArticlesController::class, 'index']);

$router->get('fetch-location', 'Location\LocationsController@fetchAllLocations');

$router->post('order/store', 'Order\OrdersController@store');
$router->post('order/search', 'Order\OrdersController@search');
$router->post('auth/login', 'AuthController@login');
$router->post('auth/recover-password', 'AuthController@recoverPassword');
$router->post('auth/reset-password', 'AuthController@resetPassword');
$router->post('auth/confirm-password-reset-token', 'AuthController@confirmPasswordResetToken');

$router->get('/menu-category', 'Stock\CategoriesController@index');
$router->get('get-items', 'Stock\ItemsController@index');
$router->get('item-show/{item}', 'Stock\ItemsController@show');
$router->get('item-details', 'Stock\ItemsController@itemDetails');
$router->get('latest-products', 'Stock\ItemsController@fetchLatestProducts');


$router->get('item-reviews', 'Stock\ItemsController@itemReviews');
$router->get('all-items', 'Stock\ItemsController@fetchAllItems');
$router->post('give-product-review', 'Stock\ItemsController@giveReview');

$router->get('fetch-necessary-params', 'Controller@fetchNecessayParams');

$router->group(['middleware' => 'auth:api'], function () use ($router) {

    $router->get('auth/user', 'AuthController@user');
    $router->post('auth/logout', 'AuthController@logout');
    $router->get('users', 'UserController@index'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);
    $router->get('user-notifications', 'UserController@userNotifications');

    $router->post('users', 'UserController@store'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);
    $router->post('users/add-bulk-customers', 'UserController@addBulkCustomers'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);

    $router->get('users/{user}', 'UserController@show'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);
    $router->put('users/{user}', 'UserController@update');
    $router->put('users/assign-role/{user}', 'UserController@assignRole');

    $router->put('users/change-customer-details/{customer}', 'UserController@changeCustomerDetails');
    $router->post('users/update-password', 'UserController@updatePassword');
    $router->put('users/reset-password/{user}', 'UserController@adminResetUserPassword'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);

    $router->delete('users/{user}', 'UserController@destroy'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);
    $router->delete('customers/{user}', 'UserController@destroyCustomer'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);
    $router->get('users/{user}/permissions', 'UserController@permissions'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
    $router->put('users/{user}/permissions', 'UserController@updatePermissions'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
    $router->apiResource('roles', 'RoleController'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
    $router->get('roles/{role}/permissions', 'RoleController@permissions'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
    $router->apiResource('permissions', 'PermissionController'); //->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
    // $router->apiResource('users', 'UserController')->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);


    $router->get('fetch-customers', 'Controller@fetchCustomers');
    $router->post('upload-file', 'Controller@uploadFile');
    // });
    $router->group(['prefix' => 'dashboard'], function () use ($router) {
        //customer
        $router->group(['prefix' => 'admin'], function () use ($router) {
            $router->get('/', 'DashboardController@adminDashboard'); //->middleware('permission:view admin dashboard');

        });
    });
    /////////////////////////STOCKS MODULE////////////////////////////
    $router->group(['prefix' => 'stock', 'namespace' => 'Stock'], function () use ($router) {
        /////////////////////////////general stock////////////////////////
        $router->group(['prefix' => 'general-items'], function () use ($router) {

            $router->get('/', 'ItemsController@index');
            //$router->group(['middleware' => ['permission:create menu']], function () use ($router) {

            $router->post('store', 'ItemsController@store');
            $router->put('update/{item}', 'ItemsController@update');
            $router->put('stockup/{item}', 'ItemsController@stock');

            $router->delete('delete/{item}', 'ItemsController@destroy');
            $router->get('delete-item-tax', 'ItemsController@destroyItemTax');
            $router->group(['prefix' => 'prices'], function () use ($router) {
                $router->post('store', 'ItemPricesController@store');
                $router->put('update/{item_price}', 'ItemPricesController@update');
                $router->delete('delete/{item_price}', 'ItemPricesController@destroy');
            });

            $router->put('approve/{review}', 'ItemsController@approveReview');
            //});
        });

        ///////////////////create menu//////////////////////////////////
        $router->group(['prefix' => 'item-category'], function () use ($router) {
            //$router->group(['middleware' => ['permission:create menu']], function () use ($router) {
            $router->get('/', 'CategoriesController@index');
            $router->post('store', 'CategoriesController@store');
            $router->put('update/{category}', 'CategoriesController@update');
            $router->delete('delete/{category}', 'CategoriesController@destroy');
            //});
        });
    });
    ////////////////////////////////////STOCK ENDS/////////////////////////////////////////////
    //////////////////////////////REPORTS//////////////////////////////
    $router->group(['prefix' => 'reports'], function () use ($router) {
        //$router->group(['middleware' => ['permission:view reports']], function () use ($router) {
        //customer
        $router->group(['prefix' => 'graphical'], function () use ($router) {
            $router->get('products-in-stock', 'ReportsController@productsInStockGraph');
            $router->get('reports-on-vehicle', 'ReportsController@reportsOnVehiclesGraph');
            $router->get('reports-on-waybill', 'ReportsController@reportsOnWaybillGraph');
        });
        //driver
        $router->group(['prefix' => 'tabular'], function () use ($router) {
            $router->get('products-in-stock', 'ReportsController@productsInStockTabular');
            $router->get('outbounds', 'ReportsController@outbounds');
        });
        //});
        $router->get('audit-trails', 'ReportsController@auditTrails'); //->middleware('permission:view audit trail');
        $router->get('notification/mark-as-read', 'ReportsController@markAsRead');
        $router->get('backups', 'ReportsController@backUps'); //->middleware('permission:backup database');
        $router->get('bin-card', 'ReportsController@fetchBinCard');
        $router->get('instant-balances', 'ReportsController@instantBalances');

        $router->get('reserved-product-transactions/{item_in_stock}', 'ReportsController@reservedProductTransactions');
    });
    ////////////////////////////////////////////////////////////////////////////////////////
    $router->group(['prefix' => 'user'], function () use ($router) {
        //customer
        $router->group(['prefix' => 'customer'], function () use ($router) {
            $router->post('/store', 'UserController@addCustomer');
        });
    });
    $router->group(['prefix' => 'order', 'namespace' => 'Order'], function () use ($router) {
        $router->group(['prefix' => 'general'], function () use ($router) {

            $router->get('/', 'OrdersController@index'); //->middleware('permission:view order');
            $router->get('show/{order}', 'OrdersController@show'); //->middleware('permission:view order');


            $router->put('change-status/{order}', 'OrdersController@changeOrderStatus'); //->middleware('permission:approve order|cancel order|');

            // $router->put('deliver/{order}', 'OrdersController@changeOrderStatus')->middleware('permission:approve order');

            // $router->put('cancel/{order}', 'OrdersController@changeOrderStatus')->middleware('permission:cancel order');

            $router->put('assign-order-to-location/{order}', 'OrdersController@assignOrderToLocation'); //->middleware('permission:assign order to location');
        });
    });
    ////////////////////////////////////LOCATION/////////////////////////////////////////////
    $router->group(['prefix' => 'location', 'namespace' => 'Location'], function () use ($router) {


        //$router->group(['middleware' => 'permission:manage location'], function () use ($router) {

        $router->get('/', 'LocationsController@index');
        $router->get('/assignable-users', 'LocationsController@assignableUsers');


        $router->post('store', 'LocationsController@store');
        $router->put('update/{location}', 'LocationsController@update');
        $router->delete('delete/{location}', 'LocationsController@destroy');

        $router->post('add-user-to-location', 'LocationsController@addUserToLocation');
        // });
    });
});
