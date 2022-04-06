<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CommissionsTarrif extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lp_commission_tariff', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('commission_id');
            $table->string('tariff_name');
            $table->float('min_value',32,2)->default(0.0);
            $table->float('max_value',32,2)->default(0.0);
            $table->float('commission_tariff_value');
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
        Schema::dropIfExists('lp_commission_tariff');
        
    }
}