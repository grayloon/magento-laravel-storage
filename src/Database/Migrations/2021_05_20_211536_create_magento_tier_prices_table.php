<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagentoTierPricesTable extends Migration
{
    public function up()
    {
        Schema::create('magento_tier_prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('magento_product_id')->index();
            $table->decimal('value', 15, 4)->default(0.00);
            $table->integer('quantity')->default(0);
            $table->text('extension_attributes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('magento_tier_prices');
    }
}
