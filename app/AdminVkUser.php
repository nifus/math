<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Tag;
use VkApi\Auth;
use VkApi\Api;
class AdminVkUser extends Model
{


    protected
        $fillable = ['id', 'login','updated_at','pass','created_at','vk_id','scope','status','sort','captcha_sid','captcha_url'],
        $table = 'admin_vk_users';

    public
        $timestamps = true;

    public function captchaNeeded($img, $sid){
        $this->update(['status'=>'captcha','captcha_url'=>$img,'captcha_sid'=>$sid]);

        $url = 'http://sms.ru/sms/send?api_id='.\Config::get('services.sms.api').'&from='.\Config::get('services.sms.from').'&to='.\Config::get('services.sms.from').'&text='.urlencode('Юзер '.$this->login.' залочен');
        file_get_contents($url);

    }

    public function activate(){
        $this->update(['status'=>'active','captcha_url'=>null,'captcha_sid'=>null]);
    }


    static function getUser(){
        $user =  self::where('status','active')->orderBy('sort','DESC')->first();
        return $user;
    }

    static function getCaptchaUser(){
        return self::where('status','captcha')->orderBy('sort','ASC')->first();
    }

}
