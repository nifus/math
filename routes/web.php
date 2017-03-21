<?php

use Illuminate\Http\Request;
Use App\Post;
Use App\VkUser;
Use App\Job;
Use App\ServiceVote;
Use App\Service;
use PHPHtmlParser\Dom;
Route::get('/', function ( ) {
   $members = \Classes\Vk::getGroupMembers();
   foreach($members as $member){
       $name = isset($member['first_name']) ? $member['first_name'] : '';
       $name = isset($member['last_name']) ? ' '.$member['last_name'] : $name;

       VkUser::create([
           'vk_id'=>$member['id'],
           'name'=>$name,
           'avatar'=>$member['photo_100'],
           'is_leave'=>'0',
       ]);
   }
});

Route::get('/test', function ( ) {
    $result =  `maxima -r '(2+2)*10;'` ;
    preg_match('#\(%o1\)(.*)\(%i2\)#iUs',$result, $found);
    var_dump($found[1]);
});




Route::post('/group/callback', function ( Request $request) {

    $response = file_get_contents('php://input');
    $data = json_decode($response);


    if ($data->type=='group_leave'){
        //{"type":"group_leave","object":{"user_id":310426396,"self":1},"group_id":72641161}
        $user = VkUser::getByVkId($data->object->user_id);
        if ( !is_null($user) ){
            $user->update(['is_leave'=>'1']);
        }else{
            VkUser::create(['vk_id'=>$data->object->user_id, 'is_leave'=>'1']);
        }

    }elseif ($data->type=='group_join'){
        //{"type":"group_join","object":{"user_id":310426396,"join_type":"join"},"group_id":72641161}
        $user = VkUser::getByVkId($data->object->user_id);
        if ( !is_null($user) ){
            $user->update(['is_leave'=>'0']);
        }else{
            VkUser::create(['vk_id'=>$data->object->user_id]);
        }
    }elseif($data->type=='wall_post_new'){
        //{"type":"wall_post_new","object":{"id":151,"from_id":310426396,"owner_id":-72641161,"date":1481722658,"post_type":"post","text":"asdasd","can_edit":1,"created_by":310426396,"can_delete":1,"comments":{"count":0}},"group_id":72641161}
        $post = Post::getByVkId($data->object->id);
        if ( is_null($post) ){
            $post = Post::create([
                'vk_user'=>$data->object->from_id,
                'post'=>$data->object->text,
                'post_id'=>$data->object->id,
                //  'normalize'=>$normalize,
                'type'=>'wall',
                'is_answered'=>'0',
                'attachments'=>$data->object->attachments
            ]);
            Job::createAnswerWallPostJob($post->id);
            //dispatch(new CreateAnswer($post));
            exec('php /var/www/bunzya.ru/bots/artisan run-job');
        }
    }elseif($data->type=='confirmation'){
       echo '08d15624';
       exit();
    }

    echo('ok');
    exit();
});



Route::get('/captcha', function (Request $request ) {
    $data = $request->all();
    $user = \App\AdminVkUser::getCaptchaUser();
    if ( is_null($user) ){
        dd('Больше нет залоченных');
    }
    if ( isset($data['captcha']) ) {

        \Classes\Vk::captchaRequest($user, $data['captcha'] );
        $user->activate();
        return redirect('/captcha');
    }else {
        return response( View::make('captcha',['img'=>$user->captcha_url]));
    }
});


Route::post('/api/check', function ( Request $request) {
    $text = $request->get('text');
    return response()->json(['answer'=>Classes\Answer::createByText($text)]);
});

Route::get('/check', function ( Request $request) {
    $text = $request->get('text');

    if ( $text ){
        echo '<pre>';
        echo Classes\Answer::createByText($text);
        echo '</pre>';
    }
    return response('<form><textarea style="height: 200px;width:400px" name="text"></textarea><button>Send</button></form>');
});


Route::post('/api/check/transform', function ( Request $request) {
    $text = $request->get('text');
    return response()->json(['answer'=>\Classes\Answer::transformText($text)]);
});

Route::get('/check/transform', function ( Request $request) {
    $text = $request->get('text');

    if ( $text ){
        echo '<pre>';
        echo Classes\Answer::transformText($text);
        echo '</pre>';
    }
    return response('<form><textarea style="height: 200px;width:400px" name="text"></textarea><button>Send</button></form>');
});