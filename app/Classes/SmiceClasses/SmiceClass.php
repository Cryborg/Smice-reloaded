<?php

namespace App\Classes\SmiceClasses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class SmiceClass implements Arrayable, Jsonable
{
    /**
     * Convert the class attributes to an array
     *
     * @return array
     */
    public function     toArray()
    {
        return $this->_getAttributesArray($this);
    }

    /**
     * Convert the class attributes to JSON
     * @param int $options
     * @return string
     */
    public function     toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Magic method to serialize the class
     * @return string
     */
    public function     __toString()
    {
        return $this->toJson();
    }

    private function    _getAttributesArray($class)
    {
        $attributes     = get_object_vars($class);

        foreach ($attributes as $key => $attribute)
        {
            if ($attribute instanceof Arrayable)
                $attributes[$key] = $attribute->toArray();
        }

        return $attributes;
    }
}