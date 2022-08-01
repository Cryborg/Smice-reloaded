<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Shop;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jcf\Geocode\Exceptions\EmptyArgumentsException;

class ShopGooglePlaceIdJob extends Job implements SelfHandling
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
        if ($this->shop->lat && $this->shop->lon) {
            $response = \Geocode::make()->address($this->shop->name ."," . $this->shop->postal_code);
            if ($response) {
                $this->shop->google_place_id = $response->raw()->place_id;
                $this->shop->update(['google_place_id']);
            }
        }
    }
}
