<?php

namespace App\Models\Stock;

use App\Models\ItemDiscount;
use App\Models\ItemReview;
use Illuminate\Database\Eloquent\Model;
use App\Models\Setting\Tax;

use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{

    use SoftDeletes;
    protected $hidden = [
        'created_at', 'updated_at',
    ];
    protected $fillable = ['category_id', 'name', 'slug', 'description'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function itemStocks()
    {
        return $this->hasMany(ItemStock::class);
    }
    public function media()
    {
        return $this->hasMany(ItemMedia::class);
    }
    public function discounts()
    {
        return $this->hasMany(ItemDiscount::class, 'item_id', 'id');
    }
    public function price()
    {
        return $this->hasOne(ItemPrice::class);
    }
    public function reviews()
    {
        return $this->hasMany(ItemReview::class, 'item_id', 'id');
    }
}
