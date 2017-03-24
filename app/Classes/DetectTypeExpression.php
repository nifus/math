<?php
/**
 * Created by PhpStorm.
 * User: nifus
 * Date: 20.03.17
 * Time: 18:00
 */
class DetectTypeExpression{

    static function detectType($exp){
        if ( self::isElementaryExpression($exp) ){
            return 'elementary';
        }elseif ( self::isLinearEquation($exp) ){
            return 'linear_equation';
        }elseif ( self::isQuadraticEquation($exp) ){
            return 'quadratic_equation';
        }
        return false;

    }

    /**
     * Простое выражение без переменных
     * @param $exp
     * @return bool
     */
    static function isElementaryExpression($exp){
        if ( preg_match('#[a-z]#',$exp) ){
            return false;
        }
        return true;
    }

    /**
     * Линейные уравнения
     * @param $exp
     * @return bool
     */
    static function isLinearEquation($exp){
        if ( preg_match('#[^a-z]#',$exp) ){
            return false;
        }
        if ( preg_match('#^|sqrt|abs#',$exp) ){
            return false;
        }
        return true;
    }

    static function isQuadraticEquation($exp){
        if ( preg_match('#sqrt|^#',$exp) ){
            return true;
        }
        return false;
    }
}