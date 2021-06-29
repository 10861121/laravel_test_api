<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Input;
use DB;
use Redirect;
use View;
use Image;
use User;
use Validator;
use json_decode;
use Log;
class EventListController extends Controller
{

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        
    }
    public function EventList(Request $request)
    {
        $json=              $request->data;
        $Input=             json_decode($json,true);
        $event_state=       $Input['event_state'];
        
        
        switch ($event_state) {

            case '5'://發佈清單
                return $this->ReleaseList($request);
                break;
            case '11'://未確認清單
                return $this->UnConfirmedList($request);
                break;
            case '12'://該事件未確認所有人員名單
                return $this->UnConfirmed($request);
                break;
            case '13'://已確認人員
                return $this->Confirmed($request);
                break;
            case '15'://接收清單
                return $this->ReceiveList($request);;
                break;
            case '25'://完成清單
                return $this->CompleteList($request);;
                break;
        }
    }
     function ReleaseList(Request $request)//發佈清單
    {
        $json=              $request->data;
        $Input=             json_decode($json,true);
        $event_release=     $Input['gu_id'];
        $startDate=         $Input['startDate'];

        $DBevent_tiem=DB::table('event_users_data_view')//查詢過期時間
            ->get();

        foreach ($DBevent_tiem as $value) {
            $Expired=$value->event_efftime;
            if(time()>$Expired)
            {
                $DBevent_efftiem=DB::table('event')//修改過期事件狀態
                    ->where('event_state','!=',25)
                    ->where('event_state','!=',15)
                    ->where('event_state','!=',21)
                    ->where('event_efftime',$Expired)
                    ->update([
                    'event_state'=>      999,
                    
                ]);
            }
        }
        
        if($startDate!="")
        {
            $startDate=         strtotime($Input['startDate']);
            $endDate=           strtotime($Input['endDate']);
            $orderSelect=       $Input['orderSelect'];
            $orderType=         $Input['orderType'];

            $DBevent=DB::table('event_users_data_view')
                ->orderBy($orderSelect,$orderType)
                ->whereBetween('event_time',[$startDate, $endDate])
                ->where('event_release',$event_release)
                ->get();
                

        }
        else
        {
            $DBevent=DB::table('event_users_data_view')
                ->orderBy('event_time', 'desc')
                ->where('event_release',$event_release)
                ->get();
        }
        
        $DetailedEvent=$this->DetailedEvent($DBevent);

        return $DetailedEvent;
    }
    function ReceiveList(Request $request)//確認清單
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $event_receive= $Input['gu_id'];
        $event_state=   $Input['event_state'];

       $DBevent=DB::table('event_users_data_view')
            ->orderBy('event_time', 'desc')
            ->where('event_receive',$event_receive)
            ->where('event_state',$event_state)
            ->orwhere('event_state','21') 
            ->get();
        
        $DetailedEvent=$this->DetailedEvent($DBevent);
        return $DetailedEvent;
    }
   
    function UnConfirmedList(Request $request)//未確認清單(使用者發出請求還未被確認清單)
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $event_receive=     $Input['gu_id'];

        $DBevent_check=DB::table('event_unconfirmedList_release')
            ->orderBy('event_time', 'desc')
            ->where('ec_ereceive',$event_receive)
            ->get();

        $DetailedEvent=$this->DetailedEvent($DBevent_check);

        return $DetailedEvent;

    }
    function CompleteList(Request $request)//完成清單
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $event_receive= $Input['gu_id'];
        $event_state=   $Input['event_state'];

        $DBevent=DB::table('event_users_data_view')
            ->orderBy('event_time', 'desc')
            ->where('event_receive',$event_receive)
            ->where('event_state',$event_state)
            ->get();
        $DetailedEvent=$this->DetailedEvent($DBevent);

        return $DetailedEvent;

    }

    function UnConfirmed(Request $request) //查看該事件未確認所有人員名單
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $event_id=      $Input['event_id'];

        $DBEventUserData=DB::table('event_unconfirmed_receive')
            ->where('event_id',$event_id)
            ->get();

        $number=0;
        foreach($DBEventUserData as $value){

                $gud_nickname[$number]=     $value->gud_nickname;
                $gud_gender[$number]=       $value->gud_gender;
                $gud_phone[$number]=        $value->gud_phone;
                $gud_id[$number]=           $value->gud_guid;
                $number++;
        }
        $json = [
                'gud_nickname'=>    $gud_nickname,
                'gud_gender'=>      $gud_gender,
                'gud_phone'=>       $gud_phone,
                'gud_id'=>          $gud_id,
        ];

        return json_encode($json,JSON_UNESCAPED_UNICODE);
    }
    function Confirmed(Request $request)//已確認人員
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $event_id=      $Input['event_id'];


        $DBevent=DB::table('event')
            ->where('event_id',$event_id)
            ->first();
        $gud_id=$DBevent->event_receive;//接收者ID

        if($gud_id==true)
        {
            $DBgud=DB::table('gpv_users_data')
                ->where('gud_guid',$gud_id)
                ->first();
            $json = [
                'gud_nickname'=>    $DBgud->gud_nickname,
                'gud_gender'=>      $DBgud->gud_gender,
                'gud_phone'=>       $DBgud->gud_phone,
                'gud_id'=>          $DBgud->gud_id,
            ];

            return json_encode($json,JSON_UNESCAPED_UNICODE);
        }
        
    }
    
    function DetailedEvent($DB)
    {
        $number=0;
        foreach($DB as $value){

                $time=date("Y-m-d H:i",$value->event_time);
                $efftime=date("Y-m-d H:i",$value->event_efftime);

                $event_id[$number]=         $value->event_id;
                $event_name[$number]=       $value->event_name;
                $event_content[$number]=    $value->event_content;
                $event_address[$number]=    $value->event_address;
                $event_point[$number]=      $value->event_point;
                $event_time[$number]=       $time;
                $event_efftime[$number]=    $efftime;
                $event_state[$number]=      $value->event_state;
                $gud_nickname[$number]=     $value->gud_nickname;
                $gud_gender[$number]=       $value->gud_gender;
                $gud_mail[$number]=         $value->gud_mail;
                $gud_phone[$number]=        $value->gud_phone;
                $number++;
            }
            $json = [

                'event_id' =>               $event_id,
                'event_name' =>             $event_name,
                'event_content'  =>         $event_content,
                'event_address' =>          $event_address,
                'event_point'=>             $event_point,
                'event_time'  =>            $event_time,
                'event_efftime' =>          $event_efftime,
                'event_state'=>             $event_state,
                'gud_nickname'  =>          $gud_nickname,
                'gud_gender' =>             $gud_gender,
                'gud_mail'  =>              $gud_mail,
                'gud_phone'  =>             $gud_phone,
            ];
        return json_encode($json,JSON_UNESCAPED_UNICODE);
    }
}
