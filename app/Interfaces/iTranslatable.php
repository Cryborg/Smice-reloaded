<?php

namespace App\Interfaces;

interface iTranslatable
{
    /**
     * Implementing this interface will add a SQL condition when retrieving
     * multiple records.
     * The condition is:
     *
     *  SELECT
     *          translatable_field->>'language_code' as translatable_field
     *
     * This is a JSON specific Postgresql function. It will retrieve only the value
     * of the JSON where the key is equal to the language code.
     *
     *  <!! The JSON field must be present in the $translatables array of the model. !!>
     *
     * language_code will be the current language code of the user making the request.
     */
}