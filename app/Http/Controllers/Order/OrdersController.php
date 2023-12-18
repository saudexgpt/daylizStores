<?php

namespace App\Http\Controllers\Order;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Laravue\Models\User;
use App\Models\Order\Order;
use App\Models\Order\OrderHistory;
use App\Models\Order\OrderPayment;
use App\Models\Order\OrderStatus;
use App\Models\Order\OrderItem;
use App\Mail\CustomerCredentials;
use App\Mail\OrderDetails;
use App\Models\ItemDiscount;
use App\Models\Stock\ItemPrice;
use App\Models\Stock\ItemStock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $user = $this->getUser();
        $location_id = $request->location_id;
        $condition = [];
        $condition2 = [];
        // if ($location_id !== 'all') {

        //     $condition = ['location_id' => $location_id];
        // }
        if (isset($request->status) && $request->status != '') {
            ////// query by status //////////////
            $status = $request->status;
            $condition2 = ['order_status' => $status];
        }
        $orders = Order::with([
            'customer', 'orderItems.item', 'orderItems.stock'
        ])->where($condition)->where($condition2)->orderBy('id', 'DESC')->paginate($request->limit);
        return response()->json(compact('orders'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignOrderToWarehouse(Request $request, Order $order)
    {

        $location_id = $request->location_id;
        $order->location_id = $location_id;
        $order->save();

        return $this->show($order);
    }

    private function registerCustomer($data)
    {
        $user = User::where('email', $data->email)->first();
        if (!$user) {
            $user = new User();
            $user->name = $data->name;
            $user->phone = $data->phone;
            $user->email = $data->email;
            $password = randomPassword();
            $user->password = $password;
            $user->address = $data->address;
            $user->nearest_bustop = $data->nearest_bustop;
            $user->save();
            // send login credentials email to user here

            Mail::to($user)->send(new CustomerCredentials($user, $password));
            return $user;
        }
        $user->address = $data->address;
        $user->nearest_bustop = $data->nearest_bustop;
        $user->save();
        return $user;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getInvoiceNo($prefix, $next_no)
    {
        $no_of_digits = 5;

        $digit_of_next_no = strlen($next_no);
        $unused_digit = $no_of_digits - $digit_of_next_no;
        $zeros = '';
        for ($i = 1; $i <= $unused_digit; $i++) {
            $zeros .= '0';
        }

        return $prefix . $zeros . $next_no;
    }
    private function checkStockBeforeOrdering($order_items)
    {
        $limited_stock = false;
        $details = [];
        foreach ($order_items as $order_item) {
            $stock_id = $order_item->stock_id;
            $quantity = $order_item->quantity;
            $stock = ItemStock::find($stock_id);
            $name = $order_item->name;
            $balance = $stock->quantity_stocked - $stock->reserved - $stock->sold;
            if ($balance < $quantity) {
                $limited_stock = true;
                $price = $order_item->rate * $balance;
                $order_item->subTotal = $price;
                $order_item->quantity = $balance;
                $details[] = ['product' => $name, 'balance' => $balance, 'updated_item' => $order_item];
            }
        }
        return array($limited_stock, $details);
    }
    public function generateOrderNumber(Request $request)
    {
        //
        $can_make_order = $this->settingValue('can_make_order');
        if ($can_make_order === 'false') {
            return response()->json([], 500);
        }
        $user = $this->registerCustomer($request);
        $prefix = 'DS';
        $order = Order::where('user_id', $user->id)->where('amount', NULL)->where('order_number', '!=', NULL)->first();
        if (!$order) {

            $order = new Order();
            $order->user_id = $user->id;
            if ($order->save()) {

                $order->order_number = $this->getInvoiceNo($prefix, $order->id);
            }
            $order->save();
        }
        return response()->json(['order_number' => $order->order_number, 'order_id' => $order->id, 'user_id' => $order->user_id], 200);
    }
    private function uploadReciept($image)
    {

        $name = randomNumber() . time() . '.' . $image->getClientOriginalExtension();
        $folder = "storage/receipt";
        $avatar = $image->storeAs($folder, $name, 'public');
        return '/' . $avatar;
    }
    public function store(Request $request)
    {
        //
        $can_make_order = $this->settingValue('can_make_order');
        if ($can_make_order === 'false') {
            return response()->json([], 500);
        }
        $order_items = json_decode(json_encode($request->cart_items));
        list($limited_stock, $details) = $this->checkStockBeforeOrdering($order_items);
        if ($limited_stock === true) {
            $message = 'check_cart';
            return response()->json(compact('details', 'message'), 206);
        }
        $location = '';
        foreach ($request->location as $loc) {
            $location .= $loc . '/';
        }
        $user = $this->registerCustomer($request);
        // if ($request->user_id != NULL) {
        //     $user = User::find($request->user_id);
        // } else {

        //     $user = $this->registerCustomer($request);
        // }
        $prefix = 'DS';
        $order = new Order();
        $order->location       = $location;
        $order->user_id         = $user->id;
        $image = $request->file('receipt_image');
        $order->receipt_image = $this->uploadReciept($image);
        // $order->subtotal            = $request->subtotal;
        // $order->discount            = $request->discount;
        // $order->tax                 = $request->tax;
        $order->delivery_cost       = $request->delivery_cost;
        $order->amount              = $request->amount;
        $order->total               = $request->total;
        $order->nearest_bustop      = $request->nearest_bustop;
        $order->address             = $request->address;
        $order->notes               = $request->notes;
        $order->valid_till          = date('Y-m-d H:i:s', strtotime('+504 hours')); // 21 days
        if ($order->save()) {
            $order->order_number = $this->getInvoiceNo($prefix, $order->id); //$prefix . $order->id . randomNumber(); //$this->getInvoiceNo($prefix, $order->id);
            $order->save();
            //log this action to order history
            // $this->createOrderHistory($order, $description);
            //create items ordered for
            $order = $this->createOrderItems($order, $order_items);


            Mail::to($user)->send(new OrderDetails($user, $order, $order_items));
            $title = "New Order Made";
            $description = "New order ($order->order_number) was created by: $user->name ($user->phone)";
            $this->logUserActivity($title, $description);
            // we need to reserve the product for at least 24 hours to reduce stock so that no issue will arise
            $this->reserveProduct($order->id);
        }
        return response()->json(['order_details' => $order, 'message' => 'success'], 200);
    }

    private function createOrderHistory($order, $description)
    {
        $order_history = new OrderHistory();
        $order_history->order_id = $order->id;
        $order_history->status_code = $order->order_status;
        $order_history->description = $description;
        $order_history->save();
    }

    private function calculateDiscountedAmount($itemId, $quantity)
    {
        $item_price = ItemPrice::where('item_id', $itemId)->first();
        $item_discounts = ItemDiscount::where('item_id', $itemId)->get();
        $amount = ($item_price) ? $item_price->amount : 0;
        if ($item_discounts->isNotEmpty()) {
            foreach ($item_discounts as $item_discount) {
                $moq = $item_discount->minimum_order_quantity;
                if ($quantity >= $moq) {
                    $amount = $item_discount->amount;
                }
            }
        }
        return $amount;
    }

    private function createOrderItems($order, $order_items)
    {
        $total = 0;
        foreach ($order_items as $order_item) {
            $quantity = $order_item->quantity;
            $rate = $this->calculateDiscountedAmount($order_item->id, $quantity);

            $order_item_obj = new OrderItem();
            $order_item_obj->order_id = $order->id;
            $order_item_obj->stock_id = $order_item->stock_id;
            $order_item_obj->item_id = $order_item->id;
            $order_item_obj->product_name = $order_item->name;

            $order_item_obj->quantity = $quantity;
            $order_item_obj->price = $rate;
            $order_item_obj->total = $order_item->quantity * $rate;
            $total += $order_item_obj->total;
            // $order_item_obj->tax = $order_item['tax'];
            $order_item_obj->save();
        }
        $order->total = $total;
        $order->save();
        return $order;
    }

    public function stabilizeOrderTotal()
    {
        set_time_limit(0);
        OrderItem::with('order')->where('total_updated', 0)->chunkById(200, function (Collection $orderItems) {
            foreach ($orderItems as $orderItem) {
                $order = $orderItem->order;
                $orderItem->total_updated = 1;
                $orderItem->save();
                $order->total += $orderItem->total;
                $order->save();
            }
        }, $column = 'id');
        return 'true';
    }
    public function adminSearchOrder(Request $request)
    {
        $keyword = $request->search_query;
        $orderQuery = Order::query();
        if ($keyword !== '') {
            $orderQuery->where(function ($q) use ($keyword) {
                $q->where('order_number', 'LIKE', '%' . $keyword . '%');
                $q->orWhere(function ($q2) use ($keyword) {
                    $q2->whereHas('customer', function ($q3) use ($keyword) {
                        $q3->where('name', 'LIKE', '%' . $keyword . '%');
                        $q3->orWhere('email', 'LIKE', '%' . $keyword . '%');
                        $q3->orWhere('phone', 'LIKE', '%' . $keyword . '%');
                    });
                });
            });
        }
        $orders = $orderQuery->with('customer', 'orderItems.item', 'orderItems.stock')->get();
        return response()->json(compact('orders'), 200);
    }
    public function search(Request $request)
    {
        $message = 'failed';
        $username = $request->username;
        $order_number = $request->order_number;
        $user = User::where('phone', $username)->orWhere('email', $username)->first();
        if ($user) {
            $order = Order::with('customer', 'orderItems.item')->where(['user_id' => $user->id, 'order_number' => $order_number])->first();
            if ($order) {
                $message = 'success';
                return response()->json(compact('message', 'order'), 200);
            }
        }
        return response()->json(compact('message'), 200);
    }
    public function myOrders()
    {
        //
        $user = $this->getUser();
        $orders = Order::with(['customer', 'orderItems.item'])->where('user_id', $user->id)->orderBy('id', 'DESC')->paginate(10);
        return response()->json(compact('orders'));
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
        $order =  $order->with([
            'customer.user', 'customer.type', 'orderItems.item', 'histories' => function ($q) {
                $q->orderBy('id', 'DESC');
            },
            'currency'
        ])->find($order->id);
        return response()->json(compact('order'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function changeOrderStatus(Request $request, Order $order)
    {
        //
        $user = $this->getUser();
        $status = $request->status;
        $payment_status = $request->payment_status;
        if ($request->payment_status === 'paid') {
            $description = "Order ($order->order_number) has been paid for. Payment logged by: $user->name ($user->email)";
            $title = "Order Payment Made";
            $this->logUserActivity($title, $description);
        }
        if ($status === 'On Transit') {
            $this->sellOutFromStock($order->id);
        }
        if ($status === 'Cancelled') {
            $this->releasePendingUnpaidOrderQuantities($order);
        }

        $order->order_status = $status;
        $order->payment_status = $payment_status;
        $order->save();
        $description = "Order ($order->order_number) status changed to " . strtoupper($order->order_status) . " by: $user->name ($user->email)";
        $title = "Order Status Updated";
        $this->logUserActivity($title, $description);
        return 'success';
        //log this action to order history
        // $this->createOrderHistory($order, $description);
        // return $this->show($order);
    }
    private function releasePendingUnpaidOrderQuantities($order)
    {
        $orderItems = $order->orderItems;
        foreach ($orderItems as $orderItem) {
            $stock = $orderItem->stock;
            $order_quantity = $orderItem->quantity;
            $reserved = $stock->reserved;

            if ($reserved >= $order_quantity) {
                $stock->reserved -= $order_quantity;
                $stock->save();
            }
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    private function reserveProduct($orderId)
    {
        $orderItems = OrderItem::with('stock')->where('order_id', $orderId)->get();
        foreach ($orderItems as $orderItem) {
            $stock = $orderItem->stock;
            $order_quantity = $orderItem->quantity;
            $stock_balance = $stock->quantity_stocked - $stock->reserved - $stock->sold;

            if ($stock_balance >= $order_quantity) {
                $stock->reserved += $order_quantity;
                $stock->save();
            }
        }
    }
    private function sellOutFromStock($orderId)
    {
        $orderItems = OrderItem::with('stock')->where('order_id', $orderId)->get();
        foreach ($orderItems as $orderItem) {
            $stock = $orderItem->stock;
            $order_quantity = $orderItem->quantity;
            $reserved = $stock->reserved;

            if ($reserved >= $order_quantity) {
                $stock->reserved -= $order_quantity;
                $stock->sold += $order_quantity;
                $stock->save();
            }
        }
    }
    public function reverseCancelledOrder(Request $request, Order $order)
    {
        $this->reserveOrderQuantities($order);
        $this->reverseOrderStatus($order, $request->status);
    }


    public function reverseBulkCancelledOrder()
    {
        set_time_limit(0);
        // Order::with('orderItems.stock')->where('order_status', 'Cancelled')->where('payment_status', 'cancelled')->where('updated_at', 'LIKE', '%2023-12-14%')
        //     ->chunkById(200, function (Collection $orders) {
        //         foreach ($orders as $order) {
        //             $this->reserveOrderQuantities($order->orderItems);
        //             $this->reverseOrderStatus($order);
        //         }
        //     }, $column = 'id');
        $orders = Order::with('orderItems.stock')->where('order_status', 'Cancelled')->where('payment_status', 'cancelled')->where('updated_at', 'LIKE', '%2023-12-14%')->get();
        foreach ($orders as $order) {
            $this->reserveOrderQuantities($order->orderItems);
            $this->reverseOrderStatus($order);
        }
    }
    private function reserveOrderQuantities($orderItems)
    {
        // $orderItems = $order->orderItems;
        foreach ($orderItems as $orderItem) {
            $stock = $orderItem->stock;
            $order_quantity = $orderItem->quantity;
            // $reserved = $stock->reserved;
            $stock_balance = $stock->quantity_stocked - $stock->reserved - $stock->sold;

            if ($stock_balance >= $order_quantity) {
                $stock->reserved += $order_quantity;
            }
            $stock->cancelled_quantity_reserved += $order_quantity;
            $stock->save();
        }
    }
    private function reverseOrderStatus($order)
    {
        $order->order_status = 'CARP';
        $order->payment_status = 'carp';
        $order->cancelled_status_reversed = 1;
        $order->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
