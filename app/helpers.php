<?php

if (! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    function array_get(array $array, string $key, mixed $default = null): mixed
    {
        return Arr::get($array, $key, $default);
    }
}
