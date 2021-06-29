<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GpvUsersDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gpv_users_data', function (Blueprint $table) {
            $table->increments('gud_id')->comment('使用者資料ID');
            $table->string('gud_name')->comment('使用者姓名');
            $table->string('gud_nickname')->comment('使用者暱稱');
            $table->string('gud_gender')->comment('性別');
            $table->string('gud_mail')->comment('信箱');
            $table->string('gud_phone')->comment('電話');
            $table->string('gud_address')->comment('地址');
            $table->string('gud_sticker')->comment('大頭貼照片');
            $table->string('gud_points')->comment('好人點數');
            $table->string('gud_guid')->comment('帳號ID');
            $table->string('gud_titleid')->comment('稱號ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gpv_users_data');
    }
}
