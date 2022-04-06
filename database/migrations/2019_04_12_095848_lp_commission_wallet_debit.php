<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LpCommissionWalletDebit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lp_super_wallet_debit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('super_wallet_id');
            $table->bigInteger('user_id');
            $table->float('account_number',32);
            $table->float('amount',32);
            $table->string('destination');
            $table->bigInteger('destination_id');
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
        
        Schema::dropIfExists('lp_commission_wallet_debit');
        
    }
}
