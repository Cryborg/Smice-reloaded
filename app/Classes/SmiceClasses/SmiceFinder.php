<?php

namespace App\Classes\SmiceClasses;

use App\Http\User\Models\User;
use App\Interfaces\iTranslatable;
use App\Models\SmiceModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SmiceFinder
{
    /**
     * The fields to be queried
     * @var array
     */
    private array $fields = [];

    /**
     * The table fields' type
     * @var array
     */
    private array $field_types = [];

    /**
     * The instance of the query Builder
     * @var Builder|null
     */
    private ?Builder $query;

    /**
     * The model being queried
     * @var Model|null
     */
    private ?Model $model;

    /**
     * The searched string
     * @var string|null
     */
    private ?string $search = null;

    /**
     * The array in which the sort conditions are
     * @var array
     */
    private array $sorts = [];

    /**
     * Boolean conditions to apply to the query
     */
    private array $booleans = [];

    /**
     * The number of records to retrieve per page
     * @var int
     */
    private int $per_page = 450;

    /**
     * Determine if the results must be minimum (id + name)
     * @var bool
     */
    private bool $minimum = false;

    /**
     * Determine if the results must be paginated or not
     * @var bool
     */
    private bool $no_limit = false;

    /**
     * Determine if the results must be translated or not
     * @var bool
     */
    private bool $no_translation = false;

    /**
     * If the model is translatable, the language in which
     * the query should be returned
     * @var string|null
     */
    private ?string $language_code = null;


    public function __construct(Builder $query, array $params = [], User $user = null, $extra_col = null)
    {
        $this->query = $query;
        $this->user = $user;
        $this->extra_col = $extra_col;
        $this->model = $query->getModel();

        /*
         * Set the different variables from the user's request
         */
        $this->_setFieldTypes();
        $this->_setLanguageCode($user);
        $this->_setQueryParams($params);
        $this->_setSearch(array_get($params, 'q'));
        $this->_setFields(array_get($params, 'fields'));
        $this->_setMinimum(array_get($params, 'minimum'));
        $this->_setNoLimit(array_get($params, 'no_limit'));
        $this->_setNoTranslation(array_get($params, 'no_translation'));
        $this->_setPaginate(array_get($params, 'paginate'));
    }

    private function _setFields($fields)
    {
        $queried_fields = [];
        $mandatory_fields = ['id'];
        $possible_fields = array_merge($this->model->getTableRows(), $mandatory_fields);
        dump($possible_fields);
        if ($fields && !is_array($fields)) {
            $queried_fields = explode(',', $fields);
        }

        $valid_queried_fields = array_intersect($queried_fields, $possible_fields);
dump($valid_queried_fields);
        if (empty($valid_queried_fields)) {
            $this->fields = $possible_fields;
        } else {
            $this->fields = array_unique(array_merge($valid_queried_fields, $mandatory_fields));
        }
    }

    private function _setFieldTypes()
    {
        $results = DB::table('information_schema.columns')
            ->select('column_name', 'data_type')
            ->where('table_name', $this->model->getTable())
            ->get();
        $results->each(function ($result) {
            $result = (array) $result;
            $this->field_types[$result['column_name']] = $result['data_type'];
        });

        if (isset($this->extra_col)) {
            foreach ($this->extra_col as $k => $v) {
                $this->field_types[$k] = "extra";
            }
        }
    }

    private function _setSearch($q)
    {
        if (is_string($q) && !empty($q)) {
            $this->search = pg_escape_string($q);
        }
    }

    private function _setSorts($field_name, $field_value)
    {
        if ((array)$field_value === $field_value) {
            if (isset($field_value['asc'])) {
                $this->sorts[$field_name] = 'asc';
            } elseif (isset($field_value['desc'])) {
                $this->sorts[$field_name] = 'asc';
            }
        } elseif ($field_value === 'asc' || $field_value === 'desc') {
            $this->sorts[$field_name] = $field_value;
        }
    }

    private function _setBoolean($field_name, $field_value)
    {
        if ((array)$field_value === $field_value) {
            if (isset($field_value['true'])) {
                $this->booleans[$field_name] = 'true';
            } elseif (isset($field_value['false'])) {
                $this->booleans[$field_name] = 'false';
            }
        } elseif (
            $this->field_types[$field_name] === 'boolean'
            && ($field_value === 'true' || $field_value === 'false')
        ) {
            $this->booleans[$field_name] = ($field_value === 'true') ? true : false;
        }
    }

    private function _setQueryParams(array $params)
    {
        $fields = $this->model->getTableRows();
        foreach ($fields as $field_name) {
            $field_value = array_get($params, $field_name);
            if ($field_value) {
                if ((array)$field_value === $field_value) {
                    $field_value = array_flip($field_value);
                }
                $this->_setSorts($field_name, $field_value);
                $this->_setBoolean($field_name, $field_value);
            }
        }
    }

    private function _setPaginate($per_page)
    {
        if (is_numeric($per_page) && intval($per_page) > 0) {
            $this->per_page = $per_page;
        }
    }

    private function _setMinimum($minimum)
    {
        if (isset($minimum) && method_exists($this->model, 'scopeMinimum')) {
            $this->minimum = true;
            $this->fields = ['id', 'name'];
        }
    }

    private function _setNoLimit($no_limit)
    {
        if (isset($no_limit)) {
            $this->no_limit = true;
        }
    }

    private function _setNoTranslation($no_translation)
    {
        if (isset($no_translation)) {
            $this->no_translation = true;
        }
    }

    private function _setLanguageCode($user)
    {
        if ($this->model instanceof iTranslatable) {
            $this->language_code = ($user)
                ? $user->language->code
                : Config::get('app.locale');
        }
    }

    private function _applyLanguage()
    {
        $translations = array_intersect($this->fields, $this->model->getTranslatableAttributes());
        // Fetch the correct value in the json depending on the language code key.
        foreach ($translations as $translate) {
            $this->query->selectRaw($translate . '->>\'' . $this->language_code . '\' as ' . $translate);
        }
    }

    private function _applySearching()
    {
        if ($this->search) {
            $this->query->where(function ($query) {
                foreach ($this->fields as $name) {
                    if (
                        $this->field_types[$name] === 'text' ||
                        $this->field_types[$name] === 'character varying'
                    ) {
                        $query->orWhereRaw($name . " ILIKE '%" . $this->search . "%'");
                    } else if ($this->field_types[$name] === 'jsonb') {
                        $query->orWhereRaw($name . "->>'" . $this->language_code . "'" . " ILIKE '%" . $this->search . "%'");
                    } else if ($this->field_types[$name] === 'integer' && is_int($this->search)) {
                        $query->orWhere($name, $this->search);
                    }
                }
            });
        }
    }

    private function _applySorting()
    {
        foreach ($this->sorts as $field => $sort) {
            $this->query->orderBy($field, $sort);
        }
    }

    private function _applyBooleans()
    {
        foreach ($this->booleans as $field => $boolean) {
            $this->query = $this->query->where($field, $boolean);
        }
    }

    private function _addExtraCol($response)
    {
        foreach ($this->extra_col['columnDefs'] as $field) {
            $response->rows[] = $field;
            $response->tokens[] = $field;
        }
        $paginator = $response->paginator->toArray();
        foreach ($paginator['data'] as $k => &$v) {
            if (isset($this->extra_col['data'][$v['id']])) {
                foreach ($this->extra_col['data'][$v['id']] as $field => $value) {
                    $v[$field] = $value;
                }
            }
        }
        $response->paginator =  json_decode(json_encode($paginator));

        return $response;
    }

    public function get($custom_key = false)
    {
        $response = new SmiceClass();
        //var_dump($this->user->society_id);exit;
        if ($this->minimum) {
            $this->query->minimum();
        } else if (!$custom_key) {
            $this->query->addSelect($this->fields);
        }

        $this->_applySearching();
        $this->_applySorting();
        if (!$this->no_translation) {
            $this->_applyLanguage();
        }

        if ($this->no_limit) {
            $response->data = $this->query->addPublicResources()->get();
        } else {
            $response->rows = $this->fields; // Put $this->getTableToken() the day Model will have real token names
            $response->tokens = $this->fields;
            $response->paginator = $this->query->addPublicResources()->paginate($this->per_page);
        }
        if (isset($this->extra_col)) {
            $response = $this->_addExtraCol($response);
        }


        return $response;
    }
}
