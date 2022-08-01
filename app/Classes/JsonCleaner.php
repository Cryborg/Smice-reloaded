<?php

namespace App\Classes;

use App\Classes\Permissions\Permissions;
use App\Exceptions\SmiceException;

class JsonCleaner
{
    public static function  __callStatic($name, $arguments)
    {
        return static::_cleanAttribute(array_shift($arguments));
    }

    private static function _cleanAttribute($value)
    {
        return is_array($value) ? $value : [];
    }

    public static function  roleSimplePermissions($value)
    {
        $value              = static::_cleanAttribute($value);

        return Permissions::cleanSimplePermissions($value);
    }

    public static function  roleAdvancedPermissions($value)
    {
        $value              = static::_cleanAttribute($value);

        return Permissions::cleanAdvancedPermissions($value);
    }

    public static function  alertConditions($value)
    {
        $value         = static::_cleanAttribute($value);

        return AlertCondition::validateAlertCondition($value);
    }

    public static function  missionFilters($value)
    {
        $value              = static::_cleanAttribute($value);

        return MissionFilter::cleanSkell($value);
    }

    public static function  surveyItemConditions($value)
    {
        $value              = static::_cleanAttribute($value);
        $conditions         = [];
        $tokens             = [
            '&&' => true,
            '||' => true
        ];
        $operators          = [
            '!=' => true,
            '==' => true,
            '>' => true,
            '>=' => true,
            '<' => true,
            '<=' => true
        ];

        foreach ($value as $condition)
        {
            if (!is_array($condition))
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The condition must be of type array.'
                );

            $type = isset($condition['type']) ? strval($condition['type']) : null;
            $token = isset($condition['token']) ? $condition['token'] : null;
            $operator = isset($condition['operator']) ? $condition['operator'] : null;
            $answer_id = isset($condition['answer_id']) ? $condition['answer_id'] : null;
            $question_id = isset($condition['question_id']) ? intval($condition['question_id']) : null;
            $question_col_id = isset($condition['question_col_id']) ? intval($condition['question_col_id']) : null;

            if (!isset($tokens[$token]) && !is_null($token))
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The condition token is invalid.'
                );
            if (!isset($operators[$operator]))
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The condition operator is invalid.'
                );

            array_push($conditions, [
                'type' => $type,
                'token' => $token,
                'operator' => $operator,
                'answer_id' => $answer_id,
                'question_id' => $question_id,
                'question_col_id' => $question_col_id
            ]);
        }

        return $conditions;
    }

    public static function  waveTargetFilters($value)
    {
        $value              = static::_cleanAttribute($value);

        return MissionFilter::cleanSkell($value);
    }
}