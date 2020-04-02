<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockArchivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_archives', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->bigInteger('client_id')->unsigned();
            $table->string('country');
            $table->string('city');
            $table->bigInteger('service_id')->unsigned();
            $table->string('name');
            $table->text('description');
            $table->text('photo');
            $table->date('expires_at');
            $table->boolean('sub_only');
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('service_id')->references('id')->on('service_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_archives');
    }
}
