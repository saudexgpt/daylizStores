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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
            'customer', 'orderItems.item'
        ])->where($condition)->where($condition2)->paginate($request->limit);
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
    public function store(Request $request)
    {
        //
        $user = $this->registerCustomer($request);
        $prefix = 'DLZ';
        $order_items = json_decode(json_encode($request->cart_items));
        $order = new Order();
        $order->location       = $request->location[0];
        $order->user_id         = $user->id;
        // $order->subtotal            = $request->subtotal;
        // $order->discount            = $request->discount;
        // $order->tax                 = $request->tax;
        $order->delivery_cost       = $request->delivery_cost;
        $order->amount              = $request->amount;
        $order->total               = $request->total;
        $order->nearest_bustop      = $request->nearest_bustop;
        $order->address             = $request->address;
        $order->notes               = $request->notes;
        if ($order->save()) {
            $order->order_number = $prefix . $order->id . randomNumber(); //$this->getInvoiceNo($prefix, $order->id);
            $order->save();

            Mail::to($user)->send(new OrderDetails($user, $order));
            $title = "New Order Made";
            $description = "New order ($order->order_number) was created by: $user->name ($user->phone)";
            $this->logUserActivity($title, $description);
            //log this action to order history
            // $this->createOrderHistory($order, $description);
            //create items ordered for
            $this->createOrderItems($order, $order_items);
        }
        return response()->json(['message' => 'success', 'order_no' => $order->order_number], 200);
    }

    private function createOrderHistory($order, $description)
    {
        $order_history = new OrderHistory();
        $order_history->order_id = $order->id;
        $order_history->status_code = $order->order_status;
        $order_history->description = $description;
        $order_history->save();
    }

    private function createOrderItems($order, $order_items)
    {
        foreach ($order_items as $order_item) {

            $order_item_obj = new OrderItem();
            $order_item_obj->order_id = $order->id;
            $order_item_obj->item_id = $order_item->id;
            $product_name = $order_item->name;
            if (isset($order_item->selectedColor) && $order_item->selectedColor !== '') {
                $product_name .= '-' . $order_item->selectedColor;
            }
            if (isset($order_item->selectedSize) && $order_item->selectedSize !== '') {
                $product_name .= '-' . $order_item->selectedSize;
            }
            $order_item_obj->product_name = $product_name;

            $order_item_obj->quantity = $order_item->quantity;
            $order_item_obj->price = $order_item->rate;
            $order_item_obj->total = $order_item->quantity * $order_item->rate;
            // $order_item_obj->tax = $order_item['tax'];
            $order_item_obj->save();
        }
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
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
