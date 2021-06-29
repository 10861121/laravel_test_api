<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EventUsersDataView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
          CREATE VIEW event_users_data_view AS SELECT 
          event.event_id,
          event.event_name,
          event.event_content,
          event.event_address,
          event.event_point,
          event.event_time,event.event_efftime,
          event.event_release,
          event.event_receive,
          event.event_state,
          gpv_users_data.gud_nickname,
          gpv_users_data.gud_gender,
          gpv_users_data.gud_mail,
          gpv_users_data.gud_phone
          FROM event,gpv_users_data WHERE event.event_release=gpv_users_data.gud_guid
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
