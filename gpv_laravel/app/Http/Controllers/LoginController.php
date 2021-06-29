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
class LoginController extends Controller
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




    public function Split_inlet(Request $request){


        $json=      $request->data;
        $Input=     json_decode($json,true);
        $gut_id=    $Input['gut_id'];

        // Log::info($gut_id);

        switch ($gut_id) {
            case '1':
                 return "FBLogin";
                break;
            case '2':
                return $this->GoogleLogin($request);
                break;
            case '3':
                return "GPVLogin";
                break;
        }

    }


    public function valifySignature(Request $request)
    {
        $isValid=false;

        $json=      $request->data;
        $Input=     json_decode($json,true); 
        $signature= $Input['signature'];//App算出的認證碼ABC
        $app_name=  $Input['app_name'];//App名稱
        $gut_id=    $Input['gut_id'];//帳號類型
        $timestamp= $Input['timestamp'];//時間戳

        $countSignature = $this->countSignature($timestamp);
        if($signature===$countSignature && $app_name==="gpv"){
            // $app_name="gpv";
            $isValid=true;
        }
        return $isValid;
    }

    function countSignature($timestamp){
        // 演算法 
        //暫定死資料ABC
        return "ABC";
    }

    private function GoogleLogin (Request $request)
    {
       
        $isValid=$this->valifySignature($request);

        if($isValid!=true){
            $rCode=501;
            $msg="signatrue is not valid";
            return response()->json(['State' => $rCode, 'Msg' => $msg, 'req' => $request->all()]);
        }

        $Input=         $request->all();
        $json=          $Input['data'];
        $Input=         json_decode($json,true);    
        $gut_number=    $Input['gut_number'];//回傳值
        $gut_id=        $Input['gut_id'];//帳號類型ID
        $timestamp=     $Input['timestamp'];//APP傳送時間
        


        $DBgpv_users=DB::table('gpv_users')
            ->where('gut_number',$gut_number)//驗證google登入回傳直
            ->where('gut_id',$gut_id)    //驗證google登入d
            ->first();
        
        // return $gud_guid;
         // Log::info($DBgpv_users);
        if($DBgpv_users==true)//回傳帳號驗證成功
        {
            $rCode=200;
            $msg="user is existed!";
            $gu_id=$DBgpv_users->gu_id;//取得當前帳號ID

            $DBgpv_users_data=DB::table('gpv_users_data')
                ->where('gud_guid',$gu_id)
                ->first();
                
            if($DBgpv_users_data->gud_nickname==""||$DBgpv_users_data->gud_gender==""||$DBgpv_users_data->gud_mail==""||$DBgpv_users_data->gud_phone==""){
                $rCode=501;
                $msg="userdata is null";
            }
        }
        else
        {
            $auth_code=rand(9999,1000);//產生4位數的數字
            $auth_expired=time()+3600;//1小時後  //time()當前時間所有秒數

            $add=DB::table('gpv_users')//建立新帳號
                ->insert([
                    'gut_number'=>      $gut_number,
                    'gu_authcode'=>     $auth_code,
                    'gu_authexpired'=>  $auth_expired,
                    'gu_inviteID'=>     "",
                    'gut_id'=>          $gut_id,

                ]);

            $newDBgu=DB::table('gpv_users')//新增使用者資料
                ->where('gut_number',$gut_number)
                ->where('gut_id',$gut_id)
                ->first();
            $gu_id=$newDBgu->gu_id;

            $data=DB::table('gpv_users_data')//建立使用者空資料
                ->insert([
                    'gud_name'=>        "",
                    'gud_nickname'=>    "",
                    'gud_gender'=>      "",
                    'gud_mail'=>        "",
                    'gud_phone'=>       "",
                    'gud_address'=>     "",
                    'gud_sticker'=>     "",
                    'gud_guid'=>        $gu_id,
                    'gud_titleid'=>     "",
                 ]);
            

            if($add=true&&$data=true){
                $rCode=200;
                $msg="create successfully!";
            }else{
                $rCode=501;
                $msg="create failed!";
            }    
        }
        
         $newDBgu=DB::table('gpv_users')//驗證認證碼
            ->where('gut_number',$gut_number)
            ->where('gut_id',$gut_id)
            ->first();

        if($newDBgu->gu_authexpired<$timestamp){

            $auth_code=rand(9999,1000);//產生4位數的數字
            $auth_expired=time()+3600;//1小時後  //time()當前時間所有秒數

            $newupDBgu=DB::table('gpv_users')
                ->where('gut_number',$gut_number)
                ->where('gut_id',$gut_id)
                ->update([
                    'gu_authcode'=>     $auth_code,
                    'gu_authexpired'=>  $auth_expired,
                ]);
        }
        else{
            $auth_code=$newDBgu->gu_authcode;
            $auth_expired=$newDBgu->gu_authexpired;
        }

        return response()->json(['State' => $rCode,'gu_id'=>$gu_id,'Msg' => $msg,'auth_code'=>$auth_code,'auth_expired'=>$auth_expired, 'DBgpv_users' => $DBgpv_users, 'req' => $request->all()]);
    }
    public function VerifyLogin(Request $request){

        $json=          $request->data;
        $Input=         json_decode($json,true);
        $en=            json_encode($json,true);
        $app_name=      $Input['app_name'];
        $gut_number=    $Input ['gut_number'];
        $gut_id=        $Input['gut_id'];
        $gu_authcode=   $Input['auth_code'];

        $newDBgu=DB::table('gpv_users')//取得認證碼
            ->where('gut_number',$gut_number)
            ->where('gut_id',$gut_id)
            ->where('gu_authcode',$gu_authcode)
            ->first();
        $gu_id=$newDBgu->gu_id; 
        $DBgpv_users_data=DB::table('gpv_users_data')
                ->where('gud_guid',$gu_id)
                ->first();
        

        if($newDBgu==true&&$app_name==="gpv"){
            if($newDBgu->gu_authexpired<time()){
                $rCode=401;
                $msg="validation failed!";
            }
            else{
                $rCode=200;
                $msg="validation successfully!";
            }
        }
        else{
            $rCode=401;
            $msg="validation failed!";
        }

        if($DBgpv_users_data->gud_nickname==""||$DBgpv_users_data->gud_gender==""||$DBgpv_users_data->gud_mail==""||$DBgpv_users_data->gud_phone==""){
                $rCode=501;
                $msg="userdata is null";
        }
        return response()->json(['State' => $rCode,'Msg' => $msg,'req' => $en]);

    }
}
