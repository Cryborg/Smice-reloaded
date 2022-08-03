<?php

namespace App\Http\User\Controllers;

use App\Classes\Auth\CreateJWTToken;
use App\Classes\AvatarService;
use App\Classes\ExcelValueBinder;
use App\Classes\Helpers\ArrayHelper;
use App\Classes\Helpers\GeoHelper;
use App\Classes\Services\UserService;
use App\Classes\SmiceClasses\SmiceFinder;
use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Exceptions\SmiceException;
use App\Http\Skill\Models\Skill;
use App\Http\Skill\Requests\AddSkillsRequest;
use App\Http\Skill\Resources\SkillResourceCollection;
use App\Http\SmiceController;
use App\Http\User\Models\User;
use App\Http\User\Models\UserLevel;
use App\Http\User\Requests\UserListRequest;
use App\Http\User\Resources\UserResourceCollection;
use App\Jobs\UserMessageJob;
use App\Models\Alias;
use App\Models\Answer;
use App\Models\Group;
use App\Models\Role;
use App\Models\Shop;
use App\Models\Society;
use App\Models\Survey;
use App\Models\WaveTarget;
use App\Models\WaveUser;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends SmiceController
{
    private UserService $userService;

    /**
     * UserController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService($this->user, $this->model, $request);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function showSurvey(Request $request): Response
    {
        $user_id = ($request->route('id_me') == 'me')
            ? $this->user->getKey()
            : $request->route('id_me');

        $survey = Survey::where([
            'user_survey' => true,
            'society_id' => $this->model->society->getKey()
        ])->first();

        if ($survey) {
            $survey = $survey->retrieve();
            $answers = Answer::with('images')
                ->where('user_id', $user_id)
                ->where('survey_id', $survey->getKey())
                ->get()
                ->toArray();
            return new Response([
                Survey::getName() => $survey,
                'answers' => $answers,
            ]);
        } else {
            return new Response();
        }
    }

    /**
     * @throws SmiceException
     */
    public function getToken(Request $request): Response
    {
        $user_id = $request->route('user_id', null);
        if ($this->user->isadmin === false) {
            throw new SmiceException(
                SmiceException::HTTP_FORBIDDEN,
                SmiceException::E_RESOURCE,
                'You are not allowed to use this action'
            );
        }
        if (is_null($user_id)) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_ACCEPTABLE,
                SmiceException::E_RESOURCE,
                'Parameter is missing'
            );
        }

        $user = User::find($user_id);
        $user->reloadPermissions();

        $token = (new CreateJWTToken($user))->handle();

        return new Response(['token' => $token]);
    }

    /**
     * @param UserListRequest $request
     *
     * @return UserResourceCollection
     */
    public function list(UserListRequest $request): UserResourceCollection
    {
        $groups = json_decode($request->input('filter.groups'));
        $users = json_decode($request->input('filter.ids'));

        $userBuilder = QueryBuilder::for(User::class);

        if ($groups) {
            $userBuilder->whereHas(
                'groups',
                function (Builder $builder) use ($groups) {
                    $builder->whereIn('group_id', $groups);
                }
            );
        }

        if ($users) {
            $userBuilder->whereIn('id', $users);
        }

        return new UserResourceCollection(
            $userBuilder->paginate(10)
        );
    }

    /**
     * @return Response
     */
    public function listUsersParent(): Response
    {
        //Get all users with user in parent society, add exclusion for smiceur
        $users = (new User())->newListQuery();
        //get parent :
        $o = $this->user->current_society_id;
        $parent = Society::where('id', $this->user->current_society_id)->first()->toarray();
        if (($parent['society_id'] > 1) && ($this->user->society_id === 1)) {
            $ids = [$parent['society_id']];
            $ids[] = $this->user->current_society_id;
            $users->wherein('society_id', $ids);
        } else {
            $users->where('society_id', $this->user->current_society_id);
        }


        $response = (new SmiceFinder($users, $this->params, $this->user))->get();

        return new Response($response);
    }

    /**
     * @return UserResourceCollection
     */
    public function getSmicers(): UserResourceCollection
    {
        $users = QueryBuilder::for(User::class);

        if ($this->user->society_id <> 1) {
            $users->where('society_id', $this->user->society_id);
        }

        return new UserResourceCollection(
            $users->paginate(10)
        );
    }

    public function getReaders(): UserResourceCollection
    {
        return new UserResourceCollection(
            QueryBuilder::for(User::class)
                ->where('isadmin', true)
                ->paginate(10)
        );
    }

    /**
     */
    public function addSkills(AddSkillsRequest $request, User $user): Response
    {
        $skills = $request->input('skills', []);

        $user->skills()->sync($skills);

        return new Response(['data' => 'OK']);
    }

    public function getskills(Request $request, User $user)
    {
        return new SkillResourceCollection(
            $user->skills
        );
    }

    /**
     * @return Response
     */
    public function getSupervisors()
    {
        $user = new User();
        $user = $user->newListQuery();
        $user->where('review_access', true);

        $response = (new SmiceFinder($user, $this->params, $this->user))->get();

        return new Response($response);
    }

    /**
     * @param Request $request
     * @param AvatarService $avatar
     * @throws SmiceException
     */
    public function importUsers(Request $request, AvatarService $avatar)
    {
        $csv = $request->file('document');

        Validator::make(
            [
                'file' => $csv
            ],
            [
                'file' => 'required'
            ]
        )->passOrDie();

        $ext = $csv->getClientOriginalExtension();

        Validator::make(
            [
                'extension' => $ext
            ],
            [
                'extension' => 'in:xls,csv,xlsx'
            ]
        )->passOrDie();
        $valueCasting = new ExcelValueBinder;
        $excel = Excel::setValueBinder($valueCasting)->load($csv);
        $excel->each(function ($items) use ($avatar) {
            $errors = [];
            if ($items->getTitle() == 'users') {
                foreach ($items as $item) {
                    $email = strtolower($item->email);
                    $findUser = User::whereEmail($email)->first();
                    $user = $findUser ? $findUser : new User();
                    if (isset($item->id) && $item->id !== 0 && !is_null($item->id)) {
                        $user->id = $item->id;
                        if (!$findUser) {
                            throw new SmiceException(
                                500,
                                SmiceException::E_RESOURCE,
                                'Unable to change mail address of existing user'
                            );
                        }
                    }
                    $user->first_name = $item->first_name;
                    $user->last_name = $item->last_name;
                    $user->gender = ($item->gender == 'f') ? User::GENDER_FEMALE : User::GENDER_MALE;
                    $user->birth_date = $item->birth_date;
                    $user->street = $item->street;
                    if ($item->password) {
                        $user->password = $item->password;
                    }
                    $user->postal_code = (string)$item->postal_code;
                    $user->city = $item->city;
                    $user->email = strtolower($item->email);
                    $user->picture = $avatar->getImageUrl();
                    if ($user->street && (!$user->lat || !$user->lon)) { // check avec les autres
                        $coor = GeoHelper::getLatLonByStreet($user->street, $user->city);
                        $user->lat = array_get($coor, 'lat', 0);
                        $user->lon = array_get($coor, 'lon', 0);
                    }
                    $user->phone = (string)$item->phone;
                    $user->registration_date = $item->registration_date;
                    $user->last_mission_date = $item->last_mission_date;
                    $user->send_report_exclusion = $item->send_report_exclusion == 'true' ? true : false;
                    $user->scoring = $item->scoring;
                    $user->sleepstatus = $item->sleepstatus == 'true' ? true : false;
                    $user->candidate_process = $item->candidate_process;
                    $user->society_id = $this->user->currentSociety->getKey();
                    $user->language()->associate($this->user->language->getKey());
                    if ($item->first_name && $item->first_name) {
                        try {
                            $avatar->create($item->first_name, $item->last_name);
                        } catch (\Exception $ex) {
                            $errors[] = $ex->getMessage();
                            continue;
                        }
                    }
                    $user->save();

                    //link shop to user
                    DB::table('user_shop')->where('user_id', $user->id)->delete();
                    $shop_id = $item->user_shops;
                    if ($shop_id && $shop_id !== '' && $user->id > 0) {
                        if (!is_string($shop_id)) {
                            $shop_id = (string)$shop_id;
                        }
                        if (strstr($shop_id, '.')) {
                            $shop_id = explode('.', $shop_id);
                        } else {
                            if (strstr($shop_id, ',')) {
                                $shop_id = explode(',', $shop_id);
                            } else {
                                if (strstr($shop_id, ';')) {
                                    $shop_id = explode(';', $shop_id);
                                }
                            }
                        }
                        if (!is_array($shop_id)) {
                            $shop_id = [$shop_id];
                        }

                        foreach ($shop_id as $id) {
                            if ($id > 0) {
                                DB::table('user_shop')->where('shop_id', $id)->where('user_id', $user->id)->delete();
                                DB::table('user_shop')->insert(['shop_id' => $id, 'user_id' => $user->id]);
                            }
                        }
                    }

                    //link to group
                    DB::table('group_user')->where('user_id', $user->id)->delete();
                    $groups = $item->user_groups;
                    if ($groups && $groups !== '') {
                        if (!is_string($groups)) {
                            $groups = (string)$groups;
                        }
                        $groupIds = [];
                        if (strstr($groups, '.')) {
                            $groupIds = explode('.', $groups);
                        } else {
                            if (strstr($groups, ',')) {
                                $groupIds = explode(',', $groups);
                            } else {
                                if (strstr($groups, ';')) {
                                    $groupIds = explode(';', $groups);
                                } else {
                                    $groupIds = [$groups];
                                }
                            }
                        }

                        $groupIds = array_filter($groupIds);

                        if (!empty($groupIds)) {
                            $user->groups()->attach($groupIds);
                        }
                    }

                    //link to skill
                    $skills = $item->user_skills;
                    if ($skills && $skills !== '') {
                        if (!is_string($skills)) {
                            $skills = (string)$skills;
                        }
                        $skillIds = [];
                        if (strstr($skills, '.')) {
                            $skillIds = explode('.', $skills);
                        } else {
                            if (strstr($skills, ',')) {
                                $skillIds = explode(',', $skills);
                            } else {
                                if (strstr($skills, ';')) {
                                    $skillIds = explode(';', $skills);
                                } else {
                                    $skillIds = [$skills];
                                }
                            }
                        }

                        if (!empty($skillIds)) {
                            DB::table('user_skill')->where('user_id', $user->id)->delete();
                            $user->skills()->attach($skillIds);
                        }
                    }

                    //link to role
                    $roles = $item->user_roles;
                    if ($roles && $roles !== '') {
                        if (!is_string($roles)) {
                            $roles = (string)$roles;
                        }

                        if (strstr($roles, '.')) {
                            $roleIds = explode('.', $roles);
                        } else {
                            if (strstr($roles, ',')) {
                                $roleIds = explode(',', $roles);
                            } else {
                                if (strstr($roles, ';')) {
                                    $roleIds = explode(';', $roles);
                                } else {
                                    $roleIds = [$roles];
                                }
                            }
                        }
                        if (!empty($roleIds)) {
                            DB::table('role_user')->where('user_id', $user->id)->delete();
                            $user->roles()->attach($roleIds);
                        }
                    }

                    //update user_permission
                    $UserPermission = UserPermission::where('user_id', $user->id)->first();
                    $UserPermission->review_access = (bool)$item->review_access;
                    $UserPermission->download_passage_proof = (bool)$item->download_passage_proof;
                    $UserPermission->save();
                }
            }

            if (!empty($errors)) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VALIDATION,
                    implode('<br/>', $errors)
                );
            }
        });
    }

    public function exportUsers()
    {
        $row = ['password', 'role'];

        $users = User::with('userPermission')->where('society_id', $this->user->currentSociety->getKey());
        $users = $this->usersFilter($users);
        $users = $users->get([
            'id',
            'first_name',
            'last_name',
            'gender',
            'birth_date',
            'street',
            'postal_code',
            'city',
            'email',
            'lat',
            'lon',
            'phone',
            'registration_date',
            'last_mission_date',
            'scoring',
            'sleepstatus',
            'sleep_date',
            'sleep_by',
            'sleep_reason',
            'candidate_process',
            'commentuser',
            'send_report_exclusion'
        ]);

        collect($users)->map(function ($user) {
            $user['user_level'] = ($user->userActivity)
                ? ($user->userActivity->userLevel->status[$this->user->language->code] . ' ' . $user->scoring / 20)
                : '';
            unset($user->userActivity);
            $user['review_access'] = $user->userPermission->review_access;
            $user['send_report_exclusion'] = $user->send_report_exclusion;
            $user['download_passage_proof'] = $user->userPermission->download_passage_proof;
            unset($user->userPermission);
            $user['user_shops'] = implode(',', $user->shops()->lists('id')->toArray());
            $user['user_groups'] = implode(',', $user->groups()->lists('id')->toArray());
            $user['user_roles'] = implode(',', $user->roles()->lists('id')->toArray());
            $user['user_skills'] = implode(',', $user->skills()->lists('id')->toArray());
        });

        $roles = Role::where('society_id', $this->user->currentSociety->getKey())->get(['id', 'name']);
        $lan = $this->user->language->code;
        $users_groups = Group::selectRaw('id, json_extract_path_text(name::json,\'' . $lan . '\') as name')->where(
            'society_id',
            $this->user->currentSociety->getKey()
        )->get(['id', 'name']);
        $skills = Skill::selectRaw('id, json_extract_path_text(name::json,\'' . $lan . '\') as name')->where(
            'society_id',
            $this->user->currentSociety->getKey()
        )->get(['id', 'name']);
        Excel::create('users', function ($excel) use ($users, $row, $roles, $skills, $users_groups) {
            $excel->setTitle('Exportation des users');
            $excel->sheet('password', function ($sheet) use ($row) {
                $sheet->fromModel($row, null, '', true);
            });
            $excel->sheet('users', function ($sheet) use ($users) {
                $sheet->fromModel($users, null, '', true);
            });
            $excel->sheet('roles', function ($sheet) use ($roles) {
                $sheet->fromModel($roles, null, '', true);
            });
            $excel->sheet('skills', function ($sheet) use ($skills) {
                $sheet->fromModel($skills, null, '', true);
            });
            $excel->sheet('groups', function ($sheet) use ($users_groups) {
                $sheet->fromModel($users_groups, null, '', true);
            });
        })->download('xls');
    }

    /**
     * @return Response
     */
    public function usersByRole()
    {
        $role_ids = array_get($this->params, 'roles', []);
        Validator::make([
            'roles' => $role_ids
        ], [
            'roles' => 'array'
        ])->passOrDie();

        $users = new User();
        $users = $users->newListQuery();
        $users = $users->with('roles')->whereHas('roles', function ($q) use ($role_ids) {
            if (count($role_ids)) {
                $q->whereIn('id', $role_ids);
            }
        });

        $response = (new SmiceFinder($users, $this->params, $this->user))->get();

        return new Response($response);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function showAllMissions(Request $request)
    {
        $results = $this->userService->showUserMissions($request, [4, 5, 6, 7, 8, 9, 11, 12, 13]);

        return new Response($results);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function showAvailableMissions(Request $request)
    {
        $results = $this->userService->showUserMissions($request, [2, 3, 5, 13], [
            'order' => [
                'status',
                'date_status ASC'
            ],
            'limit' => 'date',
            'type' => 'offer'
        ]);

        return new Response($results);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function showGoingMissions(Request $request)
    {
        $results = $this->userService->showUserMissions($request, [4, 6, 8, 11, 13], [
            'type' => 'todo'
        ]);

        return new Response($results);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function showDoneMissions(Request $request)
    {
        $results = $this->userService->showUserMissions($request, [7, 14]);

        return new Response($results);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function waveUserScore(Request $request)
    {
        $id = $request->route('wave_target_id');
        $wave_target = WaveTarget::find($id);

        $wave_user = WaveUser::where('wave_target_id', $id)->where('user_id', $wave_target['user_id'])->first();

        return new Response($wave_user);
    }

    /**
     * @return Response
     */
    public function profileProgress()
    {
        $progress = round(($this->userService->mainInfoProgress() + $this->userService->surveyProgress()), 0);

        return new Response($progress);
    }

    /**
     * @return Response
     */
    public function showGains() //show all gains not again asked by user
    {
        $refund = $compensation = $salary = 0;

        $payments = $this->user->gains->where('payment_id', null);
        $voucher = $this->user->gains->where('voucher_id', null);
        $payslip = $this->user->gains->where('payslip_id', null);

        foreach ($payments as $value) {
            $refund += $value->amount;
        }

        foreach ($voucher as $value) {
            $compensation += $value->compensation;
        }

        foreach ($payslip as $value) {
            $salary += $value->salary;
        }

        $dt = Carbon::now();

        if (Carbon::now()->format('d') > 27) {
            $dt->month = Carbon::now()->format('m') + 2;
        } else {
            $dt->month = Carbon::now()->format('m') + 1;
        }
        $dt->day = 5;
        $response = [
            'refund' => round($refund, 2),
            'compensation' => round($compensation, 2),
            'salary' => round($salary, 2),
            'date' => $dt->toDateTimeString(),
            'date_txt' => $dt->format('d/m/Y')
        ];

        return new Response($response);
    }

    /**
     * @return Response
     */
    public function showWinnings()
    {
        $fakerFactory = new Factory();
        $faker = $fakerFactory->create();
        $gains = [];
        $gains['user'] = [];
        $gains['user']['titulaire'] = $faker->name;
        $gains['user']['adresse'] = $faker->address;
        $gains['user']['iban'] = $faker->text(32);
        $gains['user']['bic'] = $faker->swiftBicNumber;
        $gains['user']['paypal'] = $faker->email;
        $gains['user']['total'] = 0;
        $gains['user']['gains'] = [];

        for ($i = 1; $i <= 10; $i++) {
            $gain = [];

            $gain['id'] = $i;
            $gain['mission'] = $faker->word;
            $gain['date'] = $faker->date('Y-m-d');
            $gain['gain'] = $faker->numberBetween(1, 500);

            array_push($gains['user']['gains'], $gain);
            $gains['user']['total'] += $gain['gain'];
        }

        return new Response($gains);
    }

    /**
     * @return Response
     */
    public function saveAnswers()
    {
        $answers = array_get($this->params, 'answers');
        Validator::make(['answers' => $answers], ['answers' => 'array_array'])->passOrDie();
        $count = [];

        //        dd($answers);

        foreach ($answers as $answer) {
            $count[$answer['question_id']] = $this->userService->saveAnswer($answer, $count);
        }

        return new Response();
    }

    /**
     * @return Response
     */
    public function saveShops()
    {
        $shopIds = array_get($this->params, 'shops');
        $userIds = array_get($this->params, 'users');
        # check if user is link to current company
        $userIds = User::where('society_id', $this->user->currentSociety->getKey())
            ->wherein('id', $userIds)->lists('id')->toArray();
        foreach ($userIds as $userId) {
            foreach ($shopIds as $shopId) {
                Shop::find($shopId)->users()->detach($userId);
                Shop::find($shopId)->users()->attach($userId);
            }
        }

        return new Response();
    }

    /**
     * @return Response
     */
    public function saveShopsAll()
    {
        $userIds = array_get($this->params, 'users');
        # check if user is link to current company
        $userIds = User::wherein('society_id', $this->user->society->getChildrenId())
            ->wherein('id', $userIds)->lists('id')->toArray();
        $shopIds = DB::table('shop_society')->whereIn('society_id', $this->user->society->getChildrenId())
            ->lists('shop_id');
        foreach ($userIds as $userId) {
            foreach ($shopIds as $shopId) {
                Shop::find($shopId)->users()->detach($userId);
                Shop::find($shopId)->users()->attach($userId);
            }
        }

        return new Response();
    }

    /**
     * @return Response
     */
    public function saveShopsAxes()
    {
        $userIds = array_get($this->params, 'users');
        # check if user is link to current company
        $userIds = User::where('society_id', $this->user->currentSociety->getKey())
            ->wherein('id', $userIds)
            ->lists('id')
            ->toArray();
        $axeIds = array_get($this->params, 'axes');

        $shops_id = DB::table('shop_society')->where('society_id', $this->user->currentSociety->getKey())
            ->select('shop_id');
        $sid = [];
        foreach ($shops_id->get() as $id) {
            $sid[] = $id['shop_id'];
        }

        $shopIds = Shop::whereIn('id', $sid)->whereHas('axes', function ($query) use ($axeIds) {
            $query->whereIn('id', $axeIds);
        })->lists('id')->toArray();

        foreach ($userIds as $userId) {
            foreach ($shopIds as $shopId) {
                Shop::find($shopId)->users()->detach($userId);
                Shop::find($shopId)->users()->attach($userId);
            }
        }

        return new Response();
    }

    /**
     * @return Response
     */
    public function resendActivationEmail()
    {
        if (!$this->model->email_verified) {
            SmiceMailSystem::send(SmiceMailSystem::ACTIVATE_ACCOUNT, function ($message) {
                $message->to([$this->model->getKey()]);
                $message->subject('Smice - Activate your account');
                $message->addGlobalMergeVars([
                    'name' => 'activate_token',
                    'content' => Crypt::encrypt($this->model->email)
                ]);
            }, $this->model->language->code);

            return new Response(
                ['notice' => 'We have sent you an email to activate your account.'],
                Response::HTTP_OK
            );
        } else {
            return new Response(
                ['notice' => 'Your account has already been verified.'],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws SmiceException
     */
    public function activateEmail(Request $request)
    {
        try {
            $email = Crypt::decrypt($request->route('token'));
        } catch (DecryptException $e) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE
            );
        }
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE
            );
        }
        $user->email_verified = true;
        $user->update();

        return new Response(null, Response::HTTP_OK);
    }

    /**
     * @return Response
     */
    public function getUsersOfSociety()
    {
        $this->params['minimum'] = true;
        $users = User::where('society_id', $this->user->society_id)->orderBy('name');
        $response = (new SmiceFinder($users, $this->params, $this->user))->get();

        return new Response($response);
    }

    /**
     * @return Response
     */
    public function reactivate()
    {
        $email = array_get($this->params, 'email');
        Validator::make(
            ['email' => $email],
            ['email' => 'email|required']
        )->passOrDie();
        $user = User::withTrashed()->where('email', $email)->first();
        if (!is_null($user)) {
            $user->restore();
        }
        return new Response(['user' => $user]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws SmiceException
     */
    public function userReset(Request $request)
    {
        $user_id = $request->route('id');
        $password = array_get($this->params, 'password');
        $password2 = array_get($this->params, 'password_confirmation');
        $user = User::find($user_id);

        if (!$user) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE,
                'Invalid token'
            );
        }
        /*
         * Everything is ok, the email matches, the token is still valid,
         * we can save the new password for the user and delete all his password recover tokens.
         */
        $user->fill(['password' => $password, 'password_confirmation' => $password2]);
        $user->update();

        $notice = 'Your password has been changed.';

        return new Response(['notice' => $notice], Response::HTTP_OK);
    }

    /**
     * OfflineList : List all mission available to mark as offlinee,
     *
     * @return \App\Classes\SmiceClasses\SmiceClass
     */
    public function offlineList()
    {
        return $this->userService->offlineList($this->params);
    }


    /**
     * @param Request $request
     * @return \App\Classes\SmiceClasses\SmiceClass
     * @throws SmiceException
     */
    public function offlineMode(Request $request)
    {
        if (!$request->route('id')) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE,
                'id mandatory'
            );
        }

        return $this->userService->offlineMode($this->params, $request);
    }

    /**
     * @param Request $request
     * @return $this|\App\Classes\SmiceClasses\SmiceClass|\Illuminate\Database\Eloquent\Builder|static
     * @throws SmiceException
     */
    public function onlineMode(Request $request)
    {
        if (!$request->route('id')) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE,
                'id mandatory'
            );
        }

        return $this->userService->onlineMode($this->params, $request);
    }

    /**
     * Function that will select users by filter
     *
     * @return Response
     */
    public function userSelection()
    {
        return new Response($this->userService->userSelection($this->params));
    }

    /**
     * @return Response
     * @throws SmiceException
     */
    public function addToGroup()
    {
        $ids = array_get($this->params, 'ids');
        $group_id = array_get($this->params, 'group_id');

        if (!$ids || !$group_id) {
            throw new SmiceException(
                SmiceException::HTTP_UNPROCESSABLE_ENTITY,
                SmiceException::E_RESOURCE,
                'Parameter is missing'
            );
        }

        foreach ($ids as $user_id) {
            Group::find($group_id)->users()->detach($user_id);
            Group::find($group_id)->users()->attach($user_id);
        }

        return new Response();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getUserGroups(Request $request)
    {
        $user_id = intval($request->route('user_id'));

        $groups = DB::table('group_user')
            ->where('user_id', $user_id)
            ->join('group', 'group_user.group_id', '=', 'group.id')
            ->select('group.id', 'group.name')
            ->get();

        return new Response($groups);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws SmiceException
     */
    public function getUserShops(Request $request)
    {
        $user_id = intval($request->route('id'));

        if (!$user_id) {
            throw new SmiceException(
                SmiceException::HTTP_UNPROCESSABLE_ENTITY,
                SmiceException::E_RESOURCE,
                'Parameter is missing'
            );
        }

        $shops_id = DB::table('user_shop')
            ->select('shop_id')
            ->where('user_id', $user_id)
            ->get();

        $shops = DB::table('shop')
            ->wherein('id', $shops_id)
            ->get();

        return new Response(['data' => $shops]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws SmiceException
     */
    public function deleteUserShops(Request $request)
    {
        $ids = array_get($this->params, 'ids');
        $user_id = intval($request->route('user_id'));

        if (!$ids || !$user_id) {
            throw new SmiceException(
                SmiceException::HTTP_UNPROCESSABLE_ENTITY,
                SmiceException::E_RESOURCE,
                'Parameter is missing'
            );
        }

        DB::table('user_shop')
            ->where('user_id', $user_id)
            ->whereIn('shop_id', $ids)
            ->delete();

        return new Response();
    }

    /**
     * @return Response
     */
    public function deleteUserMissions()
    {
        $wave_target_ids = array_get($this->params, 'wave_target_ids');

        Validator::make(
            ['wave_target_ids' => $wave_target_ids],
            ['wave_target_ids' => 'array|required']
        )->passOrDie();

        WaveTarget::whereIn('id', $wave_target_ids)->delete();

        return new Response();
    }

    /**
     * @param Request $request
     * @return Response
     * @throws SmiceException
     */
    public function getAlias(Request $request)
    {
        $society_id = intval($request->route('id_society'));

        if (!$society_id) {
            throw new SmiceException(
                SmiceException::HTTP_UNPROCESSABLE_ENTITY,
                SmiceException::E_RESOURCE,
                'Parameter is missing'
            );
        }

        $alias = Alias::where('society_id', $society_id);

        $response = (new SmiceFinder($alias, $this->params, $this->user))->get();

        return new Response($response);
    }

    /**
     * @return Response
     * @throws SmiceException
     */
    public function alertUsers()
    {
        $group_ids = array_get($this->params, 'ids');

        if (!isset($group_ids)) {
            throw new SmiceException(
                SmiceException::HTTP_UNPROCESSABLE_ENTITY,
                SmiceException::E_RESOURCE,
                'Parameter is missing'
            );
        }

        Validator::make(
            [
                'ids' => $group_ids
            ],
            [
                'ids' => 'array'
            ]
        )->passOrDie();

        if (count($group_ids)) {
            $users_groups = DB::table('group_user')
                ->whereIn('group_id', $group_ids)
                ->select('user_id')
                ->get();

            $user = new User();
            $user = $user->newListQuery();
            $users = $user->where('society_id', $this->user->currentSociety->getKey())
                ->whereIn('id', $users_groups);
        } else {
            $user = new User();
            $user = $user->newListQuery();
            $users = $user->where('society_id', $this->user->currentSociety->getKey());
        }

        $response = (new SmiceFinder($users, $this->params, $this->user))->get();

        return new Response($response);
    }

    /**
     * @return Response
     */
    public function loginHistory()
    {
        $homeboard_permissions = $this->user->userPermission->homeboard_permissions;

        if ($homeboard_permissions['login_history_admin']) {
            $login_history = DB::table('show_login')->where('society_id', $this->user->currentSociety->getKey())
                ->orderBy('created_at', 'desc')->limit(10)->get();
        } else {
            $login_history = DB::table('show_login')->where('user_id', $this->user->id)
                ->orderBy('created_at', 'desc')->limit(10)->get();
        }

        return new Response($login_history);
    }

    /**
     * @return Response
     */
    public function answerSendHistory()
    {
        $answers_send = [];

        $login_history = DB::table('show_log_send_answer')
            ->where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($login_history as $key => $value) {
            $answer_images = DB::table('show_images')->where('wave_target_id', $value["id"])->limit(3)->get();
            $answers_send[$key]['first_name'] = $value['first_name'];
            $answers_send[$key]['last_name'] = $value['last_name'];
            $answers_send[$key]['picture'] = $value['picture'];
            $answers_send[$key]['created_at'] = $value['created_at'];
            $answers_send[$key]['uuid'] = $value['uuid'];
            $answers_send[$key]['shop_name'] = $value['shop_name'];
            $answers_send[$key]['wave_name'] = $value['wave_name'];
            $answers_send[$key]['answer_images'] = $answer_images;
        }

        return new Response($answers_send);
    }

    /**
     * @return Response
     */
    public function nbSmiceReleased()
    {
        $nb_smice_released = WaveTarget::where('user_id', $this->user->id)
            ->where('status', 'read')
            ->where('date_status', '>=', Carbon::now()->subMonth())
            ->count();

        return new Response($nb_smice_released);
    }

    /**
     * @return Response
     */
    public function globalScorePerShop()
    {
        return new Response($this->userService->globalScorePerShop());
    }

    /**
     * @return Response
     */
    public function globalScorePerSequence()
    {
        return new Response($this->userService->globalScoreSequence());
    }

    /**
     * @return Response
     */
    public function minGlobalScoreSequence()
    {
        return new Response($this->userService->globalScoreSequence('min'));
    }

    /**
     * @return Response
     */
    public function maxGlobalScoreSequence()
    {
        return new Response($this->userService->globalScoreSequence('max'));
    }

    /**
     *
     */
    public function updatePassword()
    {
        $user_logins = DB::table('user_login')->whereNotNull('google_user_id')->get();
        foreach ($user_logins as $user_login) {
            $user = User::where('id', $user_login['user_id'])->first();
            $user->password = $user_login->google_user_id;
            $user->save();
        }
    }

    public function sendMessage()
    {
        $message = array_get($this->params, 'message');
        $type = array_get($this->params, 'type');
        $filters = array_get($this->params, 'filters');
        $subject = array_get($this->params, 'subject');

        $users = $this->userService->userSelection($filters, true);

        $job = (new UserMessageJob($this->user, $users, $type, $message, $subject))
            ->onQueue('user-message');
        $this->dispatch($job);

        return new Response(['data' => 'OK']);
    }

    /**
     * @return Response
     */
    public function getAllGroups()
    {
        $groups = Group::where('society_id', $this->user->current_society_id)->get();

        return new Response($groups);
    }

    public function getStatus()
    {
        $status = [
            0 => [
                'id' => 1,
                'name' => 'Candidat'
            ],
            1 => [
                'id' => 2,
                'name' => 'Débutant'
            ],
            2 => [
                'id' => 3,
                'name' => 'Confirmée'
            ],
        ];
        return new Response($status);
    }

    public function addExclusionSociety(Request $request)
    {
        $user_id = $request->route('id_me');
        $society_id = array_get($this->params, 'society_id');

        $res = DB::table('user_exclusion_society')
            ->where('user_id', $user_id)
            ->where('society_id', $society_id)
            ->get();

        if (empty($res)) {
            DB::table('user_exclusion_society')->insert(['user_id' => $user_id, 'society_id' => $society_id]);
        }

        return new Response(['data' => 'OK']);
    }

    public function removeExclusionSocieties(Request $request)
    {
        $user_id = $request->route('id_me');
        $societies = array_get($this->params, 'societies');

        foreach ($societies as $society) {
            DB::table('user_exclusion_society')->where([
                'user_id' => $user_id,
                'society_id' => $society
            ])->delete();
        }

        return new Response(['data' => 'OK']);
    }

    public function addComment(Request $request)
    {
        $user_id = $request->route('id_me');
        $created_by = $request['created_by'];
        $created_at = $request['created_at'];
        $comment = $request['comment'];
        $array = [
            'user_id' => $user_id,
            'created_by' => $created_by,
            'created_at' => $created_at,
            'comment' => $comment,
        ];
        DB::table('user_comment')->where('user_id', $user_id)->insert($array);
        return new Response(['data' => 'ok']);
    }

    public function deleteComments(Request $request)
    {
        $user_id = $request->route('id_me');

        foreach ($request['comments'] as $comment_id) {
            DB::table('user_comment')->where([
                'user_id' => $user_id,
                'id' => $comment_id
            ])->delete();
        }
        return new Response(['data' => 'ok']);
    }

    public function getUserName($comments)
    {
        foreach ($comments as $i => $c) {
            $user = User::where('id', $c['created_by'])->get();
            $name = null;
            foreach ($user as $u) {
                $name = $u['first_name'] . ' ' . $u['last_name'];
            }
            $comments[$i]['created_by_name'] = $name;
        }
        return $comments;
    }

    public function getComments(Request $request)
    {
        $user_id = $request->route('id_me');
        $comments = DB::table('user_comment')
            ->where('user_id', $user_id)
            ->get();

        $comments = $this->getUserName($comments);
        return new Response($comments);
    }

    public function addExclusionShop(Request $request)
    {
        $user_id = $request->route('id_me');
        $shop_id = array_get($this->params, 'shop_id');

        $res = DB::table('user_exclusion_shop')
            ->where('user_id', $user_id)
            ->where('shop_id', $shop_id)
            ->get();

        if (empty($res)) {
            DB::table('user_exclusion_shop')->insert(['user_id' => $user_id, 'shop_id' => $shop_id]);
        }

        return new Response(['data' => 'OK']);
    }

    public function removeExclusionShops(Request $request)
    {
        $user_id = $request->route('id_me');
        $shops = array_get($this->params, 'shops');

        foreach ($shops as $shop) {
            DB::table('user_exclusion_shop')->where([
                'user_id' => $user_id,
                'shop_id' => $shop
            ])->delete();
        }

        return new Response(['data' => 'OK']);
    }

    public function infos()
    {
        $user = new User();
        $user = $user->newListQuery();
        $users = $user->selectraw('DISTINCT id');
        $users = $user->where('society_id', $this->user->current_society_id)->whereNull('deleted_at');

        $response = (new SmiceFinder($users, $this->params, $this->user))->get();

        return new Response($response);
    }


    /**
     * @return Response
     */
    public function filter()
    {
        return new Response($this->userService->filter($this->params));
    }

    private $uigrid_index = -1;
    private $uigrid_index_key = -1;

    private function _uigrid($name, $colType = null)
    {
        $this->uigrid_index++;

        return [
            'name' => $name,
            'field' => 'col' . $this->uigrid_index,
            'colType' => $colType
        ];
    }

    private function radians($degrees)
    {
        $pi = pi();
        return $degrees * $pi / 180;
    }

    private function distance($latA, $latB, $lonA, $lonB)
    {
        return acos(
                sin($this->radians($latA)) * sin($this->radians($latB))
                + cos($this->radians($latA)) * cos($this->radians($latB))
                * cos($this->radians($lonA) - $this->radians($lonB))
            ) * 6371;
    }

    public function listUsers()
    {
        $users = User::select('id')->where('society_id', $this->user->current_society_id);
        $users = $this->usersFilter($users);

        $users_id = $users->get()->toarray();
        $user = new User();
        $user = $user->newListQuery();
        $users = $user->wherein('id', ArrayHelper::getIds($users_id));
        $response = (new SmiceFinder($users, $this->params, $this->user))->get();

        return new Response($response);
    }

    public function usersFilter($users)
    {
        $filters = $this->params['filters'];
        // si condition
        if (isset($filters['filter_group'])) {
            $users = $users->whereHas('groups', function ($q) use ($filters) {
                $q->whereIn('group.id', ArrayHelper::getIds($filters['filter_group']));
            });
        }
        if (isset($filters['filter_roles'])) {
            $users = $users->whereHas('roles', function ($q) use ($filters) {
                $q->where('role.id', $filters['filter_roles']);
            });
        }
        if (isset($filters['filter_validated_mission'])) {
            $users = $users->whereHas('userActivity', function ($q) use ($filters) {
                $q->where('user_activity.validated_mission', '>', $filters['filter_validated_mission']);
            });
        }
        if (isset($filters['filter_user_level_id'])) {
            $users = $users->whereHas('userActivity', function ($q) use ($filters) {
                $q->where('user_activity.user_level_id', $filters['filter_user_level_id']['id']);
            });
        }
        if (isset($filters['filter_smiceur_type'])) {
            $users = $users->where('sleepstatus', $filters['filter_smiceur_type']['id']);
        }
        if (isset($filters['filter_skills'])) {
            $users = $users->whereHas('skills', function ($q) use ($filters) {
                foreach ($filters['filter_skills'] as $skill_id) {
                    $q->where('user_skill.skill_id', $skill_id);
                }
            });
        }
        if (isset($filters['filter_address'])) {
            if (!isset($filters['filter_address_km'])) {
                $filters['filter_address_km'] = 10;
            }
            $users->whereRaw(
                "ACOS(SIN(RADIANS(lat)) * SIN(RADIANS(" . $filters['filter_address_lat'] . ")) + COS(RADIANS(lat)) * COS(RADIANS(" . $filters['filter_address_lat'] . ")) * COS(RADIANS(lon) - RADIANS(" . $filters['filter_address_lon'] . "))) * 6380 < " . $filters['filter_address_km']
            );
        }
        return $users;
    }

    public function getLevels()
    {
        return new Response(UserLevel::all());
    }
}
