<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagentoCustomerGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('magento_customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->bigInteger('tax_class_id');
            $table->string('tax_class_name');
            $table->timestamps();
            $table->timestamp('synced_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('magento_customer_groups');
    }
}
