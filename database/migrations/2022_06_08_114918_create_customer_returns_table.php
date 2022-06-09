<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_returns', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->integer('imei_number');
            $table->string('serial_number');
            $table->integer('quantity_delivered')->default('1');
            $table->integer('invoice_number')->nullable();
            $table->string('returning_customer');
            $table->string('receiving_person');
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
        Schema::dropIfExists('customer_returns');
    }
}
