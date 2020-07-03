<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReviewClassToReviewFlows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_flows', function (Blueprint $table) {
            if (!Schema::hasColumn('review_flows', 'review_class')) {
                $table->string('review_class')->nullable()->comment('任务关联的模型的class 去掉命名空间');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_flows', function (Blueprint $table) {
            //
        });
    }
}
