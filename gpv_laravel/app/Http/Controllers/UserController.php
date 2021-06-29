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
class UserController extends Controller
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
    
    public function UserDataAdd(Request $request)
    {
    
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $gud_nickname=  $Input['gud_nickname'];//暱稱
        $gud_gender=    $Input['gud_gender'];//性別
        $gud_mail=      $Input['gud_mail'];//信箱
        $gud_phone=     $Input['gud_phone'];//手機
        $gu_id=         $Input['gu_id']; //=====字串轉換  strval=====
        $invite_code=   $Input['invite_code']; //認證碼

        $DBinvite=DB::table('gpv_users_invitecode')
            ->where('gui_invite_code',$invite_code)
            ->first();
        

        if($DBinvite==true)
        {
            $gui_guid=$DBinvite->gui_guid;

            $up=DB::table('gpv_users')
                ->where('gu_id',$gu_id)
                ->update([
                    'gu_inviteID'=> $gui_guid,
                ]);

            $del=DB::table('gpv_users_invitecode')
                ->where('gui_invite_code',$invite_code)
                ->where('gui_guid',$gui_guid)
                ->delete();
       

            $up=DB::table('gpv_users_data')
                ->where('gud_guid',$gu_id)
                ->update([
                    'gud_nickname'=>    $gud_nickname,
                    'gud_gender'=>      $gud_gender,
                    'gud_mail'=>        $gud_mail,
                    'gud_phone'=>       $gud_phone,
                ]);
            $rCode=200;
            $msg="update successfully!";
        }
        
        else{
            $rCode=501;
            $msg="update failed!";
        }
        return response()->json(['State' => $rCode, 'Msg' => $msg, 'req' => $request->all()]);
    }

    public function UserData(Request $request)
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $gu_id=         $Input['gu_id'];//使用者

        $DBgud=DB::table('gpv_users_data')
            ->where('gud_guid',$gu_id)
            ->first();
        $DBinvite=DB::table('gpv_users_invitecode')
            ->where('gui_guid',$gu_id)
            ->first();

        $encodeDB=          json_encode($DBgud,JSON_UNESCAPED_UNICODE);
        if($DBinvite==true)
        {
            $invite_expired=$DBinvite->gui_invite_expired;
        }
        else
        {
            $invite_expired=0;
        }

        if(time()>$invite_expired)
        {
            $del=DB::table('gpv_users_invitecode')
                ->where('gui_guid',$gu_id)
                ->delete();
            $rCode=500;
            $msg="Expired!";
            $invite_code="";
        }
        else
        {
            $rCode=200;
            $msg="UnExpired!";
            $invite_code=$DBinvite->gui_invite_code;
        }
        return response()->json(['State' => $rCode, 'Msg' => $msg,'code'=>$invite_code, 'DBgud' => $encodeDB]);
        
    }

    public function UserDataUpdate(Request $request)
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $gud_name=      $Input['gud_name'];//真實姓名
        $gud_nickname=  $Input['gud_nickname'];//暱稱
        $gud_gender=    $Input['gud_gender'];//性別
        $gud_mail=      $Input['gud_mail'];//信箱
        $gud_phone=     $Input['gud_phone'];//手機
        $gud_address=   $Input['gud_address'];//地址
        $gud_sticker=   $Input['gud_sticker'];//大頭貼
        $gu_id=         $Input['gud_guid']; //=====字串轉換  strval=====
        // return $gu_id;
        $up=DB::table('gpv_users_data')
            ->where('gud_guid',$gu_id)
            ->update([
                'gud_name'=>        $gud_name,
                'gud_nickname'=>    $gud_nickname,
                'gud_gender'=>      $gud_gender,
                'gud_mail'=>        $gud_mail,
                'gud_phone'=>       $gud_phone,
                'gud_address'=>     $gud_address,
                'gud_sticker'=>     $gud_sticker,
            ]);
        if($up==true){
            $rCode=200;
            $msg="update successfully!";
        }    
        else{
            $rCode=501;
            $msg="update failed!";
        }
        return response()->json(['State' => $rCode, 'Msg' => $msg, 'req' => $request->all()]);
    }

    public function UserSideMenu(Request $request)
    {
        $json=          $request->data;
        $Input=         json_decode($json,true);
        $gu_id=  $Input['gu_id'];//使用者

        $DBgud=DB::table('gpv_users_data')
            ->where('gud_guid',$gu_id)
            ->first();

        $json = [
            'sidemenu_name' =>  $DBgud->gud_nickname,
            'sidemenu_email' =>  $DBgud->gud_mail,
            'sidemenu_point' =>  $DBgud->gud_points,
        ];
        return json_encode($json,JSON_UNESCAPED_UNICODE);
    }

    public function invite_btn(Request $request)
    {
        $json=              $request->data;
        $Input=             json_decode($json,true);
        $gu_id=             $Input['gu_id'];//使用者
        $invite_code=       rand(9999,1000);//產生4位數的數字
        $invite_expired=    time()+86400;//1小時後  //time()當前時間所有秒數

        $DBinvite=DB::table('gpv_users_invitecode')
            ->get();

        foreach ($DBinvite as $value) {

            if($value->gui_invite_code==$invite_code)
            {
                 $invite_code=rand(9999,1000);//產生4位數的數字
            }
        }

        $add=DB::table('gpv_users_invitecode')
            ->insert([
                    'gui_invite_code'=>        $invite_code,
                    'gui_invite_expired'=>     $invite_expired,
                    'gui_guid'=>                $gu_id,
            ]);

        if($add=true)
        {
            $rCode=200;
            $msg="create successfully!";
        }else
        {
            $rCode=501;
            $msg="create failed!";
        }   

         return response()->json(['State' => $rCode, 'Msg' => $msg , 'code'=>$invite_code]);
    }
}
