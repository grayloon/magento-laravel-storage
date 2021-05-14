<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStreet2AndCompanyToMagentoCustomerAddresses extends Migration
{
    public function up()
    {
        Schema::table('magento_customer_addresses', function (Blueprint $table) {
            $table->text('street2')->nullable()->after('street');
            $table->string('company')->nullable()->after('last_name');
        });
    }
}
