<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Category;
use App\Service;
use Classes\Vk;
use Classes\VkWiki;
use Classes\Answer;

class Job extends Model
{


    protected
        $fillable = ['id', 'post_id', 'updated_at', 'type', 'created_at', 'complete', 'priority', 'data', 'response'],
        $table = 'jobs';

    public
        $timestamps = true;


    public function Post()
    {
        return $this->hasOne('App\Post', 'id', 'post_id');
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    public function getDataAttribute()
    {
        return json_decode($this->attributes['data']);
    }

    public function deleteWall()
    {
        $post = $this->Post;
        return Vk::deleteWall($post->post_id);
    }

    function banSchedule(){
        $post = $this->Post;
        $user = $post->VkUser;
        Vk::addUserToBan($user->vk_id);
        return true;
    }

    function createAnswer()
    {
        $post = $this->Post;
        $user = $post->VkUser;
        //$tags = Answer::normalize($post->post);

        preg_match('#https?://#iUs',$post->post,$result );

        //$post->update(['normalize' => implode('', $tags)]);
        if ( sizeof($result)>0 ) {
            $post->createBanAnswer();
        }elseif (is_null($user) || $user->is_leave == '1' ) {
            $post->createRemoveAnswer();
        } else {
            $post->createAnswer();
        }
        return true;
    }

    function updateStat()
    {
        $count_posts = Post::count();
        $count_services = Service::count();
        $count_active_users = VkUser::getCountActiveUsers();
        $count_removed_users = VkUser::getCountRemovedUsers();
        $count_light_banned_users = VkUser::getCountLightBannedUsers();
        $count_full_banned_users = VkUser::getCountFullBannedUsers();
        $count_votes = ServiceVote::getCountVotes();
        $count_comments = ServiceVote::getCountVotesWithComments();

        $content = VkWiki::createStaticPage($count_posts, $count_services, $count_active_users, $count_removed_users, $count_light_banned_users, $count_full_banned_users, $count_votes, $count_comments);

        return Vk::page('Статистика группы', $content);
    }

    public function updateSummaryService()
    {
        $goods = Category::getGoods();
        $services = Category::getServices();
        $goods_content = VkWiki::createSummaryPage('Товары',$goods);
        $services_content = VkWiki::createSummaryPage('Услуги',$services);
        Vk::page('Товары', $goods_content,51707572 );
        Vk::page('Услуги', $services_content, 51707573 );
        return true;
    }

    public function updateSummaryBus()
    {
        $buses = Bus::getAllBuses();
        $trains = Bus::getAllTrains();
        $content = VkWiki::busSummary($buses, $trains);
        return Vk::busSummary($content);
    }

    public function updatePageService()
    {
        $service_id = $this->data->service_id;
        $service = Service::find($service_id);
        $photos = [];
        if ($service->vk_album){
            $photos = Vk::getAlbumDetails($service->vk_album);
        }

        $text = VkWiki::createServicePage($service ,$photos);
        if (!is_null($service->vk_page)){
            $count = Service::where('vk_page',$service->vk_page)->count();
            if ($count>1){
                $service->vk_page = null;
            }
        }


        $page_id = Vk::page($service->title, $text,$service->vk_page);
        $service->update(['vk_page' => $page_id,'last_update'=>date ("Y-m-d H:i:s", time())]);
        return $page_id;
    }

    public function updateCategoryServices()
    {
        $category_id = $this->data->category_id;
        $category = Category::find($category_id);

        $services = Service::getByCategory($category->id);
        $text = VkWiki::createCategoryPage($category, $services);

        $page_id = Vk::page($category->title, $text);
        $category->update(['vk_page' => $page_id,'last_update'=>date ("Y-m-d H:i:s", time())]);
        return $page_id;
    }

    public function updateBusSchedule()
    {
        $bus_id = $this->data->bus_id;
        $bus = Bus::find($bus_id);

       // $services = Service::getByCategory($category->id);
        //$text = VkWiki::createCategoryPage($category, $services);

        //$page_id = Vk::page($category->title, $text);
        //$category->update(['vk_page' => $page_id,'last_update'=>date ("Y-m-d H:i:s", time())]);
       // return $page_id;
    }


    static function createSummaryServiceJob()
    {
        return Job::create(['type' => 'summary_service', 'priority' => 'low']);
    }

    static function createCategoryServicesJob($category_id)
    {
        return Job::create(['type' => 'category_services', 'data' => ['category_id' => $category_id], 'priority' => 'low']);
    }

    static function createBusScheduleJob($bus_id){
        return Job::create(['type' => 'bus_schedule', 'data' => ['bus_id' => $bus_id], 'priority' => 'low']);
    }

    static function createStatUpdateJob()
    {
        return Job::create(['type' => 'stat', 'priority' => 'low']);
    }

    static function createDeleteWallPostJob($post_id)
    {
        return Job::create(['type' => 'delete', 'post_id' => $post_id, 'priority' => 'low']);
    }

    static function createBanUserPostJob($post_id, $vk_user)
    {
        return Job::create(['type' => 'ban', 'post_id' => $post_id, 'priority' => 'low','data'=>['user'=>$vk_user]]);
    }

    static function createAnswerWallPostJob($post_id)
    {
        $post = Post::find($post_id);
        $user = $post->VkUser;
        $priority = 'high';
        if (is_null($user) || $user->is_leave == '1') {
            $priority = 'low';
        }
        return Job::create(['type' => 'answer', 'post_id' => $post_id, 'priority' => $priority]);
    }

    static function createBusSummaryJob(){
        return Job::create(['type' => 'summary_bus', 'priority' => 'low']);
    }

    static function createPageServiceJob($service_id)
    {
        return Job::create(['type' => 'page_update', 'priority' => 'low', 'data'=>['service_id'=>$service_id]]);
    }



    static function runJob()
    {
        $job = self::where('priority', 'high')->where('complete', '0')->orderBy('created_at', 'ASC')->first();
        if (is_null($job)) {
            $job = self::where('priority', 'low')->where('complete', '0')->orderBy('created_at', 'ASC')->first();
        }
        if (is_null($job)) {
            return false;
        }
        $job->update(['complete' => '2']);

        if ($job->type == 'delete') {
            $response = $job->deleteWall();
        } elseif ($job->type == 'answer') {
            $response = $job->createAnswer();
        } elseif ($job->type == 'ban') {
            $response = $job->banSchedule();
        }

        $job->update(['complete' => '1', 'response' => $response]);

        return true;

    }
}
