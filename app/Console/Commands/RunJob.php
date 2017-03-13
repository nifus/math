<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Job;
class RunJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run-job';

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
        $i = 0;
        while( $i<10 ){
            $continue = Job::runJob();
            if ($continue!==true){
                break;
            }
            sleep(10);
            $i++;
        }

    }
}
