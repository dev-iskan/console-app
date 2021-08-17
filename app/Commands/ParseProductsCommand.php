<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use JsonMachine\JsonMachine;
use LaravelZero\Framework\Commands\Command;

class ParseProductsCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'parse:products';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command to parse products.json file and create or update by eId products in DB';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Starting products parser');
        $filename = __DIR__ . '/../../products.json';

        $time_start = microtime(true);

        //get category_ids to validate input json

        $products = JsonMachine::fromFile($filename, "");

        $bar = $this->output->createProgressBar();
        foreach ($products as $product) {
            // validate product
            // check


            $bar->advance();
        }

        $time_end = microtime(true);

        $bar->finish();

        $this->newLine();
        $this->info('Successfully parsed! - ' . ($time_end - $time_start));
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
