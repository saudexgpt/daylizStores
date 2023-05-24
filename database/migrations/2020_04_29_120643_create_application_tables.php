<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('picture')->nullable();
            $table->boolean('enabled');
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('category_id')->references('id')->on('categories')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['name']);
        });
        Schema::create('item_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->double('amount', 10, 2);
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('item_id')->references('id')->on('items')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index('item_id');
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->integer('location_id');
            $table->string('order_number');
            $table->enum('order_status', ['Pending', 'On Transit', 'Delivered'])->default('Pending');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->double('amount', 10, 2);
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['order_number', 'deleted_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('item_id')->nullable();
            $table->integer('quantity');
            $table->double('price', 10, 2);
            $table->double('total', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('items');
        Schema::dropIfExists('item_prices');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_items');
    }
}
