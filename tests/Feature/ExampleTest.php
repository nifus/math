<?php

namespace Tests\Feature;

use App\Post;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasic()
    {
        $tests = [
            '2+2'=>'4',
            'проверка 2+2'=>'4',
            'проверка'=>'vk.cc',
            '2,2*2'=>'4.4',
            '2.2*2'=>'4.4',
            '1/2'=>'0.5',
            '1000/2111'=>'0.4737091425864519',
            'проверка100*x=10 проверка'=>'0.1',
            'проверкаsqrt(9) проверка'=>'3',
            '3^3'=>'27',
            '2^3+26'=>'34',
            '(x+2,3)*0,2=0,7 проверка'=>'1.2',
            '4,2x+8,4=14,7'=>'1.5',
            '(m-0,67)*0,02=0,0152'=>'1.43',
            '6(2x-3)+ 2(4-3x)=5'=>'2.5',
            'abs(x)+abs(-12)=abs(-22)'=>'10',
            '10x=100+10x-x'=>'100',
            '400-4х+6'=>'vk.cc',
            //'400-4х+6'=>'vk.cc',
                //  квадратные
            'x(x-30)'=>'x = 0',
            'x(x+2)'=>'x = 0',
            '2x-1/x+2'=>'x = 0.36',
            '(x-y)^2'=>'http',
            '8a+122 [a=1.5]'=>'134',
            'abs(-1)'=>'1',
            'abs(1)'=>'1',
                //  системы
            '3*x-8*y=22
7*x+8*y=78' =>'y = 1, x = 10',
            'x^2+xy=28,
y^2+xy=-12'=>'y = 3, x = - 7'

        ];
        foreach($tests as $key=>$value){
            //echo $key;
            $response = $this->post('/api/check',['text'=>$key]);
            $response->assertStatus(200)->assertSee($value);

        }


        $db_tests = Post::getTests();
        foreach($db_tests as $test){
            $response = $this->post('/api/check',['text'=>$test->post]);
            $response->assertStatus(200)->assertSee($test->test_result);
        }
    }

    /*public function testTransformText()
    {
        $tests = [
            '2+2'=>'4',
        ];

        foreach($tests as $key=>$value){
            //echo $key;
            $response = $this->post('/api/check/transform',['text'=>$key]);
            $response->assertStatus(200)->assertSee($value);

        }



    }*/
}
