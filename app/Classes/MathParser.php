<?php

class MathParser
{

    public $detect_cyrillic;
    public $detect_utf;
    public $detect_task;
    public $count_expressions;
    public $expressions = [];
    public $variables = [];

    private $debug;
    private $request;
    private $normalize_request;



    function __construct($value, $debug = false)
    {
        $this->debug = $debug;
        $this->request = $value;
        $this->message($value , ' на входе выражение ');

        $this->detect_task = self::detectTask($this->request);
        $this->normalize_request = self::clearRequest($value);
        $this->detect_cyrillic = self::detectCyrillic($this->normalize_request);
        $this->detect_utf = self::detectUtf($this->normalize_request);

        $this->message($this->normalize_request ,' после очистки запроса ');
        $this->message($this->detect_cyrillic ,' кирилица ');
        $this->message($this->detect_utf , 'utf ');


        $this->normalize_request = $this->parseAndRemoveVariables($this->normalize_request);
        $this->message($this->variables , 'найденные переменные ');
        $this->message($this->normalize_request , 'после удаления переенных ');

        $this->expressions = self::findExpressions($this->normalize_request);
        $this->message($this->expressions, 'найденные выражения');

        $this->count_expressions = sizeof($this->expressions);

    }

    private function message($value, $msg){
        if ($this->debug){
            if ( is_array($value) ){
                echo $msg.'<br><pre>'.var_export($value, true).'</pre> <br>   ';
            }else{
                echo $value.' - '.$msg.'<br>';
            }
            echo '<hr>';
        }
    }

    private function parseAndRemoveVariables($text)
    {
        if (!preg_match('#\[(.*)\]#is', $text, $variables)) {
            return $text;
        }

        $result = [];
        $text = str_replace('[' . $variables[1] . ']', '', $text);
        $variables = preg_replace('#\s#', '', $variables[1]);

        preg_match_all('#([a-z]{1,3})=([^;\]]*)#i', $variables, $found);

        $i = 0;
        foreach ($found[1] as $var) {
            $result[$var] = $found[2][$i];
            $i++;
        }
        if (sizeof($result) == 0) {
            return $text;
        }
        $this->variables = $result;
        return $text;
    }

    static function findExpressions($text){
        $result = [];
        if ( !preg_match_all('#([-0-9 .,+/*=^a-z:)(]{2,})#is', $text, $founds) ){
            return $result;
        }

        foreach( $founds[0] as $expr ){
            $expr = self::normalize($expr);
            if ( false!==$expr ){
                $type = \DetectTypeExpression::detectType($expr);

                if ( $type!=false ){
                    array_push($result, $expr);
                }

            }
        }
        return $result;
    }

    static function normalize($expression){
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
    static function detectUtf($text)
    {
        return preg_match('#[√²]#iu', $text);
    }

    static function detectCyrillic($text)
    {
        return preg_match('#[а-я]#iu', $text);
    }

    static function detectTask($value){
        $words = preg_split('/ /ui',$value);
        if (sizeof($words)>15){
            return true;
        }
    }

    static function clearRequest($text)
    {
        $patterns = [
            '/×/'=>'*',
            '/:/'=>'/',
            '/÷/'=>'/',
            '/=$/'=>'',
            '/,/'=>'.',
            '/ +/'=>'',
            '/[а-я.,:!]{2,}/ui'=>'',
            '/^[a-zа-я]([0-9])/ui'=>'\1',
            '/²/u'=>'^2',
            '/х/u'=>'x',
            '/у/u'=>'y',
            '/а/u'=>'a',
            '/с/u'=>'c',
        ];
        //$text = mb_str_replace('×', '*', $text);
        //$text = str_replace(':', '/', $text);
        //$text = preg_replace('#=$#', '', $text);
        //$text = str_replace(',', '.', $text);

        //$text = preg_replace('# +#', '', $text);
        //$text = preg_replace('#[а-я.,:!]{2,}#i', '', $text);
        foreach($patterns as $find=>$replace){
            $text = preg_replace($find, $replace, $text);

        }
        return $text;
    }
}