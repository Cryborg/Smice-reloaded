<?php

namespace App\Classes\SmiceClasses;

use App\Exceptions\SmiceException;
use App\Models\Language;
use Illuminate\Support\Facades\Validator;

class SmiceTranslator
{
    public static function getTranslations()
    {
        return array_fill_keys(array_keys(Language::all()->keyBy('code')->toArray()), '');
    }

    /**
     * Check a table of translations.
     * Check if the table contains the the good keys and if the codes / names
     * are valid.
     *
     * @param array $values
     * @return array
     * @throws SmiceException
     */
    public static function translate($values)
    {
        $translations = self::getTranslations();
        /*
         * Check if the values are correctly formatted
         */

        $validator = Validator::make(
            ['translations' => $values],
            ['translations' => 'array']);
        $validator->passOrDie();
        /*
         * Hotfix since translations have been handled directly by the Model.php when
         * filling an attribute
         */

        $values = (!$values) ? $translations : $values;
        foreach ($values as $code => $value) {
            if (!isset($translations[$code])) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The code does not exists.'
                );
            }
            if ($translations[$code] !== '') {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The translation for language ' . $code . ' already exists.'
                );
            }
           
            $translations[$code] = $value;
        }

        return $translations;
    }
}