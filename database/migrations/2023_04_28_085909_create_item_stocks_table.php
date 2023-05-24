<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->integer('quantity_stocked');
            $table->integer('sold');
            $table->integer('damaged');
            $table->integer('balance');
            $table->timestamps();
        });

        Schema::create('item_media', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->string('link');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_stocks');
        Schema::dropIfExists('item_media');
    }
};
