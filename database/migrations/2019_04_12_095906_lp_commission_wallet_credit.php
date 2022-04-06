<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LpCommissionWalletCredit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lp_super_wallet_credit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('super_wallet_id');
            $table->bigInteger('user_id');
            $table->bigInteger('product_id');
            $table->bigInteger('booking_id');
            $table->string('booking_reference');
            $table->float('account_number',32,0);
            $table->float('amount',32,2);
            $table->string('source',100);
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
        //

        Schema::dropIfExists('lp_super_wallet_credit');
        
    }
}
