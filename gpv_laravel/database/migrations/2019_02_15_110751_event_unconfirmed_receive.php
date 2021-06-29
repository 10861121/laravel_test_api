<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EventUnconfirmedReceive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW event_unconfirmed_receive AS SELECT 
            event.event_id,
            event.event_name,
            event.event_content,
            event.event_address,
            event.event_point,
            event.event_time,
            event.event_efftime,
            event.event_state,
            gpv_users_data.gud_guid,
            gpv_users_data.gud_nickname,
            gpv_users_data.gud_gender,
            gpv_users_data.gud_mail,
            gpv_users_data.gud_phone
            FROM event_check,gpv_users_data,event WHERE event_check.ec_ereceive=gpv_users_data.gud_guid and event.event_id=event_check.ec_eid
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
