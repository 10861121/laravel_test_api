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
class EventStateController extends Controller
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
    

    public function click_btn(Request $request)
    {

        $json=              $request->data;
        $Input=             json_decode($json,true);
        $event_id=          $Input['event_id']; //事件ID
        $gu_id=             $Input['gu_id'];    //接收者ID


        $DBgud=DB::table('gpv_users_data')//透過接收者ID查詢個人資料表
            ->where('gud_guid',$gu_id)
            ->first();
            
        $DBevent=DB::table('event')//透過事件ID查詢發佈者ID
            ->where('event_id',$event_id)
            ->first();

        $ec_erelease=       $DBevent->event_release;   //發佈者ID
        $ec_ereceive=       $DBgud->gud_guid;          //接收者ID


        $DBevent2=DB::table('event')//判斷事件的發佈者，與接收者是否同一人
            ->where('event_id',$event_id)
            ->where('event_release',$ec_ereceive)
            ->first();

        $DBec1=DB::table('event_check')//判斷同個事件接收者是否重複接取
            ->where('ec_eid',$event_id)
            ->where('ec_ereceive',$ec_ereceive)
            ->first();
        if($DBevent2==false&&$DBec1==false)//判斷是否重複接收事件or發布接收同一個人
        {
            $add=DB::table('event_check')
                ->insert([
                    'ec_eid'=>        $event_id,
                    'ec_erelease'=>   $ec_erelease,
                    'ec_ereceive'=>   $ec_ereceive,
                ]);

            $up=DB::table('event')
                ->where('event_id',$event_id)
                ->update([
                    'event_state'=>11,
                ]);
                // return $up;
            if($add==true)
            {
                $rCode=200;
                $msg="creat successfully!";
            }
            else
            {
                $rCode=501;
                $msg="creat failed!";
            }
        }
        else
        {
                $rCode=400;
                $msg="Repeated";
        }
         return response()->json(['State' => $rCode, 'Msg' => $msg, 'req' => $request->all()]);
        
    }
    public function del_click_btn(Request $request)
    {
        $json=              $request->data;
        $Input=             json_decode($json,true);
        $event_id=          $Input['event_id']; //事件ID
        $gu_id=             $Input['gu_id'];    //接收者ID

        $DBgud=DB::table('gpv_users_data')//透過接收者ID查詢個人資料表
            ->where('gud_guid',$gu_id)
            ->first();
            
        $DBevent=DB::table('event')//透過事件ID查詢發佈者ID
            ->where('event_id',$event_id)
            ->first();

        $ec_erelease=       $DBevent->event_release;   //發佈者ID
        $ec_ereceive=       $DBgud->gud_guid;          //接收者ID

        

        $del=DB::table('event_check')
            ->where('ec_eid',$event_id)
            ->where('ec_ereceive',$ec_ereceive)
            ->delete();

        $DBec=DB::table('event_check')
            ->where('ec_eid',$event_id)
            ->get();
            

        if(count($DBec)==0)
        {
             $up=DB::table('event')
                ->where('event_id',$event_id)
                ->update([
                    'event_state'=>5,
                ]);
                
        }
         
        if($del==true)
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

    public function click_confirm_btn(Request $request){

        $json=              $request->data;
        $Input=             json_decode($json,true);
        $event_id=          $Input['event_id']; //事件ID
        $event_receive=     $Input['gu_id'];    //接收者ID

        $up=DB::table('event')
            ->where('event_id',$event_id)
            ->update([
                'event_receive'=>$event_receive,
                'event_state'=>15,
        ]);

        $delete=DB::table('event_check')
            ->where('ec_eid',$event_id)
            ->delete();
        
        if($delete==true)
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

    public function click_uncompleted_btn(Request $request)
    {
        $json=              $request->data;
        $Input=             json_decode($json,true);
        $event_id=          $Input['event_id']; //事件ID
        
        $DBevent=DB::table('event')//判斷同個事件接收者是否重複接取
            ->where('event_id',$event_id)
            ->first();
        $eventstate=$DBevent->event_state;

        if($eventstate==21)
        {
            $rCode=400;
            $msg="Repeated!";
        }
        else
        {
            $up=DB::table('event')
                ->where('event_id',$event_id)
                ->update([
                'event_state'=>21,
            ]);
            if($up==true)
            {
                $rCode=200;
                $msg="update successfully!";
            }
            else
            {
                $rCode=501;
                $msg="update failed!";
            }
        }
       

        return response()->json(['State' => $rCode, 'Msg' => $msg, 'req' => $request->all()]);
    }
    public function click_completed_btn(Request $request)
    {
        $json=              $request->data;
        $Input=             json_decode($json,true);
        $event_id=          $Input['event_id']; //事件ID

        $DBevent=DB::table('event')
        ->where('event_id',$event_id)
        ->first();
        $json = [
            'event_point' =>    $DBevent->event_point,
            'event_state' =>    $DBevent->event_state,
            'event_release' =>  $DBevent->event_release,
            'event_receive' =>  $DBevent->event_receive,
            'event_id'=>        $event_id,
        ];
     
        $result= $this->PointsOperation($json);
        return $result;
    }

    function PointsOperation($json)
    {
        $event_point=   $json['event_point'];//事件獎勵
        $event_state=   $json['event_state'];//事件狀態
        $event_id=      $json['event_id'];//事件ID
        $releaseID=     $json['event_release'];//發佈者ID
        $receiveID=     $json['event_receive'];//領取者ID
        

        $DBrelease=DB::table('gpv_users_data')//發佈者點數
            ->where('gud_guid',$releaseID)
            ->first();
        $releasePoint=$DBrelease->gud_points;

        $DBreceive=DB::table('gpv_users_data')//領取者點數
            ->where('gud_guid',$receiveID)
            ->first();
        $receivePoint=$DBreceive->gud_points;


        if($event_state==21)//確認領取者是否點即完成 
        {
            $DBgud1=DB::table('gpv_users_data')//發佈者扣除點數
                ->where('gud_guid',$releaseID)
                ->update([
                    'gud_points'=>$releasePoint-$event_point,
                 ]);        
            $DBgud2=DB::table('gpv_users_data')//接收者增加點數
                ->where('gud_guid',$receiveID)
                ->update([
                    'gud_points'=>$receivePoint+$event_point,
                 ]);
            if($DBgud1==true&&$DBgud2==true)
            {
                $up=DB::table('event')
                    ->where('event_id',$event_id)
                    ->update([
                        'event_state'=>25,
                ]);

                $rCode=200;
                $msg="update successfully!";
            }
            else
            {
                $rCode=501;
                $msg="update failed!";
            }
        }
        else
        {
            $rCode=501;
            $msg="update failed!";
        }

        
            return response()->json(['State' => $rCode, 'Msg' => $msg]);
    }
}
