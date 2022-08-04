<?php

namespace App\Http\Role\Permissions;

use App\Exceptions\SmiceException;
use App\Http\User\Models\User;
use App\Interfaces\iProtected;
use App\Interfaces\iPublicable;
use App\Models\SmiceModel;
use App\Models\Society;

/**
 * This class contains all the code useful to check to permissions on Smice.
 *
 * They are 2 modes available to check the permissions :
 *  - check against a given resource (model + id)
 *  - check against a given model (model only)
 *
 * The model can be set from two ways :
 *  - by passing a SmiceModel instance
 *  - by given the name of a "Module" (load the module in DB then instantiate the model)
 *
 * The permissions exists in two ways :
 *  - static
 *      - 0 : OFF
 *      - 1 : ON
 *  - non-static
 *      - 0 : OFF
 *      - 1 : SELF
 *      - 2 : SOCIETY
 *      - 3 : ADMIN
 *
 * === IMPORTANT ===
 * After validating an action for a given model, this class will generate a "safe" query
 * that can be used to list / update / delete resources.
 *
 * Class PermissionChecker
 * @package App\Classes
 */
class PermissionChecker
{
    /**
     * The model to be checked.
     * @var null|SmiceModel
     */
    private $model = null;

    /**
     * A query to fetch a model with the permissions
     * applied on it.
     * @var null
     */
    private $query = null;

    /**
     * The action to be checked
     * @var null|string
     */
    private $action = null;

    /**
     * The user who's rights are to be checked
     * @var null|User
     */
    private $user = null;

    /**
     * The permissions of the user
     * @var array|mixed
     */
    private $permissions = [];

    /**
     * Whether or not the permissions should be forced
     * - true : allow everything, no safe query or model permission are created.
     * @var bool
     */
    private $force_permission = false;

    /**
     * The user's actions on a model. This variable is
     * available after the permissions where checked.
     * @var array
     */
    private $model_permissions = [];

    private function __construct(User $user)
    {
        $this->setUser($user);
    }

    public static function getInstance(User $user = null)
    {
        static $instance = null;

        if ($instance == null) {
            if (!$user) {
                throw new SmiceException(
                    SmiceException::HTTP_INTERNAL_SERVER_ERROR,
                    SmiceException::E_SERVER,
                    'An user is required to instantiate the PermissionChecker.'
                );
            }
            $instance = new self($user);
        }

        return $instance;
    }

