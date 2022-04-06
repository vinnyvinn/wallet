<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LpCommissionWalletBalance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lp_super_wallet_balance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('super_wallet_id');
            $table->bigInteger('user_id');
            $table->float('account_number', 32,0);
            $table->float('total_credit', 32);
            $table->float('total_debit', 32);
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::dropIfExists('lp_super_wallet_balance');
    }
}
