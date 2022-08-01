<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use App\Http\User\Models\User;
use App\Interfaces\iPublicable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Translatable\HasTranslations;

abstract class SmiceModel extends Model
{
    use HasTranslations;

    /**
     * Mass assignment attribute
     * @var array
     */
    protected array $mass_fillable = [];

    /**
     * Importation assignment attribute
     */
    protected array $exportable = [];

    /**
     * The model validation rules
     * @var array
     */
    protected array $rules = [];

    /**
     * The model hidden attributes
     * @var array
     */
    protected $hidden = [];

    /**
     * The model's children
     * @var array
     */
    protected array $children = [];

    /**
     * Files belonging to the model
     * @var array
     */
    protected array $files = [];

    /**
     * The model's columns retrieved by the SmiceFinder
     * @var array
     */
    protected array $list_rows = [];

    /**
     * The model's column names retrieved by the SmiceFinder
     * @var array
     */
    protected array $list_tokens = [];

    /**
     * The model's guarded attributes.
     * (Attributes used after store / update but non-affecting
     * the query)
     * @var array
     */
    private array $guarded_attributes = [];

    protected array $translatable = [];

    protected static function boot()
    {
        parent::boot();

        self::deleting(function ($model) {
            self::_deleteFiles($model, true);
        });

        self::updated(function ($model) {
            self::_deleteFiles($model);
        });
    }

    /**
     * Deletes any files that belongs to the model.
     * The files must be defined in the $files array
     * @param $model
     * @param bool|false $force
     */
    protected static function _deleteFiles($model, $force = false)
    {
        foreach ($model->files as $file) {
            if ($force || ($model->getOriginal($file) &&
                    ($model->getAttribute($file) != $model->getOriginal($file)))) {
                $path = $model->getOriginal($file);

                if ($path && Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
        }
    }

    /**
     * Validate the rules of the model with the model's attributes
     * @return bool
     * @throws SmiceException
     */
    public function validate(): bool
    {
        $tmp_hidden = $this->hidden;
        $this->hidden = [];
        $replaced_rules = [];

        foreach ($this->rules as $key => $rule) {
            $this->rules[$key] = ($id = $this->getKey())
                ? str_replace('{id}', $id, $rule)
                : str_replace(',{id}', null, $rule);

            $replaced_rules[$key] = $rule;
        }

        $validator = Validator::make($this->toArray(), $this->rules);
        $this->hidden = $tmp_hidden;
        $errors = $validator->errors();
        $item = '';
        foreach ($errors->all() as $msg) {
            $current_item = $this->toArray();
            $message = $msg;
            foreach ($current_item as $error => $value) {
                if (!is_array($value))
                    $item .= ' ' . $error . ' : ' . $value;
            }
            throw new SmiceException(
                SmiceException::HTTP_BAD_REQUEST,
                SmiceException::E_VALIDATION,
                $message . $item
            );
        }
        $validator->passOrDie();

        foreach ($replaced_rules as $key => $rule)
            $this->rules[$key] = $rule;

        return true;
    }

    /**
     * Set the model's guarded attributes
     * @param array $guarded_attributes
     */
    public function setGuardedAttributes(array $guarded_attributes)
    {
        $this->guarded_attributes = $guarded_attributes;
    }

    /**
     * Returns the guarded attributes
     * @return array
     */
    public function getGuardedAttributes(): array
    {
        return $this->guarded_attributes;
    }

    /**
     * Set the new rows to be displayed when retrieving multiple records
     * @param array $rows
     */
    public function setTableRows(array $rows = [])
    {
        $this->list_rows = $rows;
    }

    /**
     * Returns the list of the rows to be displayed in a table
     */
    public function getTableRows(): array
    {
        if (empty($this->list_rows)) {
            $hidden_attributes = array_merge($this->getHidden(), $this->children);

            return array_values(array_diff($this->getFillable(), $hidden_attributes));
        }
        return $this->list_rows;
    }

    public function getTableTokens(): array
    {
        return $this->list_tokens;
    }

    /**
     * A generic scope to add relations the a model.
     * @param $query
     * @return mixed
     */
    public function scopeRelations($query)
    {
        return $query;
    }

    public function scopeAddPublicResources($query)
    {
        if ($this instanceof iPublicable) {
            $query = $query->orWhere('public', true);
        }

        return $query;
    }

    /**
     * A generic scope query to retrieve one model
     * @param $query
     * @return mixed
     */
    public function scopeRetrieve($query)
    {
        return $query->relations()->find($this->getKey());
    }

    /**
     * A generic scope query to retrieve all the models
     * @param $query
     * @return mixed
     */
    public function scopeRetrieveAll($query)
    {
        return $query->get();
    }

    /**
     * Overload of the save function
     * This function handles the generic saving / updating of names
     * for the models that are Translatable
     * It also sets the children's attributes in the array $related.
     * Those attributes can be retrieved with the function getRelatedAttributes().
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        $event = 'create';

        foreach ($this->children as $key => $child) {
            $attributes = $this->attributes[$child] ?? [];

            $this->children[$child] = $attributes;
            unset($this->children[$key]);
            unset($this->attributes[$child]);
        }

        if ($this->getKey()) {
            $event = 'update';
        }

        if (parent::save($options)) {
            if ($event === 'create') {
                $this->createdEvent();
            } else {
                $this->updatedEvent();
            }
            return true;
        }

        return false;
    }

    /**
     * Overload of the update function
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    /**
     * Event fired when a model was created
     * @return bool
     */
    protected function createdEvent(): bool
    {
    }

    /**
     * Event fired when a model was updated
     * @return bool
     */
    protected function updatedEvent(): bool
    {
    }

    /**
     * A generic function to call an event on create with
     * the query parameters
     * @param array $params
     * @param User $user
     * @return bool
     */
    public function creatingEvent(User $user, array $params = []): bool
    {
    }

    /**
     * A generic function to call an event on update with
     * the query parameters
     * @param array $params
     * @param User $user
     * @return bool
     */
    public function updatingEvent(User $user, array $params = []): bool
    {
    }

    /**
     * Get the model's children attributes
     * @param $child
     * @return array
     */
    protected function getChildren($child): array
    {
        return (isset($this->children[$child])) ? $this->children[$child] : [];
    }

    public function getMassFillable(): array
    {
        return $this->mass_fillable;
    }

    public function getExportable(): array
    {
        return $this->exportable;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @note Seems to not be used anywhere
     * A generic function called after a model was sync() / detach() with another model.
     * The name of the model that was synced is given as first parameter.
     *
     * @param $synced_model
     * @return bool
     */
    public function synced($synced_model): bool
    {
    }

    /**
     * @note Seems to not be used anywhere

     * @param $changes
     * @param $id
     * @return void
     */
    public function afterSync($changes, $id)
    {
    }
}
