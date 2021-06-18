<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('items')) {
            return;
        }
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('名称');
            $table->string('alias', 50)->default('')->index();
            $table->string('description')->nullable()->comment('描述');
            $table->integer('gold')->comment('智慧点');
            $table->string('resolve_functions')->comment('解析');
            $table->unsignedInteger('count')->default(1)->comment('道具数量');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
