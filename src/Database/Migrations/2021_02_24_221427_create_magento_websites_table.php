<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagentoWebsitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magento_websites', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
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
        Schema::dropIfExists('magento_websites');
    }
}
