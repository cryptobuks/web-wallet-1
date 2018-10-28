<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coin');
            $table->integer('coin_id');
            $table->integer('userid');
            $table->string('username');
            $table->string('address');
            $table->string('message')->nullable();
            $table->string('category');
            $table->string('amount');
            $table->integer('confirmations');
            $table->string('txid');
            $table->smallInteger('status')->default(0);
            $table->smallInteger('status_update')->default(0);
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
        Schema::dropIfExists('deposits');
    }
}
