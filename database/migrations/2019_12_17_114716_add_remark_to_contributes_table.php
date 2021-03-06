<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarkToContributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tasks')){
            return;
        }
        Schema::table('contributes', function (Blueprint $table) {
            if (!Schema::hasColumn('contributes', 'remark')) {
                $table->string('remark')->nullable()->comment('备注');
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
        Schema::table('contributes', function (Blueprint $table) {
            //
        });
    }
}
