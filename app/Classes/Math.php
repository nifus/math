<?php
class Math{

    private $expression;
    function __construct($expression)
    {
        $this->expression = $expression;
    }

    public function solve(){
        return $this->simplification($this->expression);
       // if ( preg_match('/()/i',$this->expression) ){
//
       // }
    }

    private function simplification($exp){
        if ( !preg_match('#\(#', $exp) ){
            return $exp;
        }
        if ( preg_match('#-\(([+-0-9.]*)\)#',$exp, $found) ){

            $invert=$found[1]*-1;
            $exp = str_replace('-('.$found[1].')',$invert,$exp);
        }

            //  расскрываем скобки в которых
        if ( preg_match('#-\((\+|-[0-9.]*)\)#',$exp, $found) ){
            $invert=$found[1]*-1;
            $exp = str_replace('-('.$found[1].')',$invert,$exp);
        }
        if ( preg_match('#\+\(([+-])?([0-9.]*)\)#',$exp, $found) ){
            if ( $found[1]=='-'){
                $exp = str_replace('+('.$found[1].$found[2].')','-'.$found[2],$exp);
            }else{
                $exp = str_replace('+('.$found[1].$found[2].')','+'.$found[2],$exp);
            }

        }
        $exp = preg_replace('#\(([+-]?[0-9.]*)\)#','\1', $exp);

        return $this->simplification($exp);
    }



    private function getConstants($expressions){

        return [];
    }

    private function invert($value){
        $result = [];
        if ( is_array($value) ){
            foreach($value as $el ){
                array_push($result, $el*-1);
            }
            return $result;
        }else{
            return $value*-1;
        }

    }


}