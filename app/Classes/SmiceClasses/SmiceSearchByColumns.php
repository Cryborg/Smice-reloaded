<?php

namespace App\Classes\SmiceClasses;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SmiceSearchByColumns
{
    /**
     * This method searches through the generated data.
     *
     * @param Builder $query
     * @param $queryResult
     * @param array $params
     * @param User $user
     * @param array $rows
     * @return null
     */
    public function search(Builder $query, $queryResult, array $params = [], User $user, array $rows)
    {
        $search_text = mb_strtolower(pg_escape_string(array_get($params, 'q')));
        $id = [];

        if (!empty($search_text)) {
            foreach ($queryResult as $line) {
                foreach ($rows as $field) {
                    if (!empty($line->$field) && (mb_strpos(mb_strtolower($line->$field), $search_text) || mb_strtolower($line->$field) == $search_text)) {
                        $id[] = $line->id;
                    }
                }
            }

            $result = $query->whereIn('id', $id);

            $response = (new SmiceFinder($result, $params, $user))->get();

            if (property_exists($response, 'paginator')) {
                $property = 'paginator';
            } else {
                $property = 'data';
            }

            return $response->$property;
        } else {
            return null;
        }
    }
}