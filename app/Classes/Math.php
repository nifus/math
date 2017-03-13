<?php
class Math{
    /**
     * 10x * 10
     * 10 * 10x
     *
     * @param $v1
     * @param $v2
     */
    static function multiplication($v1, $v2){
        $mul = preg_replace('#[^[0-9]]#','',$v1)*preg_replace('#[^[0-9]]#','',$v2);

        if ( preg_match('#[a-z]{1,3}#i',$v1, $find) ){
            $mul.=$find[0];
        }
        if ( preg_match('#[a-z]{1,3}#i',$v2, $find) ){
            $mul.=$find[0];
        }
        if ($v1>0 && $v2>0){
            return $mul;
        }else{
            return '-'.$mul;
        }
    }

    static function division($v1, $v2){
        $mul = preg_replace('#[^[0-9]]#','',$v1)/preg_replace('#[^[0-9]]#','',$v2);

        if ( preg_match('#[a-z]{1,3}#i',$v1, $find) ){
            $mul.=$find[0];
        }
        if ( preg_match('#[a-z]{1,3}#i',$v2, $find) ){
            $mul.=$find[0];
        }
        if ($v1>0 && $v2>0){
            return '+'.$mul;
        }else{
            return '-'.$mul;
        }
    }

    static function moveToRight($expression){
        preg_match('#(.*)=(.*)#i',$expression,$found);
        $right = $found[2];
        $left = $found[1];
        $new_right = $right;
        var_dump($expression);
        preg_match_all('#([+-]?[0-9.a-z]+)#i',$left,$found);
        foreach($found[1] as $f){
            if (preg_match('#[a-z]#',$f)){
                continue;
            }
            $expression = str_replace($f,'',$expression);
            $f = self::invert($f);
            $new_right.=$f;
        }
      //  var_dump($expression);
       return str_replace($right,$new_right,$expression);
    }

    static function simplification($expression){
        var_dump($expression);
        preg_match_all('#=?([+-]?[.0-9]+[+-][0-9.]+)#i',$expression,$found);
        foreach($found as $f){

        }
        dd($found);
    }

    static function invert($value){
        if ($value==0){
            return 0;
        }
        return $value*-1;
    }
}