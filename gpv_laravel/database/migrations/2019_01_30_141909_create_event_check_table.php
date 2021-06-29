<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventCheckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_check', function (Blueprint $table) {
            
            $table->increments('ec_id')->comment('事件中繼站ID');
            $table->string('ec_eid')->comment('事件ID');
            $table->string('ec_erelease')->comment('發佈者ID');
            $table->string('ec_ereceive')->comment('接收者ID');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_check');
    }
}
