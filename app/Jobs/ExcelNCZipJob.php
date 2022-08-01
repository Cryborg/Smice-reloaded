<?php

namespace App\Jobs;

use App\Classes\Services\ReportExcelService;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Classes\SmiceClasses\SmiceMailSystem;
use Webpatser\Uuid\Uuid;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Storage;

class ExcelNCZipJob extends Job implements SelfHandling, ShouldQueue
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
                $reportExcelService = new ReportExcelService();
                # code...dd
                $time = time();
                $reportExcelService->exportSurveyNC($target, $this->user, $target['id'], $target['shop'], $time);
                //get_report url

                $files[] = storage_path('app/public/') . $target['shop'] . $time. '.xlsx';
                //foreach report generate & get file name
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
