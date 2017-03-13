<?php
namespace Classes;

use App\AdminVkUser;
use VkApi\Auth;
use VkApi\Api;

class Vk
{

    static function getGroupMembers($offset=0, $prev_members=[]){

        $user = AdminVkUser::getUser();
        $scope = array('groups');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        $params = [
            'group_id' => \Config::get('services.vk.group_id'),
            'offset'=>$offset,
            'fields'=>'photo_100,contacts'
        ];
        $members = $api->call('groups.getMembers', $params);
        $res = array_merge($members['items'],$prev_members);
        if ( sizeof($members['items'])==1000 ){
            return self::getGroupMembers($offset+1000,$res);
        }
        return $res;

    }

    static function getAlbumDetails($album){
        $user = AdminVkUser::getUser();
        $scope = array('groups');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        $params = [
            'owner_id' => '-'.\Config::get('services.vk.group_id'),
            'album_id'=>$album
        ];
        $response = $api->call('photos.get', $params);
        if (!isset($response['count'])){
            return null;
        }
        $images = [];
        foreach($response['items'] as $item){
            array_push($images,$item['id']);
        }
        return $images;

       /* foreach ($photos->respose->items as $item ){
            $text.='[[photo-72641161_'.$item->id.'|200x187px;left| ]] [[photo-72641161_456239029|200x212px;left| ]] ';

        }

        return*/
    }

    static function getAlbums(){
        $user = AdminVkUser::getUser();
        $scope = array('groups');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        $params = [
            'owner_id' => '-'.\Config::get('services.vk.group_id'),
            'need_system'=>0
        ];
        return $api->call('photos.getAlbums', $params);
    }

    static function getUserInfo($user_id){
        $user = AdminVkUser::getUser();
        $scope = array('users');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        $params = [
            'user_ids' => $user_id
        ];
        return $api->call('users.get', $params)[0];
    }

    static function busSummary($content){
        $user = AdminVkUser::getUser();

        $scope = array('pages');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        try
        {
            $params = [
                'text' => $content,
                'title' => 'Расписание авто и жд транспорта',
                'user_id' => $user->vk_id,
                'group_id' => \Config::get('services.vk.group_id'),
            ];
            $api->call('pages.save', $params);
            return true;
        }
        catch (VkApi\Error\AuthFailed $e) {
            print "Error: ".$e->getMessage()."\r\n";
        }
        catch ( VkApi\Error\Exception $e) {
            print "Api Error: ".$e->getMessage()."\r\n";
        } catch (\VkApi\Error\CaptchaNeed $e) {
            $user->captchaNeeded($e->getCaptchaImage(), $e->getCaptchaSid());
        }
    }

    static function captchaRequest($user, $captcha)
    {
        $scope = array('pages');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        $params = [
            'text' => 'Test page',
            'title' => 'Test page',
            //'page_id'=>51635988,
            'user_id' => $user->vk_id,
            'group_id' => \Config::get('services.vk.group_id'),
            'captcha_sid' => $user->captcha_sid,
            'captcha_key' => $captcha
        ];

        return $api->call('pages.save', $params);

    }

    static function addUserToBan($user_id)
    {
        $user = AdminVkUser::getUser();

        $scope = array('groups');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        try {

            $params = [
                'group_id' => \Config::get('services.vk.group_id'),
                'user_id' => $user_id,
                'end_date' => time()+(60*60)*24*14,
                'reason' => 1,
                'comment' => 'Вы заблокированы автоматически за спам. Обратитесь пожалуйста к администратору группы если вы считаете, это ошибкой нашей программы',
                'comment_visible'=>1
            ];
            return $api->call('groups.banUser', $params);
        } catch (VkApi\Error\AuthFailed $e) {
            echo $e->getCode();
            print "Error: " . $e->getMessage() . "\r\n";
        } catch (VkApi\Error\Exception $e) {
            echo $e->getCode();
            print "Api Error: " . $e->getMessage() . "\r\n";
        } catch (\VkApi\Error\CaptchaNeed $e) {
            $user->captchaNeeded($e->getCaptchaImage(), $e->getCaptchaSid());
        }
    }

    static function createWallComment($wall_id, $text)
    {
        $user = AdminVkUser::getUser();

        $scope = array('wall');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        try {

            $params = [
                'owner_id' => '-' . \Config::get('services.vk.group_id'),
                'post_id' => $wall_id,
                'from_group' => 1,
                'message' => $text,
                'guid' => time(),
            ];
            return $api->call('wall.createComment', $params);
        } catch (VkApi\Error\AuthFailed $e) {
            echo $e->getCode();
            print "Error: " . $e->getMessage() . "\r\n";
        } catch (VkApi\Error\Exception $e) {
            echo $e->getCode();
            print "Api Error: " . $e->getMessage() . "\r\n";
        } catch (\VkApi\Error\CaptchaNeed $e) {
            $user->captchaNeeded($e->getCaptchaImage(), $e->getCaptchaSid());
        }
    }

    static function deleteWall($post_id)
    {
        $user = AdminVkUser::getUser();

        $scope = array('wall');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        try {
            $params = [
                'owner_id' => '-' . \Config::get('services.vk.group_id'),
                'post_id' => $post_id,
            ];
            return $api->call('wall.delete', $params);
        } catch (VkApi\Error\AuthFailed $e) {
            echo $e->getCode();
            print "Error: " . $e->getMessage() . "\r\n";
        } catch (VkApi\Error\Exception $e) {
            echo $e->getCode();
            print "Api Error: " . $e->getMessage() . "\r\n";
        } catch (\VkApi\Error\CaptchaNeed $e) {
            $user->captchaNeeded($e->getCaptchaImage(), $e->getCaptchaSid());
        }
    }

    static function page($title, $content, $page_id = null)
    {
        $user = AdminVkUser::getUser();
        $scope = array('pages');
        $token_path = storage_path('logs');
        $auth = new Auth($user->login, $user->pass, $token_path);
        $api = new Api(\Config::get('services.vk.app_id'), $scope, $auth);
        // dd($text);
        try {


            if (!is_null($page_id)) {
                $params = [
                    'text' => $content,
                    'title' => $title,
                    'page_id'=>$page_id,
                    'user_id' => $user->vk_id,
                    'group_id' => \Config::get('services.vk.group_id'),
                ];
                $api->call('pages.save', $params);
            }else{
                $params = [
                    'text' => $content,
                    'title' => $title,
                    'user_id' => $user->vk_id,
                    'group_id' => \Config::get('services.vk.group_id'),
                ];
                $page_id = $api->call('pages.save', $params);

            }
            return $page_id;

        } catch (VkApi\Error\AuthFailed $e) {
            print "Error: " . $e->getMessage() . "\r\n";
        } catch (VkApi\Error\Exception $e) {
            print "Api Error: " . $e->getMessage() . "\r\n";
        } catch (\VkApi\Error\CaptchaNeed $e) {
            $user->captchaNeeded($e->getCaptchaImage(), $e->getCaptchaSid());
        }
    }
}