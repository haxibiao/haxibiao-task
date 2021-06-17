<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('名称');
            $table->unsignedBigInteger('score')->default(0)->comment('分值');
            $table->tinyInteger('status')->default(0)->comment('状态');
            $table->unsignedBigInteger('count')->default(0)->comment('总数');
            $table->json('data')->nullable()->comment('资源数据');
            $table->string('introduction')->default('');
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
        Schema::dropIfExists('medals');
    }
}
