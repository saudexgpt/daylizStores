<?php

namespace App\Models\Order;

use App\Models\Stock\Item;
use App\Models\Stock\ItemStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    //
    use SoftDeletes;
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function stock()
    {
        return $this->belongsTo(ItemStock::class);
    }
}
