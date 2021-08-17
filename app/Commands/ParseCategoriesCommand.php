<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
        foreach ($categories as $category) {
            // validate json
            $validator = Validator::make($category, [
                'title' => ['required', 'string', 'min:3', 'max:12'],
                'eId' => ['nullable', 'integer', 'min:0']
            ]);
            if ($validator->fails()) {
                $key = $validator->errors()->keys()[0];
                $message = $validator->errors()->messages()[$key][0];
                $this->comment('Failed validation on ' . $key . ': ' . $category[$key] . ' with message ' . $message);

                $bar->advance();
                continue;
            }

            // store to db
            DB::table('categories')->insert([
                [
                    'title' => $category['title'],
                    'eId' => $category['eId']
                ]
            ]);

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
