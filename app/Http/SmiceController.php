<?php

namespace App\Http;

use App\Hooks\Hook;
use App\Http\User\Models\User;
use App\Models\Society;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SmiceController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * The user making the request
     * @var null|User
     */
    protected ?User $user = null;

    /**
     * The society on which the user is making a request
     * @var null|Society
     */
    protected ?Society $society = null;

    /**
     * The model being touched by the request
     * @var mixed
     */
    protected mixed $model = null;

    /**
     * The request parameters
     * @var null|array
     */
    protected ?array $params = null;

    /**
     * The user agent information
     */
    protected string|array|null $useragent = null;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->user = $request->user;
        $this->society = $request->society;
        $this->model = $request->model;
        $this->useragent = $request->server('HTTP_USER_AGENT');
        $this->params = $request->all();

        // Initialize the AlertHook class
        if ($this->society) {
            Hook::init($this->society->getKey());
        }
    }
}
