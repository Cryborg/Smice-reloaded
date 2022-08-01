<?php

namespace App\Jobs;

use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Models\User;
use App\Models\WaveTarget;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webpatser\Uuid\Uuid;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Storage;

class ImageMissionZipJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var WaveTarget[]
     */
    private $results = [];
    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $results, User $user)
    {
        $this->results = $results;
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
        if (!empty($this->results)) {
            $token = Uuid::generate(4)->string;
            $files = [];
            foreach ($this->results as $r) {

                //getuuid
                $r['url'] = str_replace('api.smice.com', 'ik.imagekit.io/smice', $r['url']);

                $path = parse_url($r['url'], PHP_URL_PATH);
                $path = str_replace('/smice', '', $path);
                $r['url'] = str_replace('smice/images/', 'smice/images/tr:n-pdf_report/', $r['url']);
                $filename = $r['wave_target_id'] . "_" .  $r['shop_id'] . "_" .  $r['question_id'] . "_" . $r['id'];
                $files[] = storage_path('app/public/tmp/') . $filename . ".jpg";
                $path = Storage::disk('local')->put('public/tmp/' . $filename . ".jpg", file_get_contents($r['url']));

            }

            Zipper::make(storage_path('app/public/tmp/' . $token . '.zip'))->add($files)->close();
            $file = storage_path('app/public/tmp/') . $token . '.zip';
            Storage::put('/public/ZIP/' . $token . '.zip', file_get_contents($file));
            $file = request()->getSchemeAndHttpHost() . '/zip/' . $token . '.zip';

            foreach ($files as $f) {
                unlink($f);
            }

            SmiceMailSystem::send(SmiceMailSystem::REPORT_ZIP, function ($message) use ($file) {
                $message->to([$this->user->id]);
                $message->subject('Smice - Vos images sont disponibles');
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
