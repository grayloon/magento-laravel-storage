<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfigurableProdDataToCustomAttributeTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('magento_custom_attribute_types', function (Blueprint $table) {
            $table->string('type')->nullable()->after('options');
            $table->bigInteger('attribute_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_attribute_types', function (Blueprint $table) {
            //
        });
    }
}
