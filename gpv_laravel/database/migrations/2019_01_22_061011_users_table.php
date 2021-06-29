<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gpv_users', function (Blueprint $table) {
            $table->increments('gu_id')->comment('使用者帳號ID');
            $table->string('gut_number')->comment('帳號類型回傳值');
            $table->string('gu_authcode')->comment('server產生驗證碼');
            $table->string('gu_authexpired')->comment('可驗證時間');
            $table->string('gu_inviteID')->comment('邀請人ID');
            $table->string('gut_id')->comment('帳號類型');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gpv_users');
    }
}
