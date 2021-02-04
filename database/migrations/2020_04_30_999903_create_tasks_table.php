<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tasks')){
            return;
        }

        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('任务名称');

            $table->text('details')->nullable()->comment('描述');
            $table->string('icon')->nullable()->comment("任务图标");
            $table->string('background_img')->nullable()->comment('任务背景图');

            $table->string('group', 20)->nullable()->comment('任务分组：新人任务|每日任务|自定义任务|实时任务|贡献任务...');
            $table->integer('type')->nullable()->comment('任务类型：0:新人任务 1:每日任务 2:自定义任务 3:实时任务 - 逻辑用途');
            $table->boolean('status')->default(0)->comment('状态: 0:删除 1:展示');

            $table->json('resolve')->nullable()->comment('解析json，留给前端开发来发挥的json信息');
            $table->json('reward')->nullable()->comment('奖励json 智慧点，贡献，精力点');

            $table->integer('review_flow_id')->nullable()->commit('任务模版ID');
            $table->integer('max_count')->default(0)->commit('最多完成的次数');
            $table->integer('parent_task')->nullable()->comment('父任务');

            //FIXME: 这两个时间只有喝水睡觉的 assignment 上有用
            // $table->timestamp('start_at')->nullable()->comment('开始时间');
            $table->timestamp('end_at')->nullable()->comment('截止时间');
            $table->string('description')->default('');

            $table->string('task_action')->nullable()->comment('任务对应的行为，如浏览，点赞，评论等');
            $table->string('relation_class')->nullable()->comment('任务对应的类，如合集，动态等');
            $table->json('task_object')->nullable()->comment('任务指定的对象，如collections,posts数组等');

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
        Schema::dropIfExists('tasks');
    }
}
