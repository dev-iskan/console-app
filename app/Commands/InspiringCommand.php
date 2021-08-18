<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class InspiringCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'inspiring';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filename = __DIR__ . '/../../products.json';
        $fileHandle = fopen($filename, "r");

        $json_string = '';
        while (!feof($fileHandle)) {
            $line = fgets($fileHandle);
            $line = trim(preg_replace('/\s+/', ' ', $line));

            if (strpos($line, '{') !== false) {
                $json_string = $line;
                continue;
            }
            if (strpos($line, '}') !== false) {
                $line = str_replace(',', '', $line);
                $json_string .= $line;

                $product = json_decode($json_string, true);
                // clear string
                $json_string = '';
                // do logic with product
            }
            if ($json_string !== '') {
                $json_string .= $line;
            }
        }

        fclose($fileHandle);
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
