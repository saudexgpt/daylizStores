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
        $menu_varieties = Item::count();
        $sales = Order::where('payment_status', 'paid')->selectRaw('SUM(amount) as total_amount')->first();
        $total_sales = $sales->total_amount;
        $pending_orders = Order::where('order_status', 'Pending')->count();
        $transit_orders = Order::where('order_status', 'On Transit')->count();

        return response()->json([
            'data_summary' => compact('menu_varieties', 'total_sales', 'pending_orders', 'transit_orders'),
        ], 200);
    }
}
