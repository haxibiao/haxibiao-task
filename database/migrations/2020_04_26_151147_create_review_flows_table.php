<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('review_flows');

        Schema::create('review_flows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('模版名');
            $table->string('review_class')->nullable()->comment('任务关联的模型的class 最后的Name');
            $table->json('check_functions')->nullable()->comment('任务检查流程函数名');
            $table->boolean('need_owner_review')->default(false)->comment('是否需要任务建立者Review');
            $table->boolean('need_offical_review')->default(false)->comment('是否需要官方人员Review');
            $table->tinyInteger('type')->default(0)->comment('模版类型: 0 运营选用 1 用户可选用');
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
        Schema::dropIfExists('review_flows');
    }
}
