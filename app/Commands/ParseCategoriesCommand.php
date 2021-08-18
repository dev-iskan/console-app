<?php

namespace App\Commands;

use App\Helpers\Validation;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use JsonMachine\JsonMachine;
use LaravelZero\Framework\Commands\Command;

class ParseCategoriesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'parse:categories';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command to parse categories json file and create categories in DB';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Starting categories parser');
        $filename = __DIR__ . '/../../categories.json';

        $time_start = microtime(true);

        $categories = JsonMachine::fromFile($filename, "");

        $bar = $this->output->createProgressBar();

        $batch = [];
        foreach ($categories as $category) {
            if (!(isset($category['title']) && Validation::title($category['title']))) {
                $this->comment('Title of category should exists and min 3 symbols and max 14');
                $bar->advance();
                continue;
            }
            if (isset($category['eId']) && !Validation::eId($category['eId'])) {
                $this->comment('eId: ' . $category['eId'] . ' of category should be numeric');
                $bar->advance();
                continue;
            }

            $batch[] = [
                'title' => $category['title'],
                'eId' => $category['eId']
            ];

            if (count($batch) > 20) {
                // store in db 20 at once
                DB::table('categories')->insert($batch);

                $batch = [];
            }
            $bar->advance();
        }

        // store rest
        DB::table('categories')->insert($batch);

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
