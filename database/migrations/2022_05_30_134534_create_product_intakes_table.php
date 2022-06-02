<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductIntakesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_intakes', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->string('sku');
            $table->integer('imei_number');
            $table->string('serial_number');
            $table->float('sale_price',20)->default('0.0');
            $table->float('retail_price',20)->default('0.0');
            $table->integer('invoice_number')->nullable();
            $table->string('type');
            $table->integer('created_by')->default('0');
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
        Schema::dropIfExists('product_intakes');
    }
}
