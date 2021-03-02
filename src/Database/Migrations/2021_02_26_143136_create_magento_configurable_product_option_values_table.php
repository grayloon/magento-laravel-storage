<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagentoConfigurableProductOptionValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magento_configurable_product_option_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('magento_configurable_product_option_id');
            $table->string('value');
            $table->timestamps();
            $table->timestamp('synced_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('magento_configurable_product_option_values');
    }
}
