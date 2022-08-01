<?php

namespace App\Jobs;

use App\Classes\Factory\ReportPdfFactory;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\WaveTarget;
use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Classes\Helpers\NotificationHelper;

use Illuminate\Support\Facades\Storage;


class ReportPdfJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    private $user = null;
    private $wave_target = null;
    private $forceupdate = null;

    public function __construct(User $user, $wave_target, $forceupdate = false)
    {
        $this->user = $user;
        $this->wave_target = $wave_target;
        $this->forceupdate = $forceupdate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $wave_target = WaveTarget::find($this->wave_target);

        //pdf generation
        $mission = Mission::find($wave_target->mission_id);
        //check if report already create
        $reportFactory = new ReportPdfFactory();
        $pdfName = $reportFactory->create($mission->report_type)->generateReport($wave_target->uuid, $this->forceupdate);
        //get report template
        $file = request()->getSchemeAndHttpHost() . '/pdf/' . $pdfName;
        if ($mission->report_mail_name && $mission->report_mail) {
            $template = $mission->report_mail_name;
        }
        else {
            $template = SmiceMailSystem::REPORT_LINK;
        }
        SmiceMailSystem::send($template,  function ($message) use ($wave_target, $file) {
            $message->to([$this->user->id]);
            $message->subject('Smice – Votre rapport ' . $wave_target->shop->name . ' du ' . $wave_target->visit_date . ' est disponible');
            $message->addMergeVars([
                $this->user->id => [
                    'name' => $this->user->first_name,
                    'target_name' => $wave_target->name,
                    'pdf_url' => $file,
                ]
            ]);
        }, $this->user->language->code);
        NotificationHelper::PushNotification($this->user->email, "Un nouveau rapport Smice est disponible !", $wave_target->shop->name, $file);
    }
}
