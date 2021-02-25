<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagentoConfigurableProductOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magento_configurable_product_options', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('attribute_id');
            $table->bigInteger('magento_product_id');
            $table->string('label');
            $table->integer('position');
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
        Schema::dropIfExists('magento_configurable_product_options');
    }
}
