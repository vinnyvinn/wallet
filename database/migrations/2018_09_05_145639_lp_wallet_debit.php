<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LpWalletDebit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lp_wallet_debit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('wallet_id');
            $table->bigInteger('destination_id');
            $table->bigInteger('money_out_id');
            $table->bigInteger('user_id');
            $table->float('account_number',32);
            $table->float('amount',32);
            $table->string('destination');
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
        Schema::drop('lp_wallet_debit');
    }
}
