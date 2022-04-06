<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CommissionsRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lp_commission_rate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sector_id')->default(0);// I considered the sectors
            $table->enum('user_type', ['promoter', 'agent', 'merchant', 'flexpay']);
            $table->string('commission_name');
            $table->enum('category_commissioned', ['no', 'yes'])->default('no');
            $table->enum('commission_type', ['onPercentage', 'onTariff'])->default('onPercentage');
            $table->enum('applied', ['onBooking','onPayment', 'onConfirmation', 'onCompletion'])->default('onCompletion');
            $table->enum('commission_cost_inclusive', ['inBookingCost', 'extraBookingCost'])->default('inBookingCost');
            $table->enum('tariff_type', ['isFixed', 'isGraded'])->default('isGraded');
            $table->enum('commission_occurrence', ['perCustomer', 'perMerchant', 'perBooking'])->default('perBooking');
            $table->float('percentage_value');
            $table->timestamps();
            $table->softDeletes();

            //

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lp_commission_rate');
    }
}

