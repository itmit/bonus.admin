<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\StockArchive;

class archiveStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:archiveStock';

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
        $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        $stocks = Stock::where('expires_at', '<=', $yesterday)->get();
        foreach($stocks as $stock)
        {
            StockArchive::create([
                'uuid' => Str::uuid(),
                'client_id' => $stock->client_id,
                'service_id' => $stock->service_id,
                'country' => $stock->country,
                'city' => $stock->city,
                'name' => $stock->name,
                'description' => $stock->description,
                'photo' => $stock->photo,
                'expires_at' => $stock->expires_at,
                'sub_only' => $stock->sub_only,
                'created' => $stock->created_at
            ]);
            $stock->delete();
        }
        return $this->sendResponse([], 'Stocks has been archived');
    }
}
