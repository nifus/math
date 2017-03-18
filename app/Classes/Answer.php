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

    private function createMsg($text)
    {

        //$text = str_replace(' ', '', $text);
        //$text = str_replace('х','x',$text);
        //$text = str_replace('а','a',$text);
        $text = str_replace('×', '*', $text);
        $text = str_replace(':', '/', $text);
        $text = $this->removeText($text);
        var_dump('На входе текст:' . $text);
        if (preg_match('#[а-я]#i', $text)) {
            return $this->createCyrillicMsg();
        }
        if (preg_match('#[√]#i', $text, $found)) {
           // dd($found);
            return $this->createStupidMsg($found[0]);
        }
        $vars = [];
        if (preg_match('#\[(.*)\]#is', $text, $variables)) {
            $vars = $this->parseVariables($variables[1]);
            $text = str_replace('['.$variables[1].']','',$text);
        }

        preg_match_all('#([-0-9 .,+/*=^a-z:)(]{2,})#is', $text, $founds);




        $expressions = [];
        if (sizeof($founds[0])==0) {
            return $this->createEmptyMsg();
        }elseif (sizeof($founds[0]) > 1) {

            foreach ($founds[0] as $found) {
                $exp = $this->normalize($found);
                if ( false!==$exp ){
                    array_push($expressions, $exp);
                }
            }
            var_dump('На входе выражения:' );
            var_dump($expressions);
            if ( sizeof($expressions) ==0 ){
                return $this->createEmptyMsg();
            }

        }elseif(sizeof($founds[0]) ==1 ){
            $expression = $this->normalize($founds[0][0]);
            if ( false===$expression ){
                return $this->createEmptyMsg();
            }
            array_push($expressions, $expression);

            var_dump('На входе выражение:' . $expressions[0]);
        }


        $end = false;
        //var_dump($value);
        try {
            if (sizeof($expressions)==1){
                if ($this->detectSimpleExpression($expressions[0])) {
                    var_dump('detect simple expr');
                    $end = $this->createSimpleMath($expressions[0]);
                } else {
                    var_dump('detect eq expr');
                    $end = $this->createEqMath($expressions[0], $vars);
                }
            }else{
                $end = $this->createEqSystemMath($expressions);
            }

        } catch (\Exception $e) {
            //var_dump($e->getMessage());
            if ($e->getCode() == 1) {
                return $this->createManyParametersMsg();
            }
        }


        //preg_match('#[a-z]{1,2}#is',$value, $type);

        //if (!$type){
        if ($this->debug == true) {
            //  var_dump($end);
        }


        if (false === $end) {
            return $this->createFailMsg();
        }
        //var_dump($result);
        return $end;

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
        return 'Недостаточно данных для вычисления. Возможно Вам поможет хелп, как заставить бота решать ваши задачи - https://vk.com/page-36661139_53304819';
    }

    private function createEmptyMsg()
    {
        return 'Вычислений не найдено. Возможно Вам поможет хелп, как заставить бота решать ваши задачи - https://vk.com/pages?oid=-36661139&p=%D0%9D%D0%B0%D0%B2%D0%B8%D0%B3%D0%B0%D1%86%D0%B8%D0%B8%20%D0%BF%D0%BE%20%D0%BA%D0%BE%D0%BC%D0%B0%D0%BD%D0%B4%D0%B0%D0%BC';
    }

    private function createFailMsg()
    {
        return 'Вычисления неслучились.... админ об этом узнает и пнет Валеру, если он не прав. ';
    }

    private function createCyrillicMsg()
    {
        return 'В запросе найдена кириллица. Возможно Вам поможет хелп, как заставить бота решать ваши задачи - https://vk.com/pages?oid=-36661139&p=%D0%9D%D0%B0%D0%B2%D0%B8%D0%B3%D0%B0%D1%86%D0%B8%D0%B8%20%D0%BF%D0%BE%20%D0%BA%D0%BE%D0%BC%D0%B0%D0%BD%D0%B4%D0%B0%D0%BC';
    }
    private function createStupidMsg($chars)
    {

        return 'Ух, Я впечатлен! Вы не поленились найти UTF символы вроде этого - '.$chars.'. Но увы, они бесполезны для запроса. Как составить запрос правильно поможет хелп - https://vk.com/pages?oid=-36661139&p=%D0%9D%D0%B0%D0%B2%D0%B8%D0%B3%D0%B0%D1%86%D0%B8%D0%B8%20%D0%BF%D0%BE%20%D0%BA%D0%BE%D0%BC%D0%B0%D0%BD%D0%B4%D0%B0%D0%BC';
    }

    private function normalize($expression)
    {
        //preg_match('#[-+/*=^]|sqrt|abs#is', $expression, $found1);
        //$value = trim($expression);

        $expression = preg_replace('#=$#', '', $expression);
        $expression = str_replace(',', '.', $expression);

        // $value = str_replace('•','×',$value);
        $expression = preg_replace('#([0-9.]{1,})([a-z]{1,2})#i', '\1*\2', $expression);
        $expression = preg_replace('#([0-9.a-z]{1,})\(#i', '\1*(', $expression);
        $expression = preg_replace('#\)([0-9.a-z]{1,})#i', ')*\1', $expression);


        if ( preg_match_all('#([a-z]{2,})#i',$expression, $found) ){
            foreach($found[0] as $f ){
                if ($f=='sqrt' || $f=='abs' ){
                    continue;
                }
                $replace = [];
                for($i=0;$i<strlen($f);$i++){
                    array_push($replace, $f[$i]);
                }
                $expression = str_replace($f, implode('*',$replace), $expression);
            }
        }

        $expression = str_replace('sqrt*(', 'sqrt(', $expression);
        $expression = str_replace('abs*(', 'abs(', $expression);
        return $expression;
    }

    /**
     * Проверяем, относится ли выраженеи к простейшим
     */
    private function detectSimpleExpression($expression)
    {
        $expression = str_replace('sqrt', '', $expression);
        $expression = str_replace('abs', '', $expression);
        //var_dump($expression);
        if (preg_match('#=.*#is', $expression)) {
            return false;
        };
        if (preg_match('#[a-z]{1,2}#is', $expression)) {
            return false;
        };
        return true;
    }

    private function parseVariables($variables)
    {
        $result = [];
        $variables = preg_replace('#\s#', '', $variables);

        preg_match_all('#([a-z]{1,3})=([^;\]]*)#i', $variables, $found);

        $i = 0;
        foreach ($found[1] as $var) {
            $result[$var] = $found[2][$i];
            $i++;
        }
        if (sizeof($result) == 0) {
            return null;
        }
        return $result;
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
        passthru('maxima -r \'printf(true,"~f",' . $value . ')$\'');
        $result = ob_get_contents();
        ob_end_clean();

        return $this->parseAnswer($result);
    }

    private function parseAnswer($value)
    {
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
            return preg_replace('#.0$#', '', $found);
        } elseif (preg_match('#\(%i1\)(.*)\(%i2\)#iUs', $value, $found)) {
            $found = preg_replace('#\s+#', '', $found[1]);
            // var_dump($found);
            return preg_replace('#.0$#', '', $found);
        }

        return false;

    }

    private function removeText($text)
    {
        $text = preg_replace('# +#', '', $text);
        $text = preg_replace('#[а-я.,:!]{2,}#i', '', $text);
        $text = preg_replace('#^[a-zа-я]([0-9])#i', '\1', $text);
        return $text;
    }


    static function createByText($text)
    {
        $answer = new self();
        $answer->debug(true);
        // $normalize = self::normalize($text);
        return $answer->createMsg($text);
    }


}