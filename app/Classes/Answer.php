<?php
namespace Classes;

use App\Bus;
use League\Flysystem\Exception;

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
            $this->response .= $this->createMsg($post->post, $post->attachments);
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

    private function createMsg($text, $attachments)
    {
        if (!empty($attachments)){
            return $this->createAttachmentMsg();
        }
        $parser =  new \MathParser($text, $this->debug);
        if ( $parser->detect_long_numbers == true ){
            return $this->createLongMsg();
        }
        if ( $parser->detect_cyrillic == true ){
            return $this->createCyrillicMsg();
        }
        if ( $parser->detect_utf == true ){
            return $this->createStupidMsg();
        }

        if ( $parser->detect_task == true ){
            return $this->createTaskMsg();
        }

        try {
            if ($parser->count_expressions==0) {
                return $this->createEmptyMsg();

            }elseif ($parser->count_expressions==1) {
                $type = \DetectTypeExpression::detectType($parser->expressions[0]);
                var_dump($type);
                if ( false===$type ){
                    return $this->createEmptyMsg();
                }elseif( $type=='elementary' ){
                    // $math = new \Math($parser->expressions[0]);
                    //$math->solve();

                    return  $this->createSimpleMath($parser->expressions[0]);
                }else{
                    return $this->createEqMath($parser->expressions[0], $parser->variables);
                }
            }elseif($parser->count_expressions>1){
                //foreach( $parser->expressions as $expression ){
                 //   $type = \DetectTypeExpression::detectType($expression);
                    //if ( )
                //}
                return $this->createEqSystemMath($parser->expressions);
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            if ($e->getCode() == 1) {
                return $this->createManyParametersMsg();
            }
        }

        return $this->createFailMsg();


    }

    private function createAttachmentMsg(){
        return 'Ммм это похоже на картинку. К сожалению пока я не могу распознавать текст на картинках. Вам стоит обратиться к живым людям. ';
    }

    private function createTaskMsg(){
        return 'Ух, это похоже на одну из тех задач, которые тут решают живые люди за денежку. Больше информации в хелпе https://vk.cc/6oV2be';
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

    private function createManyParametersMsg()
    {
        return 'Недостаточно данных для вычисления. Возможно Вам поможет хелп, как заставить бота решать ваши задачи - https://vk.cc/6oV2be';
    }

    private function createEmptyMsg()
    {
        return 'Вычислений не найдено. Возможно Вам поможет хелп, как заставить бота решать ваши задачи - https://vk.cc/6oV2be';
    }

    private function createFailMsg()
    {
        return 'Вычисления неслучились.... админ об этом узнает и пнет Валеру, если он не прав. ';
    }

    private function createCyrillicMsg()
    {
        return 'В запросе найдена кириллица. Возможно Вам поможет хелп, как заставить бота решать ваши задачи - https://vk.cc/6oV2be';
    }

    private function createLongMsg()
    {
        return 'Начальник сказал, что Ваш запрос слишком глуп, чтобы его выполнять';
    }
    private function createStupidMsg()
    {

        return 'Ух, Я впечатлен, это красиво, но мимо! Составить запрос правильно поможет хелп - https://vk.cc/6oV2be';
    }

    private function createIncorrectMsg()
    {
        $msgs = [
            'Это не похоже на корректный запрос',
            'Ух, я в замешательстве, как это можно посчитать',
            'Это точно математика?',
        ];

        return $msgs[rand(0,2)].'. Составить запрос правильно поможет хелп - https://vk.cc/6oV2be';
    }


    private function createEqSystemMath($expressions){
        $run = implode(',',$expressions);


        echo 'maxima -r \'solve(['.$run.']);\'';
        ob_start();
        passthru('maxima -r \'solve(['.$run.']);\'');
        $result = ob_get_contents();
        ob_end_clean();
        return $this->parseAnswer($result);

    }

    private function createEqMath($value, $variables)
    {
        var_dump('createEqMath');
        $request = '';
        if (sizeof($variables) > 0) {
            foreach ($variables as $key => $v) {
                $request .= $key . ':' . $v . '$';
            }
        }

        var_dump($variables);
        echo 'maxima -r \'' . $request . 'f:' . $value . '$solve(f);\'';
        ob_start();
        passthru('maxima -r \'' . $request . 'f:' . $value . '$solve(f);\'');
        $result = ob_get_contents();
        ob_end_clean();

        if (preg_match('#variable list is empty, continuing anyway#i', $result)) {

            ob_start();
            passthru('maxima -r \'' . $request . '' . $value . ';\'');
            $result = ob_get_contents();
            ob_end_clean();
            var_dump($result);
        }
        if (preg_match('#\-{1,}#', $result)) {
            ob_start();
            passthru('maxima -r \'f:' . $value . '$float(solve(f));\'');
            $result = ob_get_contents();
        }
        // var_dump($result);

        if (preg_match('#Unknowns given#', $result)) {
            throw new \Exception("", 1);
        }
        return $this->parseAnswer($result);

        //preg_match('#\(%o2\)\s+\[(.*)\]\s*(.*)\(%i3\)#iUs', $result, $end);


    }

    private function createSimpleMath($value)
    {
        ob_start();
        passthru('maxima -r \'printf(false,"~a",' . $value . ');\'');
        $result = ob_get_contents();
        ob_end_clean();
       // dd($result);
        return $this->parseAnswer($result);
    }

    private function parseAnswer($value)
    {
        if ( preg_match('#incorrect syntax#',$value) ){
            return $this->createIncorrectMsg();
        }
        if (preg_match('#\(%o1\)\s+\[(\[.*\])\]\s*\(%i2\)#iUs', $value, $found)) {
            return $found[1];
        }elseif (preg_match('#\(%o2\)\s+\[(.*)\]\s*(.*)\(%i3\)#iUs', $value, $found)) {
            $variable = explode('=', $found[1]);
            if ($variable[1] == ' --') {
                return trim($variable[0]) . '=' . trim($found[2]);
            }
            $res = trim($found[1]) . ' ' . trim($found[2]);
            return $res;
        } elseif (preg_match('#\(%o2\)\s+(.*)\s*\(%i3\)#iUs', $value, $found)) {
            $found = preg_replace('#\s+#', '', $found[1]);
            // var_dump($found);
            return preg_replace('#\.0$#', '', $found);
        } elseif (preg_match('#\(%o1\)(.*)\(%i2\)#iUs', $value, $found)) {
            $found = preg_replace('#\s+#', '', $found[1]);
            return preg_replace('#\.0$#', '', $found);
        }
        return false;

    }



    static function createByText($text)
    {
        $answer = new self();
        $answer->debug(true);
        // $normalize = self::normalize($text);
        return $answer->createMsg($text, null);
    }


}