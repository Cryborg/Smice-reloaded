<?php

namespace App\Jobs;

use App\Classes\Factory\ReportPdfFactory;
use App\Models\User;
use App\Models\WaveTarget;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Classes\SmiceClasses\SmiceMailSystem;
use Webpatser\Uuid\Uuid;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Storage;


class ReportZipJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $data;
    private $type;
    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data, $type, User $user)
    {
        $this->data = $data;
        $this->type = $type;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $token = Uuid::generate(4)->string;
        // If you do not put a condition, then "laravel.log" will be crowded
        if (!empty($this->data)) {
            $files = [];
            foreach ($this->data as $target) {
                $reportFactory = new ReportPdfFactory();
                switch ($this->type) {
                    case ReportPdfFactory::TYPE_STANDARD: $report = $reportFactory->create(ReportPdfFactory::TYPE_STANDARD);
                        break;
                    case ReportPdfFactory::TYPE_IMAGE: $report = $reportFactory->create(ReportPdfFactory::TYPE_IMAGE);
                        break;
                    case ReportPdfFactory::TYPE_NOT_COMPLIANT: $report = $reportFactory->create(ReportPdfFactory::TYPE_NOT_COMPLIANT);
                        break;
                        case ReportPdfFactory::TYPE_SYNTETIC: $report = $reportFactory->create(ReportPdfFactory::TYPE_SYNTETIC);
                    break;
                    default: $report = $reportFactory->create(ReportPdfFactory::TYPE_STANDARD);
                        break;
                }
                //getuuid
                $wt = WaveTarget::find($target['id']);
                $report->generateReport($wt['uuid']);
                $waveTarget = WaveTarget::find($target['id']);
                $files[] = storage_path('app/public/PDF/') . $waveTarget->pdf_url;
            }

            Zipper::make(storage_path('app/public/' . $token . '.zip'))->add($files)->close();
            $file = storage_path('app/public/') . $token. '.zip';
            Storage::put('/public/ZIP/'. $token. '.zip', file_get_contents($file));
            $file = request()->getSchemeAndHttpHost() . '/zip/' . $token . '.zip';
            SmiceMailSystem::send(SmiceMailSystem::REPORT_ZIP, function ($message) use ($file) {
                $message->to([$this->user->id]);
                $message->subject('Smice - Vos rapports sont disponibles');
                $message->addMergeVars([
                    $this->user->id => [
                        'name' => $this->user->first_name,
                        'zip_url' => $file
                    ]
                ]);
            }, $this->user->language->code);

            
        }
    }
}
