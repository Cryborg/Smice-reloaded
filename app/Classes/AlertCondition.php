<?php

namespace App\Classes;

class AlertCondition
{
	/**
	* function that return skell example to create a personalised alert
	*
	*/
	static public function scoreSkell()
	{
		$skell = [
			'target' => [
				'target_id' 		   => null,
				'proposed'             => false,
				'refused_proposition'  => false,
				'refused_visit'        => false,
				'confirmed_visit'      => false,
				'waiting_survey'       => false,
				'refunded'             => false,
				'outdated_proposition' => false,
				'to_confirmed'         => false,
				'mark_as_read'         => false,
				'invalidated'          => false,
				'waiting_validation'   => false,
				'waiting_lecture'      => false,
				'to_prepare'           => false,
				'realisation_proof'    => false,
			],
			'scoring' => [
				'wave' => [
					'wave_id' => null,
					'selected' => false,
					'types' => [
						'global_score' => [
							'selected' => false,
							'conditions' => [
								'equal' 	=> ['selected' => false, 'value' => null],
								'greater'	=> ['selected' => false, 'value' => null],
								'lesser' 	=> ['selected' => false, 'value' => null],
								'between'	=> ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'sequence' => [
							'selected' => false,
							'conditions'  => [
								'equal'   => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser'  => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'theme' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'job' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'criteria_a' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'criteria_b' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						]
					]
				],
				'mission' => [
					'selected' => false,
					'types' => [
						'global_score' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'sequence' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'theme' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'job' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'criteria_a' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						],
						'criteria_b' => [
							'selected' => false,
							'conditions' => [
								'equal' => ['selected' => false, 'value' => null],
								'greater' => ['selected' => false, 'value' => null],
								'lesser' => ['selected' => false, 'value' => null],
								'between' => ['selected' => false, 'min' => null, 'max' => null]
							]
						]
					]
				]
			]
		];

		return $skell;
	}

    static private function     array_walk_recursive(array $array, $callback, $parent_key = null)
    {
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                static::array_walk_recursive(
                    $value,
                    $callback,
                    ($parent_key) ? ($parent_key. "." .$key) : $key);
            }
            else
                $callback($key, $value, $parent_key);
        }
    }

    static public function      validateAlertCondition($conditions)
    {
        $clean_skell            = static::scoreSkell();

        static::array_walk_recursive($clean_skell, function($key, $item, $parent_key) use (
            $conditions, &$clean_skell)
        {
            $value = array_get($conditions, $parent_key. "." .$key);

            if ($value !== null && ((is_bool($item) && is_bool($value)) || !is_bool($item)))
                array_set($clean_skell, $parent_key. "." .$key, $value);
            elseif (($key === 'min' || $key === 'max' || $key === 'value') && is_numeric($value))
                array_set($clean_skell, $parent_key. "." .$key, intval($value));
        });

        return $clean_skell;
    }
}