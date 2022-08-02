<?php

namespace App\Traits;

use Spatie\QueryBuilder\AllowedFilter;

trait RequestToQueryable
{
    public static function allowedFilters(): array
    {
        return array_merge(
            [AllowedFilter::exact('id')],
            static::$allowedFilters ?? []
        );
    }

    public static function allowedSorts(): array
    {
        return static::$allowedSorts ?? [];
    }

    public static function allowedIncludes(): array
    {
        return static::$allowedIncludes ?? [];
    }
}
