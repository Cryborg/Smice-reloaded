<?php

namespace App\Models;

use App\Classes\Helpers\NotificationHelper;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Carbon\Carbon;
use App\Classes\SmiceClasses\SmiceMailSystem;


/**
 * App\Models\Actionplan
 *
 * @property int $id
 * @property string $name
 * @property string|null $content
 * @property string|null $due_date
 * @property string $priority
 * @property int|null $status_id
 * @property int|null $type_id
 * @property int|null $criteria_id
 * @property int|null $shop_id
 * @property int $society_id
 * @property int|null $wave_id
 * @property int|null $sequence_id
 * @property int|null $answer_id
 * @property int|null $assigned_to
 * @property bool $auto_reminder
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int|null $updated_by
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property bool|null $fixed
 * @property string $score
 * @property int|null $axe_id
 * @property int|null $axe_directory_id
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ActionplanComments[] $comments
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\Criteria|null $criteria
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ActionplanHistory[] $history
 * @property-read \App\Models\Sequence|null $sequence
 * @property-read \App\Models\Shop|null $shop
 * @property-read \App\Models\ActionplanStatus|null $status
 * @property-read \App\Models\ActionplanType|null $type
 * @property-read \App\Models\User $updatedBy
 * @property-read \App\Models\Wave|null $wave
 * @property-read \App\Models\Axe|null $axe
 * @property-read \App\Models\AxeDirectory|null $axeDirectory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereAutoReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereFixed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereAxeDirectoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Actionplan whereAxeId($value)
 * @mixin \Eloquent
 */
class Actionplan extends SmiceModel implements iREST, iProtected
{
    protected $table = 'actionplan';

    protected $primaryKey = 'id';

    protected $jsons = ['recipient'];

    public $timestamps = true;

    protected $fillable = [
        'name',
        'content',
        'extern',
        'due_date',
        'priority',
        'criteria_id',
        'shop_id',
        'axe_id',
        'axe_directory_id',
        'society_id',
        'wave_id',
        'answer_id',
        'assigned_to',
        'auto_reminder',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'status_id',
        'extern',
        'wave_target_id'
    ];

    public static $history = [
        'name',
        'content',
        'extern',
        // we check due date manually
        // 'due_date',
        'priority',
        'extern',
        'assigned_to',
        'status_id',
        'type_id'
    ];

    public static $emailalert = [
        'assigned_to',
        // 'due_date',
        'extern',
        'status_id'
    ];

    protected $hidden = [];

    protected array $rules = [
        'name' => 'string|required',
        'content' => 'string',
        'extern' => 'string',
        'criteria_id' => 'integer|required|read:criteria',
        'wave_target_id' => 'integer|read:wavetarget',
        'priority' => 'integer|required',
        'assigned_to' => 'integer|required|read:users',
        'auto_reminder' => 'boolean|required',
        'created_by' => 'integer|required|read:users',
        'updated_by' => 'integer|required|read:users',
    ];

    public static function getURI()
    {
        return 'actionplans';
    }

    public static function getName()
    {
        return 'actionplan';
    }

    public function getModuleName()
    {
        return 'actionplans';
    }

    public function status()
    {
        return $this->belongsTo('App\Models\ActionplanStatus', 'status_id');
    }

