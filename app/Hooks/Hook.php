<?php

namespace App\Hooks;

use App\Jobs\HookRunnerJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

abstract class Hook
{
    CONST HOOK_QUEUE = 'hooks';

    /**
     * The id of the society running the hook
     * @var null
     */
    protected static $society_id = null;

    /**
     * The conditions necessary for the hook to run
     *
     * @var array
     */
    private $conditions = [];

    /**
     * The enum get in alert to get json to load
     *
     * @var string
     */
    private $condition_type = null;


    /**
     * The name of the hook
     *
     * @var null
     */
    protected static $action = null;

    /**
     *  Defines whether the hook is a system one and should always be launched
     *
     * @var bool
     */
    protected $system = false;

    /**
     * Static function called to launch any hook from anywhere
     *
     * @param $hook
     * @param \Closure $callback
     * @return bool
     */
    public static function launch($hook, \Closure $callback)
    {
        $hook = static::_newHook($hook);

        if (!$hook) {
            return false;
        }
        if ($hook->isRegistered()) {
            $callback($hook);
            Bus::dispatch(
                (new HookRunnerJob($hook))->onQueue(self::HOOK_QUEUE)
            );
        } else {
            return false;
        }

        return true;
    }

    /**
     * Static function called to launch any hook from anywhere
     *
     * @param $hook
     * @param $delay
     * @param \Closure $callback
     * @return bool
     */
    public static function launchWithDelay($hook, $delay, \Closure $callback)
    {
        $hook = static::_newHook($hook);

        if (!$hook)
            return false;
        if ($hook->isRegistered()) {
            $callback($hook);
            Bus::dispatch(
                (new HookRunnerJob($hook))->onQueue(self::HOOK_QUEUE)->delay($delay)
            );
        } else
            return false;

        return true;
    }

    /**
     * This function is called by the HookRunner.
     * It runs all the necessary code when implementing a Hook.
     * @return mixed
     */
    public abstract function run();

    final static public function getAction()
    {
        return (static::$action) ? static::$action : get_called_class();
    }

    private final function setConditions($conditions)
    {
        $this->conditions = json_decode($conditions, true);
    }

    private final function setConditionType($condition_type)
    {
        $this->condition_type = $condition_type;
    }

    public final function getConditionType()
    {
        return $this->condition_type;
    }

    public final function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Check whether the hook is registered for this company or not
     * The check is performed against the database
     * If the hook exists, the conditions are set.
     * @return bool
     */
    public final function isRegistered()
    {
        $hook = DB::table('alert_v1')
            ->where('action', $this->getAction())
            ->where('society_id', static::$society_id)
            ->join('alert_hook', 'alert_v1.alert_hook_id', '=', 'alert_hook.id')
            ->first();

        if ($hook) {
            $this->setConditions($hook['conditions']);
            $this->setConditionType($hook['condition_type']);
        }

        return ($this->isSystem() || (($hook) ? true : false));
    }

    /**
     * Determines if the hook is a system (mandatory) one or not.
     * @return bool
     */
    public final function isSystem()
    {
        return $this->system;
    }

    /**
     * Initialize the Hook class with the society id
     *
     * @param $society_id
     */
    public static function init($society_id)
    {
        static::$society_id = $society_id;
    }

    /**
     * Instantiate a new Hook
     *
     * @param $hook
     * @return bool|Hook
     */
    private static function _newHook($hook)
    {
        if (!class_exists($hook)) {
            return false;
        } else
            $hook = new $hook;

        if (!$hook instanceof Hook)
            return false;

        return $hook;
    }

    /**
     * Determines if a hook can run given its conditions
     * @return bool
     */
    public function canRun()
    {
        return true;

        // mission or wave id set in callback when triggering alerthook
        // forceRun attribute that will detect if necessary to check condition ex: signup
        // heritate can run in proper hook

        //return true;
    }

}