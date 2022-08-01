<?php

namespace App\Jobs;

use App\Hooks\Hook;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class HookRunnerJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $hook       = null;

    public function     __construct(Hook $hook)
    {
        $this->hook     = $hook;
    }

    public function     handle()
    {
        if ($this->hook->canRun())
            $this->hook->run();
    }
}