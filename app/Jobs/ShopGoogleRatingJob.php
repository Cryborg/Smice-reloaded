<?php

namespace App\Jobs;

use App\Http\Shops\Models\Shop;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShopGoogleRatingJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $shop = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->shop->google_place_id) {
            $response = \GooglePlaces::placeDetails($this->shop->google_place_id);
            $res = $response->toArray();
            if ($res['status'] === 'OK' && isset($res['result']['rating'])) {
                $this->shop->google_rating = $res['result']['rating'];
                $this->shop->update(['google_rating']);
            }
        }
    }
}
