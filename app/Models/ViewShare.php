<?php

namespace App\Models;

use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Illuminate\Support\Facades\DB;

class ViewShare extends SmiceModel implements iREST, iProtected
{

    protected $table                = 'view_share';

    protected $primaryKey           = 'id';

    public $timestamps              = false;

    public static function getURI()
    {
        return 'view-shares';
    }

    public static function getName()
    {
        return 'viewShare';
    }

    public function getModuleName()
    {
        return 'view-shares';
    }

    protected $jsons = [
        'filters'
    ];

    protected $fillable             = [
        'view_id',
        'society_id',
        'user_id',
        'group_id',
    ];

    protected $hidden = [
        'society_id'
    ];

    protected array $list_rows = [];

    protected array $rules        = [
        'view_id'           => 'integer|required|read:view',
        'society_id'        => 'integer|required|read:society',
        'user_id'           => 'integer|read:user',
        'group_id'          => 'integer|read:group',
    ];

    public function view()
    {
        return $this->belongsTo('App\Models\View');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function user()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function group()
    {
        return $this->belongsToMany('App\Http\Group\Models\Group');
    }


    protected static function boot()
    {
        parent::boot();
        self::created(function (self $viewShare) {

            if ($viewShare->user_id && $viewShare->view_id) {
                //read dashboard name
                $u = User::find($viewShare->user_id);
                $v = View::find($viewShare->view_id);
                $creator = User::find($v->created_by);
                SmiceMailSystem::send(SmiceMailSystem::SHARE_DASHBOARD, function (SmiceMailSystem $message) use ($v, $u, $creator) {
                    $message->to([$u->id]);
                    $message->subject('Vous avez un nouveau dashboard');
                    $message->addMergeVars([
                        $u->id => [
                            'name-expediteur' => $creator->first_name . " " . $creator->last_name,
                            'name-dashboard' => $v->name,
                        ]
                    ]);
                }, $u->language->code);
            }

            if ($viewShare->group_id && $viewShare->view_id) {
                //read dashboard name
                $group_user = DB::table('group_user')
                    ->where('group_id', $viewShare->group_id)
                    ->select('group_user.user_id')
                    ->get();

                foreach ($group_user as $user_id) {
                    $user_ids[] = $user_id['user_id'];
                }
                $v = View::find($viewShare->view_id);
                $creator = User::find($v->created_by);
                $user_ids = User::whereIn('id', $user_ids);
                foreach ($user_ids as $u) {
                    SmiceMailSystem::send(SmiceMailSystem::SHARE_DASHBOARD, function (SmiceMailSystem $message) use ($v, $u, $creator) {
                        $message->to([$u]);
                        $message->subject('Vous avez un nouveau dashboard');
                        $message->addMergeVars([
                            $u => [
                                'name-expediteur' => $creator->first_name . " " . $creator->last_name,
                                'name-dashboard' => $v->name,
                            ]
                        ]);
                    }, $u->language->code);
                }
            }
        });
    }
}
