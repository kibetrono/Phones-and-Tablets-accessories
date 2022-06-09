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
            $table->integer('imei_number');
            $table->string('serial_number');
            $table->unsignedBigInteger('delivery_men_id')->nullable();

            $table->integer('quantity_delivered')->default('1');
            $table->float('sale_price',20)->default('0.0');
            $table->float('retail_price',20)->default('0.0');
            $table->integer('invoice_number')->nullable();
            $table->integer('returned')->default('0');
            $table->string('supplier_person');
            $table->integer('delivery_person');
            $table->string('receiving_person');
            $table->foreign('delivery_men_id')->references('id')->on('delivery_men')->onDelete('cascade');
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
