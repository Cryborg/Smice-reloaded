<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Classes\SmiceClasses\SmiceMailSystem;
use Illuminate\Support\Facades\DB;
use App\Models\WaveTarget;
use App\Models\WaveUser;

class ConfirmationVerificationJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    private $target          = null;
    private $wave_user       = null;
    private $status          = null;


    public function __construct($uuid, $target_id, $status)
    {
        $this->target    = WaveTarget::find($target_id);
        $this->wave_user = $this->target->waveUsers->where('uuid', $uuid)->first();
        $this->status    = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
    }

   
}