<?php
namespace Classes;

use App\Bus;

class Answer
{

    private $response;
    private $post;
    private $user;
    private $debug = false;

    static private $respects = [
        'global' => [
            'Привет', 'Доброго времени суток', 'Здравствуте', 'Здрасте', 'Aloha', 'Физкульт привет'
        ],
        'time' => [
            'morning' => ['Доброе утро'],
            'day' => ['Добрый день'],
            'evening' => ['Добрый вечер', 'Вечерочек добрый'],
            'night' => ['Доброй ночи'],
        ],
        'sex' => [
            'woman' => ['Здравствуйте уважаемая', 'Здравствуйте губокоуважаемая', 'Здравствуйте сударыня'],
            'man' => ['Здравствуйте уважаемый', 'Здравствуйте губокоуважаемый'],
        ]
    ];

    function __construct($post = null, $user = null)
    {
        $this->post = $post;
        $this->user = $user;
        $this->response = $this->createRespect();
        if (!is_null($post)) {
            $this->response .= $this->createMsg($post->post);



        }
    }

    function debug($debug = true)
    {
        $this->debug = $debug;
    }

    function response()
    {
        return $this->response;
    }


    private function createRespect()
    {
        if (is_null($this->user)) {
            return '';
        }
        $type = rand(0, 2);
        if ($type == 0) {
            $words = self::$respects['global'];
            $respect = $words[rand(0, count($words) - 1)];
        } elseif ($type == 1) {
            $words = self::$respects['time'];
            $hour = date('H', time());
            if ($hour >= 5 && $hour <= 11) {
                $respect = $words['morning'][rand(0, count($words['morning']) - 1)];
            } elseif ($hour > 11 && $hour <= 18) {
                $respect = $words['day'][rand(0, count($words['day']) - 1)];
            } elseif ($hour > 18 && $hour <= 23) {
                $respect = $words['evening'][rand(0, count($words['evening']) - 1)];
            } elseif ($hour > 23 && $hour <= 4) {
                $respect = $words['night'][rand(0, count($words['night']) - 1)];
            }
        } elseif ($type == 2) {
            $words = self::$respects['global'];
            $respect = $words[rand(0, count($words) - 1)];
        }

        return $respect . ' @id' . $this->post->vk_user . ' (' . $this->user['first_name'] . ")\n\r\n\r";
    }


    private function createEmptyMsg(){
        return 'Вычислений не найдено. Возможно Вам поможет хелп, как заставить бота решать ваши задачи - https://vk.com/page-36661139_53304819';
    }
    private function createFailMsg(){
        return 'Вычисления неслучились.... админ об этом узнает и пнет Валеру, если он не прав. ';
    }

    private function createMsg($text)
    {

        preg_match('#([0-9 .,+-/*=^a-z)(]{2,})#is',$text, $found);


        if (!$found){
            return $this->createEmptyMsg();
        }else{
            preg_match('#[+-/*=^]|sqrt#is',$found[1], $found1);
            if (!$found1){
                return  $this->createEmptyMsg();
            }
        }
        $value = trim($found[1]);
        $value = preg_replace('#=$#','',$value);
        $value = str_replace(',','.',$value);

        //preg_match('#[a-z]{1,2}#is',$value, $type);
        $end = $this->createSimpleMath($value);
        //if (!$type){


        if (false===$end){
            $end = $this->createEqMath($value);
        }



        if (false===$end){
            return $this->createFailMsg();
        }
        //var_dump($result);
        return $end;

    }

    private function createEqMath($value){

        ob_start();
        //passthru('maxima -r \'' . $value . ';\'');
         passthru('maxima -r \'f:' . $value . '$solve(f);\'');
        $result = ob_get_contents();
        ob_end_clean(); //Use this instead of ob_flush()

        //var_dump($result);
        //$result =  `maxima -r '"$text"'` ;
        preg_match('#\(%o2\)\s+\[(.*)\]\s*(.*)\(%i3\)#iUs', $result, $end);

        if (!isset($end[1]) && !isset($end[2]) ){
            return false;
        }
        $variable = explode('=',$end[1]);
        if ($variable[1]==' --'){
            return trim($variable[0]).'='.trim($end[2]);
        }
        return trim($end[1]).' '.trim($end[2]);
    }

    private function createSimpleMath($value){

        ob_start();
        passthru('maxima -r \'' . $value . ';\'');
        // passthru('maxima -r \'f:' . $value . '$ solve(f);\'');
        $result = ob_get_contents();
        ob_end_clean(); //Use this instead of ob_flush()

        //$result =  `maxima -r '"$text"'` ;
        preg_match('#\(%o1\)(.*)\(%i2\)#iUs', $result, $end);
        if (!$end){
            return false;
        }
        return trim($end[1]);
    }


    static function createByText($text)
    {
        $answer = new self();
        $answer->debug(true);
        // $normalize = self::normalize($text);
        return $answer->createMsg($text);
    }
}