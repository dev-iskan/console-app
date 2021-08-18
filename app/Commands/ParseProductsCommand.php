<?php

namespace App\Commands;

use App\Helpers\Validation;
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
    protected $description = 'Command to parse products.json file and create products in DB';

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

        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        $products = JsonMachine::fromFile($filename, "");

        $bar = $this->output->createProgressBar();
        foreach ($products as $product) {
            if (!(isset($product['title']) && Validation::title($product['title']))) {
                $this->comment('Title of product should exist and min 3 symbols and max 14');
                $bar->advance();
                continue;
            }
            if (isset($product['eId']) && !Validation::eId($product['eId'])) {
                $this->comment('eId: ' . $product['eId'] . ' of product should be numeric');
                $bar->advance();
                continue;
            }
            if (!(isset($product['price']) && Validation::eId($product['price']))) {
                $this->comment('Price of product should exist and min 0 and max 200');
                $bar->advance();
                continue;
            }
            if (!Validation::in_array_all($product['categoryIds'], $categoryIds)) {
                $this->comment('Categories of product with given ids don\'t exist');
                $bar->advance();
                continue;
            }

            DB::transaction(function () use ($product) {
                $savedProductId = DB::table('products')->insertGetId(
                    [
                        'title' => $product['title'],
                        'price' => $product['price'],
                        'eId' => $product['eId']
                    ]
                );

                $batch = array_map(function ($category_id) use ($savedProductId) {
                    return [
                        'product_id' => $savedProductId,
                        'category_id' => $category_id
                    ];
                }, array_unique($product['categoryIds']));

                DB::table('category_product')->insert($batch);
            });

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
