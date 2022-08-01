<?php

namespace App\Classes\Services;

use App\Classes\SmiceClasses\SmiceFinder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PaymentSearchService
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
    public function searchSmiceFinder(Builder $query, $queryResult, array $params = [], User $user, array $rows)
    {
        $search_text = mb_strtolower(pg_escape_string(array_get($params, 'q')));
        $id = [];

        if (!empty($search_text)) {
            foreach ($queryResult as $line) {
                foreach ($rows as $field) {
                    if (!empty($line->$field) && (mb_strpos(mb_strtolower($line->$field), $search_text)
                            || mb_strtolower($line->$field) == $search_text)) {
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

    /**
     * @param Illuminate\Support\Facades\DB $query
     * @param array $params
     * @param array $fields_search
     * @return mixed
     */
    public function searchData($query, array $params, array $fields_search)
    {
        $search_text = mb_strtolower(pg_escape_string(array_get($params, 'q')));

        if (!empty($search_text)) {
            $query->where(function ($query) use ($fields_search, $search_text) {
                foreach ($fields_search as $field) {
                    $query->orWhere($field, 'ILIKE', '%'. $search_text .'%');
                }
            });
        }

        return $query;
    }

    /**
     * @param Illuminate\Support\Facades\DB $query
     * @param array $params
     * @return mixed
     */
    public function sortData($query, array $params)
    {
        $sort_field = array_get($params, 'sort_field');
        $sort_method = array_get($params, 'sort_method');

        if ($sort_field && $sort_method) {
            $query->orderBy($sort_field, $sort_method);
        }

        return $query;
    }
}