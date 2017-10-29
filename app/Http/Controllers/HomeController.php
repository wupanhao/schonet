<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Mail;
use App\User;
use DB;
use Crypt;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    if(DB::table('radcheck')->where('username',Auth::user()->name)->first()->op != ':=')
    {
        $url = config('app.url').'/home/sendactivation/';
        return view('home',['activate_url' => $url]);
    }
        return view('home');
    }

    public function activation()
    {
    $user = Auth::user();
    $link = config('app.url').'/home/activate/'.$user->id.'/'.Crypt::encryptString(Auth::user()->name);
        $name = $user->name;
        $flag = Mail::send('emails.activation',['link'=>$link],function($message) use($user){
            $to = $user->email;
            $message ->to($to)->subject('Activate Your Account');
        });
            echo '已向'.$user->email.'发送激活链接';
    }

    public function activate($uid,$token)
    {
    $user = User::where('id',$uid)->first();
    if($user && Crypt::decryptString($token)==$user->name){
        if(DB::table('radcheck')->where('username',$user->name)->first()->op == ':=')
            echo '用户名'.$user->name.'已激活';
        else{
        $radcheck = DB::table('radcheck')->where('username',$user->name)->update(['op' => ':=']);
        echo '激活成功，'.$user->name.'已可使用';
        }
    }
    else
        echo '??¨®D¡ä?¨®??¡ì';  
    }
}
