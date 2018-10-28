<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coin');
            $table->string('coin_name');
            $table->boolean('deposits')->default(0);
            $table->boolean('withdrawals')->default(0);
            $table->boolean('message');
            $table->string('withdraw_fees');
            $table->string('min_withdraw');
            $table->boolean('is_fiat')->default(0);
            $table->boolean('is_auto')->default(0);
            $table->smallInteger('confirmations')->default(0);
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
        Schema::dropIfExists('coins');
    }
}
