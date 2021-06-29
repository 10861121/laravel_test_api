<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event', function (Blueprint $table) {
            $table->increments('event_id')->comment('事件ID');
            $table->string('event_name')->comment('事件名稱');
            $table->string('event_content')->comment('事件內容');
            $table->string('event_address')->comment('事件地址');
            $table->string('event_latitude')->comment('事件經度');
            $table->string('event_longitude')->comment('事件緯度');
            $table->string('event_point')->comment('事件點數');
            $table->string('event_state')->comment('事件狀態')->default(5);
            $table->string('event_time')->comment('事件發佈時間');
            $table->string('event_efftime')->comment('發布有效時間');
            $table->string('event_release')->comment('發佈者ID');
            $table->string('event_receive')->comment('接收者ID');
             
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event');
    }
}
