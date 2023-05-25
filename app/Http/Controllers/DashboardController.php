<?php

namespace App\Http\Controllers;

use App\Models\Invoice\Invoice;
use App\Models\Logistics\Vehicle;
use App\Models\Order\Order;
use App\Models\Stock\Item;
use App\Models\Stock\ItemStock;
use App\Models\Warehouse\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $products = Item::count();
        // $sales = Order::where('payment_status', 'paid')->selectRaw('SUM(amount) as total_amount')->first();
        // $total_sales = $sales->total_amount;
        $delivered_orders = Order::where('order_status', 'Delivered')->count();
        $pending_orders = Order::where('order_status', 'Pending')->count();
        $transit_orders = Order::where('order_status', 'On Transit')->count();

        return response()->json([
            'data_summary' => compact('products', 'delivered_orders', 'pending_orders', 'transit_orders'),
        ], 200);
    }

    // public function itemsRunningOutOfStock()
    // {
    //     $running_out_of_stock_products = ItemStock::with('item')->whereRaw('(SUM(quantity_stocked) - SUM(sold)) <= 10')->select('*', \DB::raw('(SUM(quantity_stocked) - SUM(sold)) as total_balance'))->groupBy('item_id')->get();

    //     return response()->json(compact('running_out_of_stock_products'), 200);
    // }
}
