<?php

namespace App\Jobs;

use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Models\PassageProof;
use App\Models\User;
use App\Models\WaveTarget;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webpatser\Uuid\Uuid;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Storage;

class ImageZipJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var WaveTarget[]
     */
    private $waveTargets = [];
    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $waveTargets, User $user)
    {
        $this->waveTargets = $waveTargets;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // If you do not put a condition, then "laravel.log" will be crowded
         if (!empty($this->waveTargets)) {
            $files = [];
            $token = Uuid::generate(4)->string;
            foreach ($this->waveTargets as $waveTarget) {
                foreach (PassageProof::whereWaveTargetId($waveTarget['id'])->get() as $passageProof) {
                    //get image url
                    $name = $waveTarget['wave'] . " " . $waveTarget['shop'] . "_" . $waveTarget['id'] . ".png";
                    if ($passageProof->url) {
                            // $file = $this->copyFile($passageProof->url, $name);

                        $image = file_get_contents($passageProof->url);
                        file_put_contents(storage_path('app/public/tmp/'). $name, $image);
                        $files[] = storage_path('app/public/tmp/'). $name;

                    }
                    if ($passageProof->url2) {
                       // $file = $this->copyFile($passageProof->url2, $name);
                        //$files[] = $file;
                    }
                    if ($passageProof->url3) {
                        //$file = $this->copyFile($passageProof->url3, $name);
                        //$files[] = $file;
                    }
                }
            }
                $zipName = $token . "-" . $this->waveTargets[0]['program'] . '.zip';
                Zipper::make(storage_path('app/public/' . $zipName))->add($files)->close();
                $file = storage_path('app/public/') . $zipName;
                Storage::put('/public/ZIP/'. $zipName, file_get_contents($file));

                $file = request()->getSchemeAndHttpHost() . '/zip/' . $zipName;

                SmiceMailSystem::send(SmiceMailSystem::REPORT_LINK, function ($message) use ($file) {
                    $message->to([$this->user->id]);
                    $message->subject('Smice – Vos images sont disponibles');
                    $message->addMergeVars([
                         $this->user->id => ['pdf_url' => $file]
                    ]);
            }, $this->user->language->code);
        }
    }

     private function copyFile($url = '', $name = '')
    {
        $file = storage_path('app/images/') . str_replace('http://api.smice.com/images/', '', $url);
        $newFile = storage_path('app/public/ZIP/' . $name . time());
        copy($file, $newFile);
        return $newFile;
    }
    
}
