<?php
namespace Classes;

class VkWiki{

    static function createServicePage($service, $images){
        $text='';
        $text.='<gray>'.$service->title.'</gray> ' . "\n\r";

        if ($service->address) {
            $text .= "'''Адрес''' " . $service->address . " \n\r";
        }
        if ($service->phones) {
            $text .= "'''Телефоны''' " . $service->phones . " \n\r";
        }
        if ($service->schedule) {
            $text .= "'''Режим работы''' " . $service->schedule . " \n\r";
        }
        if ($service->url ){
            $text.="'''Сайт''' [".$service->url."]  \n\r";
        }



        $text.=" \n\r ";
        if (!is_null($images) ){
            foreach( $images as $image ){
                $text.=" [[photo-".\Config::get('services.vk.group_id')."_".$image."|nopadding;120px|".$service->title."]]";
            }

        }
        $text.=" \n\r "; $text.=" \n\r ";
        $text.="'''Отзывы''' ".$service->rating."  \n\r";
        $text.='<gray>Голосование открытое. Вы можете оставить свой голос и отзыв. Нажмите на ссылку ЗА или ПРОТИВ, и будете перенаправлены на наш сайт prio-service.ru, где сможете оставить отзыв. Обновление в группе появляются с небольшой задержкой.     </gray> ' . " \n\r";
        $text.="'''Голосовать''' [http://prio-service.ru/vote/".$service->id."/like|ЗА]  или  [http://prio-service.ru/vote/".$service->id."/unlike|ПРОТИВ]" . "\n\r";
        $comments = $service->Comments()->get();

        if (!is_null($comments) ){
            foreach( $comments as $comment ){
              //  var_dump($comment->toArray());
                if ($comment->comment){
                    $text.="<blockquote>[[id".$comment->UserVK->vk_id."|".$comment->UserVK->name."]]: ".$comment->comment."</blockquote> ";

                }
            }
        }

        return $text;
    }

    static function busSummary($buses, $trains){
        $text = '==Автобусы==' . "\n\r";
        $text .= '<gray>Номеров не имеют проходящие автобусы</gray>'."\n\r\n\r";

        $text .= "{|nomargin;\n\r|-\n\r ! Номер \n\r ! Маршрут \n\r ! Туда\n\r ! Обратно \n\r ! Цена \n\r  ";
        foreach( $buses as $bus){
            $d_schedules = $bus->DirectSchedulers()->get();
            $r_schedules = $bus->ReverseSchedulers()->get();
            $d_links = [];
            foreach($d_schedules as $schedule){
                $d_links[]='[[page-'.\Config::get('services.vk.group_id').'_'.$schedule->vk_page.'|'.$schedule->begin.']]';
            }
            $d_links = implode(', ', $d_links);
            $r_links = [];
            foreach($r_schedules as $schedule){
                $r_links[]='[[page-'.\Config::get('services.vk.group_id').'_'.$schedule->vk_page.'|'.$schedule->begin.']]';
            }
            $r_links = implode(', ', $r_links);

            $text .= "|-\n\r| ".$bus->number." \n\r| ".$bus->start_point." - ".$bus->end_point." (<gray>".$bus->regularity."</gray>) \n\r| ".$d_links."  \n\r| ".$r_links." \n\r| ".$bus->price." \n\r  ";
        }
        $text .= "\n\r|}\n\r";

        $text .= '==Электрички==' . "\n\r";
        $text .= '<gray></gray>'."\n\r\n\r";

        $text .= "{|nomargin;\n\r|-\n\r ! Номер \n\r ! Маршрут \n\r ! Туда\n\r ! Обратно \n\r ! Цена \n\r  ";
        foreach( $trains as $bus){
            $d_schedules = $bus->DirectSchedulers()->get();
            $r_schedules = $bus->ReverseSchedulers()->get();
            $d_links = [];
            foreach($d_schedules as $schedule){
                $d_links[]='[[page-'.\Config::get('services.vk.group_id').'_'.$schedule->vk_page.'|'.$schedule->begin.']]';
            }
            $d_links = implode(', ', $d_links);
            $r_links = [];
            foreach($r_schedules as $schedule){
                $r_links[]='[[page-'.\Config::get('services.vk.group_id').'_'.$schedule->vk_page.'|'.$schedule->begin.']]';
            }
            $r_links = implode(', ', $r_links);

            $text .= "|-\n\r| ".$bus->number." \n\r| ".$bus->start_point." - ".$bus->end_point." (<gray>".$bus->regularity."</gray>) \n\r| ".$d_links."  \n\r| ".$r_links." \n\r| ".$bus->price." \n\r  ";
        }
        $text .= "\n\r|}\n\r";

        return $text;


    }

    static function createCategoryPage($category, $services){

        $text = '==' . $category->title . '==' . "\n\r";

        $text .= '[[page-72641161_51635988|Назад]]' . "\n\r\n\r";

        $text .= '<gray>Вы можете оставить свое мнение о любой из перечисленных ниже услуг. Голосование открытое. Любой желающий сможет увидеть, кто голосовал и как. </gray>'."\n\r\n\r";

        $text .= "{|nomargin;\n\r|-\n\r ! Описание \n\r ! Телефоны  \n\r ! Рейтинг \n\r";

        foreach ($services as $service) {
            $title = $service->vk_page  ? '[[page-'.\Config::get('services.vk.group_id').'_'.$service->vk_page.'|'.$service->title.']]' : $service->title;
            $title.='<br><gray>'.$service->address.'</gray>';
            $rate = is_null($service->rating)  ? '-' : $service->rating.' из 5';

            $text .= "|-\n\r| " . $title . " \n\r| " . $service->phones  . " \n\r| ".$rate." \n\r";
        }


        $text .= "\n\r|}";
        return $text;
    }

    static function createSummaryPage($title, $categories){
        $text = '=='.$title.'==' . "\n\r";
        $text .= '<gray>Все данные разбиты на категории</gray>' . "<br/><br/><br/>";

        foreach ($categories as $c) {
            $text .= '[[page-72641161_' . $c->vk_page . '|' . $c->title . ' (' . $c->getCountServices() . ')]]'."<br>";
            if ($c->desc){
                $text .= '<gray>' . $c->desc . '</gray><br>';
            }
            $text.='<br>';
        }
        return $text;
    }

    static function createStaticPage($count_posts,$count_services,$count_active_users, $count_removed_users, $count_light_banned_users, $count_full_banned_users,$count_votes, $count_comments  ){
        return  "'''Количество сообщений на стене''' - " . $count_posts . " \n\r" .
            "'''Количество услуг доступно''' - " . $count_services . " \n\r" .
            "::'''Голосов за услуги''' - ".$count_votes." \n\r" .
            "::'''Отзывов об услугах''' - ".$count_comments." \n\r" .
            "'''Пользователей в группе''' - " . $count_active_users . " \n\r" .
            "::'''Пользователей убежало''' - " . $count_removed_users . " \n\r" .
            "::'''Пользователей забанено на время''' - " . $count_light_banned_users . " \n\r" .
            "::'''Пользователей забанено навсегда''' - " . $count_full_banned_users . " \n\r" . " \n\r" .
            "'''Денег заработано''' - $ " . rand(0, 99999999) . " \n\r\n\r\n\r" .
            "<i>Ни одно животное не пострадало</i>";

    }
}