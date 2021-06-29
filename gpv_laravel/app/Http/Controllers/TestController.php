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
use mail;
class TestController extends Controller
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
    // public function Split inlet(){
    //     switch ($gut_id) {
    //         case '1':
    //             return "FBLogin"
    //             break;
    //         case '2':
    //             return "GoolgeLogin"
    //             break;
    //         case '3':
    //             return "GPVLogin"
    //             break;
    //        }
    // }
    public function GoogleLogin (Request $request)
    {
        $Input=$request->all();

        
        $gut_number=    $Input['data']['gut_number'];
        $gut_id=        $Input['data']['gut_id'];

        
        $DBgpv_users=DB::table('gpv_users')
            ->where('gut_number',$gut_number)
            ->where('gut_id','2')    //驗證google登入d
            ->first();

        
         // Log::info($DBgpv_users->gut_number);
         //  return $DBgpv_users->gut_number;  
        if($DBgpv_users==true)
        {
            return ("驗證成功");
        }
        else
        {
            $add=DB::table('gpv_users')
                ->insert([
                    'gu_account'=>     $gut_number,
                    'gu_password'=>    $gut_number,
                    'gut_number'=>      $gut_number,
                    'gut_id'=>          $gut_id,
                ]);
            if($add=true)
            {
                return "新增成功";
            }
            return $a;
            
        }
    }
   
    
}
