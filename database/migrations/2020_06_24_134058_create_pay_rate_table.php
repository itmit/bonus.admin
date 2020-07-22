<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayRateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_rate', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->bigInteger('client_id')->unsigned();
            $table->bigInteger('rate_id')->unsigned();
            $table->text('payment_id')->nullable();
            $table->integer('count_rates')->unsigned()->default(0);
            $table->boolean('is_successful')->default(0);

            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('rate_id')->references('id')->on('rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_rate');
    }
}
