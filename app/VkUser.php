<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Tag;
use VkApi\Auth;
use VkApi\Api;
class VkUser extends Model
{


    protected
        $fillable = ['id', 'vk_id','updated_at','is_leave','created_at','name','avatar'],
        $table = 'vk_users';

    public
        $timestamps = true;


    static function getByVkId($id){
        return self::where('vk_id',$id)->first();
    }
    static function getCountActiveUsers(){
        return self::where('is_leave','0')->count();
    }

    static function getCountRemovedUsers(){
        return self::where('is_leave','1')->count();
    }

    static function getCountLightBannedUsers(){
        return 0;
    }

    static function getCountFullBannedUsers(){
        return 0;
    }
}
