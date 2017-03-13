<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class math extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'math {t}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expression = $this->argument('t');
        $expression = str_replace(' ','', $expression);
        $expression = str_replace(',','.', $expression);
        if (!preg_match('#[a-z]#i',$expression)){
            $this->info('Это не уравнение');
            return false;
        }
        preg_match('#.*(\(.*[a-x]*.*\)[*/0-9.]*).*=#i',$expression, $result);
        if (!isset($result[1])){
            $this->info('Пока облом');
            return false;
        }
        preg_match('#(([0-9.]?)([*/]?)(\(.*[a-x]*.*\))([*/]?)([0-9.]*)).*=#is',$expression, $result);
        var_dump($result);

        $general = $result[1];
        $front_value = $result[2];
        $front_oper = $result[3];
        $value = $result[4];
        $rear_oper = $result[5];
        $rear_value = $result[6];
        if ( isset($rear_value) ){
            preg_match_all('#([+-]?[0-9a-z.]+)#i',$value, $values);
            $total = '';
            foreach($values[1] as $v){
                if ($rear_oper=='*'){
                    $total .= \Math::multiplication($v, $rear_value);
                }elseif ($rear_oper=='/'){
                    $total .= \Math::division($v, $rear_value);
                }
            }
            $expression = str_replace($general,$total,$expression);
            $expression = \Math::moveToRight($expression);
            $expression = \Math::simplification($expression);

            dd($expression);
        }
        dd($value);
        dd($expression);
    }
}
