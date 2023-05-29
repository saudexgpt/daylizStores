<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\ItemDiscount;
use App\Models\ItemReview;
use App\Models\Stock\Item;
use App\Models\Stock\ItemMedia;
use App\Models\Stock\ItemPrice;
use App\Models\Stock\ItemStock;
use App\Models\Stock\ItemTax;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function fetchAllItems()
    {
        $items = Item::with(['category'])->orderBy('id')->get();
        return response()->json(compact('items'));
    }

    public function fetchLatestProducts()
    {
        $stocks = ItemStock::with(['item.media', 'item.price'])->whereRaw('quantity_stocked - sold > 0')->orderBy('updated_at', 'DESC')->groupBy('item_id')->paginate(10);
        return response()->json(compact('stocks'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $exclude_item_id = NULL;
        $relationship = ['media', 'itemStocks' => function ($q) {
            $q->whereRaw('quantity_stocked - sold > 0');
        }, 'discounts', 'category', 'price'];
        if (isset($request->exclude_item_id) && $request->exclude_item_id !== '' && $request->exclude_item_id !== null) {
            $exclude_item_id = $request->exclude_item_id;
        }
        if (isset($request->category_id) && $request->category_id !== '' && $request->category_id !== null) {
            $category_id = $request->category_id;
            $items = Item::with($relationship)->where('category_id', $category_id)->where('id', '!=', $exclude_item_id)->orderBy('id')->paginate($request->limit);
        } else {
            $items = Item::with($relationship)->where('id', '!=', $exclude_item_id)->orderBy('id')->paginate($request->limit);
        }

        return response()->json(compact('items'));
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stock\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {

        $item = $item->with(['media', 'itemStocks' => function ($q) {
            $q->whereRaw('quantity_stocked - sold > 0');
        }, 'discounts', 'category', 'price'])->find($item->id);
        // $item->currency_id = $item->price->currency_id;
        // $item->purchase_price = $item->price->purchase_price;
        // $item->amount = $item->price->amount;
        return response()->json(compact('item'), 200);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stock\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function itemDetails(Request $request)
    {
        $slug = str_replace(' ', '-', strtolower($request->slug));
        $item = Item::with(['media', 'itemStocks' => function ($q) {
            $q->whereRaw('quantity_stocked - sold > 0');
        }, 'discounts', 'category', 'price'])->where('slug', $slug)->first();
        // $item->currency_id = $item->price->currency_id;
        // $item->purchase_price = $item->price->purchase_price;
        // $item->amount = $item->price->amount;
        return response()->json(compact('item'), 200);
    }
    public function searchProduct(Request $request)
    {
        $itemQuery = Item::query();
        $relationship = ['media', 'itemStocks' => function ($q) {
            $q->whereRaw('quantity_stocked - sold > 0');
        }, 'discounts', 'category', 'price'];
        $keyword = $request->slug;

        $itemQuery->where(function ($q) use ($keyword) {
            $q->where('name', 'LIKE', '%' . $keyword . '%');
            $q->orWhere(function ($p) use ($keyword) {
                $p->whereHas('category', function ($p)  use ($keyword) {
                    $p->where('name', 'LIKE', '%' . $keyword . '%');
                });
            });
        });
        $items = $itemQuery->with($relationship)->paginate($request->limit);
        return response()->json(compact('items'));
    }
    public function itemReviews(Request $request)
    {

        $reviews = ItemReview::with('user')->where(['item_id' => $request->item_id, 'is_published' => 1])->paginate($request->limit);
        $average = ItemReview::where(['item_id' => $request->item_id, 'is_published' => 1])->selectRaw('AVG(star) as overall')->first();
        return response()->json(compact('reviews', 'average'), 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request, Item $item)
    {
        // return $request;
        //
        $user = $this->getUser();
        $tax_ids =  $request->tax_ids;
        $name = $request->name;
        $category_id = $request->category_id;
        $slug = str_replace(' ', '-', strtolower($name));
        $description = $request->description;
        $item = Item::updateOrCreate(
            ['category_id' => $category_id, 'name' => $name],
            ['slug' => $slug, 'description' => $description]
        );
        // $item = Item::where('name', $name)->first();

        // if (!$item) {
        //     $item = new Item();
        // }
        // $item->name = $name;
        // $item->slug = str_replace(' ', '-', strtolower($name));
        // // $item->package_type = $request->brand;
        // // $item->size = $request->size;
        // $item->category_id = $category_id;
        // // $item->color = $request->color;
        // $item->description = $description;
        // // $item->picture = $picture;
        // //$item->gender = $request->gender;
        // // $item->quantity_stocked = $request->quantity_stocked;
        // $item->save();

        $this->attachItemToMedia($item->id, $request->images);
        $this->removeDeletedMedia($request->deletedImages);
        if (count($request->discounts) > 0) {

            $this->createItemDiscounts($request->discounts, $item->id);
        }
        // save item taxes
        // if ($tax_ids) {
        //     foreach ($tax_ids  as $tax_id) {
        //         $item_tax = new ItemTax();
        //         $item_tax->tax_id = $tax_id;
        //         $item_tax->item_id = $item->id;
        //         $item_tax->save();
        //     }
        // }
        //save item price
        // $item_price = ItemPrice::where('item_id', $item->id)->first();
        // if (!$item_price) {

        //     $item_price = new ItemPrice();
        // }
        // $item_price->item_id = $item->id;
        // //$item_price->currency_id = $request->currency_id;
        // $item_price->amount = $request->amount;
        // // $item_price->purchase_price = $request->purchase_price;
        // $item_price->save();
        $item_price = ItemPrice::updateOrCreate(
            ['item_id' => $item->id],
            ['amount' => $request->amount]
        );
        // log this action
        $title = "Product Added";
        $description = $name . " added to list of products by " . $user->name;;
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($item);

        // return response()->json(['message' => 'Duplicate SKU'], 500);
    }

    private function attachItemToMedia($item_id, $mediaIds)
    {
        if (count($mediaIds) > 0) {
            $media = ItemMedia::whereIn('id', $mediaIds)->get();
            foreach ($media as $med) {
                $med->item_id = $item_id;
                $med->save();
            }
        }
    }
    private function removeDeletedMedia($deletedMediaIds)
    {
        //
        if (count($deletedMediaIds) > 0) {
            $media = ItemMedia::whereIn('id', $deletedMediaIds)->get();
            foreach ($media as $med) {
                unlink(portalPulicPath($med->thumbnail));
                unlink(portalPulicPath($med->link));


                $med->delete();
            }
        }
    }
    private function createItemDiscounts($discounts, $item_id)
    {
        foreach ($discounts as $discount) {
            if ($discount['amount'] != null && $discount['minimum_order_quantity'] != null) {
                $item_id = $item_id;
                $minimum_order_quantity = (int) $discount['minimum_order_quantity'];
                $amount = (int) $discount['amount'];
                ItemDiscount::updateOrCreate(
                    ['item_id' => $item_id],
                    ['minimum_order_quantity' => $minimum_order_quantity, 'amount' => $amount]
                );
            }
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stock\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Item $item)
    {
        //
        // return $request;
        $user = $this->getUser();
        $tax_ids =  $request->tax_ids;
        $item->name = $request->name;
        $item->slug = str_replace(' ', '-', strtolower($request->name));
        $item->category_id = $request->category_id;
        $item->description = $request->description;
        $item->save();

        $this->attachItemToMedia($item->id, $request->images);
        $this->removeDeletedMedia($request->deletedImages);
        if (count($request->discounts) > 0) {

            $this->createItemDiscounts($request->discounts, $item->id);
        }
        $this->removeDeletedDiscounts($request->deletedDiscounts);
        //update item price
        // $item_price = ItemPrice::where('item_id', $item->id)->first();
        // if (!$item_price) {
        //     $item_price = new ItemPrice();
        // }
        // $item_price->item_id = $item->id;
        // // $item_price->currency_id = $request->currency_id;
        // $item_price->amount = $request->amount;
        // // $item_price->purchase_price = $request->purchase_price;
        // $item_price->save();
        ItemPrice::updateOrCreate(
            ['item_id' => $item->id],
            ['amount' => $request->amount]
        );
        $title = "Product details modified";
        $description = "Product information for $item->name  was modified by " . $user->name;;
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($item);
    }
    private function removeDeletedDiscounts($deletedDiscounts)
    {
        //
        if (count($deletedDiscounts) > 0) {
            $discounts = ItemDiscount::whereIn('id', $deletedDiscounts)->get();
            foreach ($discounts as $discount) {
                $discount->delete();
            }
        }
    }
    /**
     * Stock Items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stock\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function stock(Request $request, Item $item)
    {
        //
        $user = $this->getUser();
        $stocks = json_decode(json_encode($request->sub_batches));
        $total_quantity = 0;
        foreach ($stocks as $stock) {

            $quantity = $stock->quantity;
            $color = $stock->color;
            $size = $stock->size;
            $item_stock = ItemStock::where(['color' => $color, 'size' => $size, 'item_id' => $item->id])->first();
            if (!$item_stock) {
                $item_stock = new ItemStock();
                $item_stock->quantity_stocked = $quantity;
            } else {

                $item_stock->quantity_stocked += $quantity;
            }
            $item_stock->item_id = $item->id;
            $item_stock->color = $color;
            $item_stock->size = $size;
            $item_stock->save();

            $total_quantity += $quantity;
        }
        $title = "New Product Stock";
        $description = "$total_quantity quantity of $item->name  was stocked by " . $user->name;;
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($item);
    }
    public function giveReview(Request $request)
    {
        $item_review = ItemReview::where(['user_id' => $request->user_id, 'item_id' => $request->item_id])->first();
        if (!$item_review) {

            $item_review = new ItemReview();
        }
        $field = $request->field;
        $item_review->user_id = $request->user_id;
        $item_review->item_id = $request->item_id;
        $item_review->$field = $request->value;
        $item_review->save();

        return response()->json(compact('item_review'), 200);
    }
    public function approveReview(Request $request, ItemReview $review)
    {
        $review->is_published = $request->value;
        $review->save();

        return response()->json(compact('item_review'), 200);
    }
    public function destroyItemTax(Request $request)
    {
        //
        $item = Item::find($request->item_id); // ->taxes()->where('tax_id', $request->tax_id)->first();
        $item->taxes()->detach($request->tax_id);

        //$item_tax->delete();
        return response()->json(null, 204);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        // first log this event
        $user = $this->getUser();
        $title = "Product deleted";
        $description = $item->name . " was removed from list of products by " . $user->name;
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);

        $item->taxes()->detach(); //use detach for pivoted relationship (hasManyThrough)
        $item->price()->delete();
        $item->delete();
        return response()->json(null, 204);
    }
}
