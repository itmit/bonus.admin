<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessmanServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('businessman_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->bigInteger('businessmen_id')->unsigned();
            $table->bigInteger('service_type_id')->unsigned()->nullable();
            $table->bigInteger('service_item_id')->unsigned();
            $table->enum('accrual_method', ['points', 'percent']); // способ начисления
            $table->enum('writeoff_method', ['points', 'percent']); // способ списания
            $table->integer('accrual_value')->default(0);
            $table->integer('writeoff_value')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('businessmen_id')->references('id')->on('clients');
            $table->foreign('service_item_id')->references('id')->on('service_items');
            $table->foreign('service_type_id')->references('id')->on('service_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businessman_services');
    }
}
