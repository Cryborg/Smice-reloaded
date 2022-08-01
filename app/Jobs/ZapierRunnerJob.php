<?php

namespace App\Jobs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use GuzzleHttp;

class ZapierRunnerJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $hooks      = null;

    private $payload    = null;

    public function     __construct(Collection $zapier_hooks, array $payload)
    {
        $this->hooks    = $zapier_hooks;
        $this->payload  = json_encode($payload);
    }

    public function     handle()
    {
        foreach ($this->hooks as $hook)
        {
            $client = new GuzzleHttp\Client();

            $response = $client->request('POST', $hook->target_url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => $this->payload
            ]);
            if ($response->getStatusCode() === 410)
                $hook->delete();
        }
    }
}