    public function history()
    {
        return $this->hasMany('App\Models\ActionplanHistory')->orderBy('id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\ActionplanComments');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function axe()
    {
        return $this->belongsTo('App\Models\Axe');
    }

    public function axeDirectory()
    {
        return $this->belongsTo('App\Models\AxeDirectory');
    }

    public function sequence()
    {
        return $this->belongsTo('App\Models\Sequence', 'sequence_id');
    }

    public function criteria()
    {
        return $this->belongsTo('App\Models\criteria');
    }

    public function wave()
    {
        return $this->belongsTo('App\Models\Wave');
    }

    public function wavetarget()
    {
        return $this->belongsTo('App\Models\WaveTarget', 'wave_target_id');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\ActionplanType', 'type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->select(array('id', 'first_name', 'last_name', 'email'));
    }

    public function assignedTo()
    {
        return $this->belongsTo('App\Models\User', 'assigned_to')->select(array('id', 'first_name', 'last_name', 'email'));
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User')->select(array('id', 'first_name', 'last_name', 'email'));
    }

    public function addHistoryUpdate($userId)
    {
        $snapshot = [];

        foreach (Actionplan::$history as $field) {
            if ($this->isDirty($field)) {
                $snapshot[$field] = $this->$field;
            }
        }

        // compare due dates
        $originalDueDate = new Carbon($this->getOriginal('due_date'));
        $originalDueDate = $originalDueDate->toDateString();
        $dueDate = new Carbon($this->due_date);
        $dueDate = $dueDate->toDateString();

        if ($originalDueDate !== $dueDate) {
            $snapshot['due_date'] = $dueDate;
        }

        if (!empty($snapshot['assigned_to'])) {
            // set the user first name / last name in plain string
            $user = User::select('id', 'first_name', 'last_name')
                ->where('id', $this->assigned_to)
                ->first();

            $snapshot['assigned_to'] = $user->first_name . ' ' . $user->last_name;
        }

        if (sizeof($snapshot)) {
            // save a new history entry only if needed
            $actionplanHistory = ActionplanHistory::create([
                'action' => 'update',
                'actionplan_id' => $this->id,
                'snapshot' => $snapshot,
                'created_by' => $userId,
                'created_at' => Carbon::now()
            ]);
        }
    }

    public function addHistoryCreating($userId, $user)
    {
        $assigned_to = '';
        if ($user)
            $assigned_to = $user->first_name . ' ' . $user->last_name;
        $snapshot = [
            'name' => $this->name,
            'content' => $this->content,
            'due_date' => $this->due_date,
            'priority' => $this->priority,
            'assigned_to' => $assigned_to,
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
        ];

        ActionplanHistory::create([
            'action' => 'creating',
            'actionplan_id' => $this->id,
            'snapshot' => $snapshot,
            'created_by' => $userId,
            'created_at' => Carbon::now()
        ]);
    }

    public function addHistoryClose($userId, $comment, $photos = [])
    {
        $snapshot = [
            'comment' => $comment,
            'fixed' => $this->fixed,
            'photos' => $photos,
        ];

        ActionplanHistory::create([
            'action' => 'closing',
            'actionplan_id' => $this->id,
            'snapshot' => $snapshot,
            'created_by' => $userId,
            'created_at' => Carbon::now()
        ]);
    }

    private static function sendActionplanMail($actionplan, $creating)
    {
        $dateFormat = 'd-m-Y';

        if (!$creating) {
            $change_type = null;
            foreach (Actionplan::$emailalert as $field) {
                if ($actionplan->isDirty($field)) {
                    $change_type = $field;
                }
            }
            if (is_null($change_type)) {
                return;
            }
        }
        // if (!$actionplan->assigned_to)
        //     return;
        /*
        * sendmail
        */

        // $template = ($creating === true) ? SmiceMailSystem::ACTIONPLAN_ASSIGNED : SmiceMailSystem::ACTIONPLAN_UPDATED; ??
        if ($creating === true) {
            $subject = "[SMICE] Vous est attribué : $actionplan->name";
            $template = SmiceMailSystem::ACTIONPLAN_ASSIGNED;
        } else {
            if ($change_type === 'assigned_to' || $change_type === 'extern') {
                $subject = "[SMICE] Vous est attribué : $actionplan->name";
                $template = SmiceMailSystem::ACTIONPLAN_ASSIGNED;
            }
            else if ($change_type === 'due_date') {
                    $subject = "[SMICE] $actionplan->name";
                    $template = SmiceMailSystem::ACTIONPLAN_UPDATED;
            }
            else if ($change_type === 'status_id') { //change status to closed
                if ($actionplan->status_id !== 2)
                    return;
                $subject = "[SMICE] $actionplan->name";
                $template = SmiceMailSystem::ACTIONPLAN_CLOSED;
            }
        }

        $created_at = Carbon::parse($actionplan->created_at)->format($dateFormat);
        $due_date = $actionplan->due_date ? Carbon::parse($actionplan->due_date)->format($dateFormat) : null;
        $due_date_old = $due_date ? Carbon::parse($actionplan->getOriginal($due_date))->format($dateFormat) : null;

        $created_by = User::find($actionplan->created_by);
        if ($actionplan->assigned_to) {
            $assigned_to = User::find($actionplan->assigned_to);
            SmiceMailSystem::send($template, function (SmiceMailSystem $message) use ($actionplan, $subject, $due_date, $due_date_old, $created_at, $created_by, $assigned_to) {
                $key = $actionplan->assigned_to;
                $message->to([[$actionplan->assigned_to]]);
                $message->subject($subject);
                $message->addMergeVars([
                    $key => [
                        'action_plan_name' => $actionplan->name,
                        'assigned_to'      => $assigned_to->email,
                        'content'          => $actionplan->content,
                        'due_date_old'     => $due_date_old,
                        'due_date'         => $due_date,
                        'created_by'       => $created_by->email,
                        'created_at'       => $created_at
                    ],
                ]);
            }, 'fr');
        } else {
            $assigned_to = $actionplan->extern;
            NotificationHelper::sendMail($assigned_to, $subject, $actionplan, 'actionplan', $template);
        }
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $actionplan) {
            self::sendActionplanMail($actionplan, true);
        });
        self::updating(function (self $actionplan) {
            self::sendActionplanMail($actionplan, false);
        });
    }
}
