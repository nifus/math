<?php
namespace App;

use Classes\Vk;
use Illuminate\Database\Eloquent\Model;
use App\Tag;
use VkApi\Auth;
use VkApi\Api;
use Classes\Answer;
class Post extends Model
{


    protected
        $fillable = ['id', 'vk_user', 'updated_at', 'post', 'normalize', 'is_answered', 'type', 'created_at', 'post_id', 'count_results','use_for_test','test_result','attachments'],
        $table = 'posts';

    public
        $timestamps = true;

    public function VkUser(){
        return $this->hasOne('App\VkUser','vk_id','vk_user');
    }

    public function makeAnswer($debug=false)
    {
        $user = $this->getUserInfo($this->vk_user);
        $answer = new Answer($this, $user);
        $answer->debug($debug);
        return $answer->response();
    }

    public function createRemoveAnswer(){
        $user = $this->getUserInfo($this->vk_user);
        $answer = ' @id'.$this->vk_user.' (' . $user['first_name']. ") Вы не состоите в группе. Ваше собщение будет удалено через 30 секунд.";
        $response = Vk::createWallComment($this->post_id, $answer);
        $this->update(['is_answered' => '1']);
        Job::createDeleteWallPostJob($this->id);
        return $response;
    }

    public function createBanAnswer(){
        $user = $this->getUserInfo($this->vk_user);
        $answer = ' @id'.$this->vk_user.' (' . $user['first_name']. ") Ваше сообщение похоже на спам. Через 30 секунд оно будет удалено автоматически, а Вы будете занесены в бан-лист группы. Если наша программа ошиблась, напишите пожалуйста администратору сообщества.";
        $response = Vk::createWallComment($this->post_id, $answer);
        $this->update(['is_answered' => '1']);
        Job::createDeleteWallPostJob($this->id);
        Job::createBanUserPostJob($this->id, $this->vk_user);
        return $response;
    }

    public function createAnswer()
    {
        $response = Vk::createWallComment($this->post_id, $this->makeAnswer());
        $this->update(['is_answered' => '1']);
        return $response;
    }

    public function getUserInfo($id)
    {
        return Vk::getUserInfo($id);

    }

    static function getByVkId($id){
        return self::where('post_id',$id)->first();
    }

    static function getTests(){
        return self::where('use_for_test',1)->get();
    }

}