    public function setUser(User $user)
    {
        $this->user = clone $user;
        $this->permissions = $user->userPermission->advanced_permissions;

        $this->user->society->loadChildrenId();
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getSafeQuery()
    {
        return $this->query;
    }

    public function getModelPermissions()
    {
        return $this->model_permissions;
    }

    public function setAction($action)
    {
        if ($action !== null) {
            $this->_loadAction($action);
        }
    }

    public function setModel($model)
    {
        $module = null;

        if ($model instanceof SmiceModel && !($model instanceof iProtected)) {
            $this->model = clone $model;
            $this->force_permission = true;
        } else {
            if ($model instanceof SmiceModel && $model instanceof iProtected) {
                $module = $model->getModuleName();
            } elseif (is_string($model)) {
                $module = $model;
            }

            if (!($class_name = $this->_loadModule($module))) {
                throw new SmiceException(
                    SmiceException::HTTP_INTERNAL_SERVER_ERROR,
                    SmiceException::E_SERVER,
                    'PermissionChecker: the module [' . $module . '] does not exists.'
                );
            }

            $this->model = (is_string($model)) ? new $class_name : clone $model;
        }
    }

    private function _loadModule($module)
    {
        return Permissions::moduleExists($module);
    }

    private function _loadAction($action)
    {
        if (Action::actionExists($action)) {
            $this->action = $action;
        } else {
            throw new SmiceException(
                SmiceException::HTTP_INTERNAL_SERVER_ERROR,
                SmiceException::E_SERVER,
                'PermissionChecker: the action [' . $action . '] does not exists.'
            );
        }
    }

    private function _getPermission()
    {
        if (isset($this->permissions[$this->model->getModuleName()][$this->action])) {
            return $this->permissions[$this->model->getModuleName()][$this->action];
        }

        return false;
    }

    /***
     * Define if the model is SELF readable
     *
     * @return bool
     */
    private function _modelIsSelfReadable()
    {
        $columns = array_fill_keys($this->model->getFillable(), true);

        if (isset($columns['created_by'])) {
            if ($this->model->getKey() && ($this->model->created_by === $this->user->getKey())) {
                return true;
            } elseif (!$this->model->getKey()) {
                return true;
            }
        } elseif (isset($columns['user_id'])) {
            if ($this->model->getKey() && ($this->model->user_id === $this->user->getKey())) {
                return true;
            } elseif (!$this->model->getKey()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Define if the model is ADMIN or SOCIETY readable
     *
     * @param array $societies
     * @return bool
     */
    private function _modelIsSocietyReadable(array $societies = [])
    {
        $society_id = [];
        if (method_exists($this->model, 'society') || method_exists($this->model, 'societies') || method_exists($this->model, 'wave')) {
            if ($this->model->getKey()) {
                if (method_exists($this->model, 'societies'))
                    $society_id = array_merge($society_id, $this->model->societies->modelKeys());
                if (method_exists($this->model, 'society') && $this->model->society)
                    array_push($society_id, $this->model->society->getKey());
                if (method_exists($this->model, 'wave') && $this->model->wave) {
                    array_push($society_id, $this->model->wave->society_id);
                }
                // A little trick to get the permission working on the societies
                if ($this->model instanceof Society) {
                    array_push($society_id, $this->model->getKey());
                }

                return !empty(array_intersect(array_unique($society_id), $societies));
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * The model to check is loaded, it has an ID, it's an unique resource.
     * First we will check if it's a public resource.
     * Then if it's the society of the user, or the profile of the user.
     * And then if the user has the rights in SELF / SOCIETY / ADMIN
     *
     * @return bool|int
     */
    private function _hasPermissionOnLoadedModel()
    {
        $permission = false;
        $mode = $this->_getPermission();

        if ($this->model instanceof User && ($this->model->getKey() === $this->user->getKey())) {
            $permission = 2;
        }

        if ($mode === PermissionMode::ADMIN) {
            $permission = 1;
        } elseif ($mode === PermissionMode::SOCIETY && $this->_modelIsSocietyReadable([$this->user->society->getKey()])) {
            $permission = 1;
        } elseif ($mode === PermissionMode::SELF && $this->_modelIsSelfReadable()) {
            $permission = 1;
        } elseif ($this->model instanceof Society && ($this->model->getKey() === $this->user->society->getKey())) {
            $permission = 3;
        } elseif ($this->action === Action::READ && $this->model instanceof iPublicable && $this->model->public === true) {
            $permission = 3;
        }

        return ($permission);
    }

    /**
     * This function is called whenever an user has the permissions on a loaded resource.
     * It will verify if the resource is public or if it's the society of the user or if it's
     * the user consulting himself.
     * If so, it will add the necessary permissions.
     * After that, the whole permissions are transformed into boolean values to simplify the front-end display.
     *
     * See the function _hasPermissionOnLoadedModel() to understand the meanings of the numbers.
     *
     * @param $permission
     */
    private function _loadModelPermissions($permission)
    {
        if ($permission === 1) {
            $this->model_permissions = $this->permissions[$this->model->getModuleName()];
            array_walk($this->model_permissions, function (&$value) {
                $value = ($value != 0) ? true : false;
            });
        } elseif ($permission === 2) {
            $this->model_permissions[Action::READ] = PermissionMode::ON;
            $this->model_permissions[Action::MODIFY] = PermissionMode::ON;
            $this->model_permissions[Action::DELETE] = PermissionMode::ON;
        } elseif ($permission === 3) {
            $this->model_permissions[Action::READ] = PermissionMode::ON;
            $this->model_permissions[Action::MODIFY] = PermissionMode::OFF;
            $this->model_permissions[Action::DELETE] = PermissionMode::OFF;
        }
    }

    /**
     * The model is not loaded. We will check if the user has the permissions demanded,
     * if so, we will return a value corresponding to which safe query must be build.
     *
     * @return bool|int
     */
    private function _hasPermissionOnEmptyModel()
    {
        $permission = false;
        $mode = $this->_getPermission();

        if ($this->model instanceof User && ($this->model->getKey() === $this->user->getKey())) {
            $permission = 2;
        }

        if ($mode === PermissionMode::ADMIN && $this->_modelIsSocietyReadable()) {
            $permission = 1;
        } elseif ($mode === PermissionMode::SOCIETY && $this->_modelIsSocietyReadable()) {
            $permission = 2;
        } elseif ($mode === PermissionMode::SELF && $this->_modelIsSelfReadable()) {
            $permission = 3;
        }

        return $permission;
    }

    /**
     * Create a safe query to retrieve only the resources the user is allowed to read.
     * If the user has an ADMIN or SOCIETY permission, we add a whereIn() condition
     * with the ids of the societies.
     * If the user has a permission SELF, we had the correct condition.
     *
     * Notice that the safe query is based on a ListQuery of the targeted model.
     * See the $list_rows in your model to define the listable attributes.
     *
     * @param $permission
     */
    private function _createSafeQuery($permission)
    {
        $this->query = $this->model->newListQuery();

        $columns = array_fill_keys($this->model->getFillable(), true);

        /* way to allow all societies to read all shops */
        if (/*$this->model->getModuleName() !== 'shops' && (*/
            $permission === 1 || $permission === 2/*)*/) {
                $societies = $this->user->society->getChildrenId();

            $this->query = $this->query->where(function ($query) use ($societies) {
                if (method_exists($this->model, 'society')) {
                    $query->whereHas('society', function ($query) use ($societies) {
                        $query->whereIn('society.id', $societies);
                    });
                }
                if (method_exists($this->model, 'societies')) {
                    $query->orWhereHas('societies', function ($query) use ($societies) {
                        $query->whereIn('society.id', $societies);
                    });
                }
                if (method_exists($this->model, 'wave')) {
                    $query->orWhereHas('wave', function ($query) use ($societies) {
                        $query->whereIn('wave.society_id', $societies);
                    });
                }
                // A little trick to get the current society in the list
                if ($this->model instanceof Society)
                    $query->orWhere('id', $this->user->society->getKey());
            });
        } elseif ($permission === 3) {
            if (isset($columns['created_by'])) {
                $this->query->where('created_by', $this->user->getKey());
            } else {
                $this->query->where('user_id', $this->user->getKey());
            }
        }
    }

    /**
     * Check the permissions on the model.
     *
     * @return bool
     */
    private function _checkPermissions()
    {
        if ($this->model->getKey()) {
            if (($permission = $this->_hasPermissionOnLoadedModel()) > false) {
                $this->_loadModelPermissions($permission);
                return true;
            } else {
                return false;
            }
        } else {
            if (($permission = $this->_hasPermissionOnEmptyModel()) > false) {
                $this->_createSafeQuery($permission);
                return true;
            } else {
                return false;
            }
        }
    }

    public function hasPermission()
    {
        if (!$this->model || !$this->action) {
            throw new SmiceException(
                SmiceException::HTTP_INTERNAL_SERVER_ERROR,
                SmiceException::E_SERVER,
                'The permission checker requires an action and a model.'
            );
        }

        if ($this->force_permission) {
            if (!$this->model->getKey()) {
                $this->query = $this->model->newListQuery();
            }
            return true;
        } elseif (Action::isStatic($this->action)) {
            $staticAction = $this->_getPermission();
            $this->setAction(Action::READ);
            $checkPermissions = $this->_checkPermissions();

            return ($staticAction & $checkPermissions);
        } else {
            return $this->_checkPermissions();
        }
    }
}
