<?php

namespace App\Hooks;

/**
 * The Hook called is configured to emit an alert on societies configuration
 *
 * Class HookSignUp
 * @package App\Hooks
 */
class HookScores extends Hook
{
    protected static $action = 'scoring alert';

    protected $system = true;

    public final function canRun()
    {
        $conditions = $this->getConditions();
        $condition_type = $this->getConditionType();
        $waves = $conditions['scoring']['wave'];
        $mission = $conditions['scoring']['mission'];

        $waves = ($waves['selected']) ? $waves : $waves['selected'];
        $mission = ($mission['selected']) ? $mission : $mission['selected'];

        if ($waves) {
            $query = WaveScoring::prepareQuery($waves['wave_id'], $waves['types']);
        } else if ($mission) {
            dd('asdsad');
        } else {
            dd($conditions[$condition_type]);
        }
    }

    public final function run()
    {
        return true;
    }
}