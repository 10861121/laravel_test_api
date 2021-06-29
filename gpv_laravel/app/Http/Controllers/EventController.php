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
class EventController extends Controller
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

    public function DetailedEvent(Request $request){

        $json=          $request->data;
        $Input=         json_decode($json,true);
        $gu_id=         $Input['gu_id'];  //使用者ID
        $startDate=     $Input['startDate'];
        

        $DBevent_tiem=DB::table('event_users_data_view')//查詢過期時間
            ->get();

        foreach ($DBevent_tiem as $value) {
            $Expired=$value->event_efftime;
            if(time()>$Expired)
            {
                $DBevent_efftiem=DB::table('event')//修改過期事件狀態
                    ->where('event_state',5)
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
                ->where('event_release','!=',$gu_id)
                ->whereBetween('event_time',[$startDate, $endDate])
                ->where('event_state','!=',25)
                ->where('event_state','!=',15)
                ->where('event_state','!=',21)
                ->where('event_state','!=',999)        
                ->get();
        }
        else
        {
            $DBevent=DB::table('event_users_data_view')
                ->orderBy('event_time','desc')
                ->where('event_release','!=',$gu_id)
                ->where('event_state','!=',25)
                ->where('event_state','!=',15)
                ->where('event_state','!=',21)
                ->where('event_state','!=',999)        
                ->get();
        }
        

        $number=0;
            foreach($DBevent as $value){

                $time=date("Y-m-d H:i",$value->event_time);
                $efftime=date("Y-m-d H:i",$value->event_efftime);

                $event_id[$number]=         $value->event_id;
                $event_name[$number]=       $value->event_name;
                $event_content[$number]=    $value->event_content;
                $event_address[$number]=    $value->event_address;
                $event_point[$number]=      $value->event_point;
                $event_time[$number]=       $time;
                $event_efftime[$number]=    $efftime;
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
                'gud_nickname'  =>          $gud_nickname,
                'gud_gender' =>             $gud_gender,
                'gud_mail'  =>              $gud_mail,
                'gud_phone'  =>             $gud_phone,
            ];

            return json_encode($json,JSON_UNESCAPED_UNICODE);
        
    }
    public function EventAdd(Request $request){
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $event_name=    $Input['event_name'];
        $event_content= $Input['event_content'];
        $event_address= $Input['event_address'];
        $event_point=   $Input['event_point'];
        $event_efftime= strtotime($Input['event_efftime']);
        $gu_id=         $Input['gu_id'];  //使用者ID

        $pointstate=$this->pointstate($gu_id,$event_point,0);

        $pointstate1=$pointstate['pointstate1'];
        $pointstate2=$pointstate['pointstate2'];
        if($pointstate1==true&&$pointstate2==true)//判斷發佈者點數是否大於獎勵點數
        {
            $add=DB::table('event')//發佈資料新增
                ->insert([
                    'event_name'=>      $event_name,
                    'event_content'=>   $event_content,
                    'event_address'=>   $event_address,
                    'event_point'=>     $event_point,
                    'event_time'=>      time(),//當前發佈時間
                    'event_efftime'=>   $event_efftime,//有效時間(暫定固定變數)
                    'event_release'=>   $gu_id,//發佈者使用者資料ID
                    'event_receive'=>   '',//接收者使用者ID
                 ]);
            if($add==true)
            {
                $rCode=200;
                $msg="creat successfully!";
            }
            else{
                $rCode=501;
                $msg="creat failed!";
            }
        }
        else
        {
            $rCode=501;
            $msg="creat failed!";
        }

        return response()->json(['State' => $rCode, 'Msg' => $msg, 'req' => $request->all()]);

    }
    function pointstate($gu_id,$event_point,$uppoint)//新增點數運算
    {   

        $DBgud=DB::table('gpv_users_data')
            ->where('gud_guid',$gu_id)
            ->first();
        $gud_points=$DBgud->gud_points;//發佈者點數

        $DBevent=DB::table('event')
            ->where('event_release',$gu_id)
            ->where('event_state','!=',25)
            ->get();

        $allpoints=$event_point;

        $pointstate1=false;  //false-沒有大於/true-有大於
        $pointstate2=false;  //判斷事件獎勵點數是否大於等於擁有者 false-沒有大於/true-有大於
        foreach ($DBevent as$value) {//判斷預設事件點數加總有沒有大於使用者
            $point=$value->event_point;
            $allpoints=$allpoints+$point;
        }
        if($gud_points>=($allpoints-$uppoint))
        {
            $pointstate1=true;
        }
        if($gud_points>=$event_point)
        {
            $pointstate2=true;
        }

        $pointstate=
        [
            'pointstate1'   =>$pointstate1,
            'pointstate2'   =>$pointstate2, 
        ];
        return $pointstate;
    }
    // public function EventData(Request $request)
    // {
    //     $json=          $request->data;
    //     $Input=         json_decode($json,true);
    //     $gu_id=         $Input['gu_id'];//使用者
    //     $event_id=      $Input['event_id'];
    //     $DBevent=DB::table('event')
    //         ->where('event_release',$gu_id)
    //         ->where('event_id',$event_id)
    //         ->first();

    //     return json_encode($DBevent,JSON_UNESCAPED_UNICODE); 
    // }
    public function EventUpdate(Request $request){

        $json=          $request->data;
        $Input=         json_decode($json,true);
        $event_name=    $Input['event_name'];
        $event_content= $Input['event_content'];
        $event_address= $Input['event_address'];
        $event_point=   $Input['event_point'];
        $event_efftime= strtotime($Input['event_efftime']);
        $event_id=      $Input['event_id'];

        $DBevent=DB::table('event')
            ->where('event_id',$event_id)
            ->first();
        $uppoint=$DBevent->event_point;
        $gu_id=$DBevent->event_release;//發佈者ID

        $pointstate=$this->pointstate($gu_id,$event_point,$uppoint);
        $pointstate1=$pointstate['pointstate1'];
        $pointstate2=$pointstate['pointstate2'];

        if($pointstate1==true&&$pointstate2==true)//判斷發佈者點數是否大於獎勵點數
        {
            $up=DB::table('event')
                ->where('event_id',$event_id)
                ->update([
                    'event_name'=>      $event_name,
                    'event_content'=>   $event_content,
                    'event_address'=>   $event_address,
                    'event_point'=>     $event_point,
                    'event_time'=>      time(),
                    'event_state'=>     5,
                    'event_efftime'=>   $event_efftime,
                ]);
            if($up==true){
                $rCode=200;
                $msg="update successfully!";
            }    
            else{
                $rCode=501;
                $msg="update failed!";
            }
        }
        else
        {
            $rCode=501;
            $msg="update failed!";
        }
        return response()->json(['State' => $rCode, 'Msg' => $msg, 'req' => $request->all()]);
    }
    public function EventDelete(Request $request)
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $event_id=      $Input['event_id'];

        $delete_event=DB::table('event')
            ->where('event_id',$event_id)
            ->delete();
        $delete_eventcheck=DB::table('event_check')
            ->where('ec_eid',$event_id)
            ->delete();
        if($delete_event==true&&$delete_eventcheck==true)
        {
            $rCode=200;
            $msg="delete successfully!";
        }    
        else
        {
            $rCode=501;
            $msg="delete failed!";
        }
        return response()->json(['State' => $rCode, 'Msg' => $msg, 'req' => $request->all()]);
    }
    
}
