<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->bigInteger('service_id')->unsigned();
            $table->bigInteger('customer_id')->unsigned();
            $table->integer('price')->unsigned()->default(0);
            $table->enum('accrual_method', ['points', 'percent']); // способ начисления
            $table->enum('writeoff_method', ['points', 'percent']); // способ списания
            $table->integer('accrual_value')->unsigned()->default(0);
            $table->integer('writeoff_value')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_id')->references('id')->on('businessman_services');
            $table->foreign('customer_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_services');
    }
}
