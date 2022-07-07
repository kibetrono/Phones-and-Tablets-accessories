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
            $table->integer('imei_number')->unique();
            $table->string('serial_number')->unique();
            $table->unsignedBigInteger('product_service_id');
            $table->unsignedBigInteger('delivery_man_id');
            $table->unsignedBigInteger('vender_id');
            $table->integer('returning_person_id')->default('0');
            $table->integer('quantity_delivered')->default('1');
            $table->float('sale_price',20)->default('0.0');
            $table->float('retail_price',20)->default('0.0');
            $table->string('color');
            $table->integer('invoice_number')->nullable();
            $table->integer('returned')->default('0');
            $table->string('status')->default('received');
            $table->string('supplier_person');
            $table->string('delivery_person');
            $table->string('receiving_person');
            $table->string('returning_person')->nullable();
            
            $table->integer('created_by')->default('0');
            $table->timestamps();
            $table->foreign('delivery_man_id')->references('id')->on('delivery_men')->onDelete('cascade');
            $table->foreign('product_service_id')->references('id')->on('product_services')->onDelete('cascade');
            $table->foreign('vender_id')->references('id')->on('venders')->onDelete('cascade');
 
        });
        
        Schema::table('product_intakes', function ($table) {
            $table->integer('imei_number')->unique(); //notice the parenthesis I added
        });

        Schema::table('product_intakes', function ($table) {
            $table->string('serial_number')->unique(); //notice the parenthesis I added
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
