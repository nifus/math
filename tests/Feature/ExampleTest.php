<?php

namespace Tests\Feature;

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
    public function testBasicTest()
    {
        $response = $this->post('/api/check',['text'=>'2+2']);
        $response->assertStatus(200)->assertSee('4');

        $response = $this->post('/api/check',['text'=>'проверка 2+2']);
        $response->assertStatus(200)->assertSee('4');

        $response = $this->post('/api/check',['text'=>'проверка']);
        $response->assertStatus(200)->assertSee('vk.com');

        $response = $this->post('/api/check',['text'=>'2,2*2']);
        $response->assertStatus(200)->assertSee('4.4');

        $response = $this->post('/api/check',['text'=>'2.2*2']);
        $response->assertStatus(200)->assertSee('4.4');

        $response = $this->post('/api/check',['text'=>'проверка100*x=10 проверка']);
        $response->assertStatus(200)->assertSee('10');

        $response = $this->post('/api/check',['text'=>'проверкаsqrt(9) проверка']);
        $response->assertStatus(200)->assertSee('3');
        $response = $this->post('/api/check',['text'=>'проверка 3^3 проверка']);
        $response->assertStatus(200)->assertSee('27');
    }
}
