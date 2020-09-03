<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagentoProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magento_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->string('name')->index();
            $table->string('slug')->nullable()->index();
            $table->decimal('price', 15, 4)->default(0.00);
            $table->integer('quantity')->default(0);
            $table->tinyInteger('is_in_stock')->default(0);
            $table->tinyInteger('status');
            $table->integer('visibility');
            $table->string('type');
            $table->decimal('weight', 15, 4)->default(0.00);
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
        Schema::dropIfExists('magento_products');
    }
}
