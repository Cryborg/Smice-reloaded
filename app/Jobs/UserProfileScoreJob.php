<?php

namespace App\Jobs;

use App\Http\User\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

//class UserProfileScoreJob extends Job implements SelfHandling, ShouldQueue
class UserProfileScoreJob extends Job implements ShouldQueue
{
    use DispatchesJobs;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(private User $user) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $scores           = 0;
        $s                = $scores;
        $wave_user_scores = $this->user->waveUsers()->whereIn('status_id', [7, 8])->get();
        foreach ($wave_user_scores as $i => $item) {
            if (count($item->score)) {
                if ($item->score['score'] > 0){
                    $scores += $item->score['score'];
                    $s++;
                }
            }
        }
        //profil score = mmscore
        //if score from mm, read it and add as one mission only
        if ($s > 0) {
            $scores /= $s;
            if (is_float($scores)) {
                $scores = round($scores);
            }

            $this->user->scoring = $scores;
            $this->user->save();
        }
    }
}
