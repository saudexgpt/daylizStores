<?php

namespace App\Models\Order;

use App\Customer;
use App\Laravue\Models\User;
use App\Location;
use App\Models\Setting\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    //
    use SoftDeletes;
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function histories()
    {
        return $this->hasMany(OrderHistory::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
