<?php

namespace App\Http\Role\Permissions;

use App\Http\Group\Models\Group;
use App\Http\Shops\Models\Shop;
use App\Http\Skill\Models\Skill;
use App\Http\User\Models\User;
use App\Http\User\Models\UserPermission;
use App\Models\Actionplan;
use App\Models\Alert;
use App\Models\AlertVariable;
use App\Models\Alias;
use App\Models\Answer;
use App\Models\Axe;
use App\Models\AxeDirectory;
use App\Models\AxeTag;
use App\Models\Briefing;
use App\Models\Checklist;
use App\Models\Color;
use App\Models\Conversation;
use App\Models\Criteria;
use App\Models\CriteriaA;
use App\Models\CriteriaB;
use App\Models\Dashboard;
use App\Models\FailedJobs;
use App\Models\Gain;
use App\Models\Graph;
use App\Models\GraphTemplate;
use App\Models\GroupWave;
use App\Models\Job;
use App\Models\Jobs;
use App\Models\MailLog;
use App\Models\MailTemplate;
use App\Models\Mission;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Question;
use App\Models\Role;
use App\Models\Scenario;
use App\Models\Sequence;
use App\Models\Society;
use App\Models\Survey;
use App\Models\Tags;
use App\Models\Theme;
use App\Models\Todoist;
use App\Models\View;
use App\Models\ViewShare;
use App\Models\Wave;
use App\Models\WaveTarget;
use App\Models\WaveTargetConversation;
use App\Models\waveTargetConversationGlobal;
use App\Models\WaveTargetConversationPrivate;
use Illuminate\Support\Str;

class Permissions
{
    private static array $simple_skell = [
        'alerts' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'missions' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'shops' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'surveys' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'roles' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'societies' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'users' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'dashboards' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'gains' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'messages' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ]
    ];

    private static array $advanced_skell = [
        'alerts' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'axes' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'axeDirectories' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'axeTags' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'briefings' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'checklists' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'todoists' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'actionplan' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'criterion' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'criterionA' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'criterionB' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'jobs' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'targets' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'mails' => [
            Action::READ   => PermissionMode::OFF
        ],
        'mail_templates' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'missions' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'shops' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'programs' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'questions' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'answers' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'surveys' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'roles' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'groups' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'scenarios' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'sequences' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'societies' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'themes' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'users' => [
            Action::READ   => PermissionMode::OFF,
            Action::MODIFY => PermissionMode::OFF,
            Action::DELETE => PermissionMode::OFF
        ],
        'waves' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'alias' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'gains' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'payments' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'dashboards' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'graphs' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'graph_templates' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'tags' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ],
        'colors' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'conversations' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'conversations_private' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'conversations_global' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'skills' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'permissions' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'group_wave' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'variables' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'views' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'view-shares' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF,
        ],
        'messages' => [
            Action::READ    => PermissionMode::OFF,
            Action::MODIFY  => PermissionMode::OFF,
            Action::DELETE  => PermissionMode::OFF
        ]
    ];

    private static array $backoffice_menu_skell = [
        'societies' => [
            'consult'           => PermissionMode::OFF
        ],
        'shops' => [
            'consult'           => PermissionMode::OFF
        ],
        'users' => [
            'consult'           => PermissionMode::OFF
        ],
        'payments' => [
            'consult'           => PermissionMode::OFF
        ],
        'surveys' => [
            'consult'           => PermissionMode::OFF
        ],
        'settings' => [
            'consult'           => PermissionMode::OFF
        ],
        'new_wave' => [
            'consult'           => PermissionMode::OFF
        ],
        'monitoring' => [
            'consult'           => PermissionMode::OFF
        ],
        'surveys_done' => [
            'consult'           => PermissionMode::OFF
        ],
        'global_report' => [
            'consult'           => PermissionMode::OFF
        ],
        'sequences_report' => [
            'consult'           => PermissionMode::OFF
        ],
        'themes_report' => [
            'consult'           => PermissionMode::OFF
        ],
        'tags_report' => [
            'consult'           => PermissionMode::OFF
        ],
        'matrix_report' => [
            'consult'           => PermissionMode::OFF
        ],
        'criterion_report' => [
            'consult'           => PermissionMode::OFF
        ],
        'questions_report' => [
            'consult'           => PermissionMode::OFF
        ],
        'images_report' => [
            'consult'           => PermissionMode::OFF
        ],
        'dashboards' => [
            'consult'           => PermissionMode::OFF
        ],
        'permissions' => [
            'consult'           => PermissionMode::OFF
        ],
        'template_email' => [
            'consult'           => PermissionMode::OFF
        ],
        'alerts' => [
            'consult'           => PermissionMode::OFF
        ],
        'variables' => [
            'consult'           => PermissionMode::OFF
        ],
        'queues' => [
            'consult'           => PermissionMode::OFF
        ],
        'exclusions' => [
            'consult'           => PermissionMode::OFF
        ],
        'failed_jobs' => [
            'consult'           => PermissionMode::OFF
        ],
        'actionplan' => [
            'consult'           => PermissionMode::OFF
        ],
        'ranking' => [
            'consult'           => PermissionMode::OFF
        ],
        'criteriagrid' => [
            'consult'           => PermissionMode::OFF
        ],
        'myresults' => [
            'consult'           => PermissionMode::OFF
        ],
        'documents' => [
            'consult'           => PermissionMode::OFF
        ],
        'verbatim' => [
            'consult'           => PermissionMode::OFF
        ],
        'picturewall' => [
            'consult'           => PermissionMode::OFF
        ],
        'reading' => [
            'consult'           => PermissionMode::OFF
        ],
        'messages' => [
            'consult'           => PermissionMode::OFF
        ],
        'news' => [
            'consult'           => PermissionMode::OFF
        ],
        'referencial' => [
            'consult'           => PermissionMode::OFF
        ],
        'progress' => [
            'consult'           => PermissionMode::OFF
        ]

    ];

    private static array $homeboard_skell = [
        'login_history' => [
            'consult'           => PermissionMode::OFF
        ],
        'login_history_admin' => [
            'consult'           => PermissionMode::OFF
        ]
    ];

    private static array $modules_classes = [
        'alerts'                => Alert::class,
        'axes'                  => Axe::class,
        'axeDirectories'        => AxeDirectory::class,
        'axeTags'               => AxeTag::class,
        'briefings'             => Briefing::class,
        'checklists'            => Checklist::class,
        'todoists'              => Todoist::class,
        'actionplan'            => Actionplan::class,
        'criterion'             => Criteria::class,
        'criterionA'            => CriteriaA::class,
        'criterionB'            => CriteriaB::class,
        'jobs'                  => Job::class,
        'targets'               => WaveTarget::class,
        'mails'                 => MailLog::class,
        'missions'              => Mission::class,
        'mail_templates'        => MailTemplate::class,
        'shops'                 => Shop::class,
        'programs'              => Program::class,
        'questions'             => Question::class,
        'answers'               => Answer::class,
        'surveys'               => Survey::class,
        'roles'                 => Role::class,
        'groups'                => Group::class,
        'scenarios'             => Scenario::class,
        'sequences'             => Sequence::class,
        'societies'             => Society::class,
        'themes'                => Theme::class,
        'users'                 => User::class,
        'waves'                 => Wave::class,
        'group_wave'            => GroupWave::class,
        'alias'                 => Alias::class,
        'gains'                 => Gain::class,
        'payments'              => Payment::class,
        'dashboards'            => Dashboard::class,
        'graphs'                => Graph::class,
        'graph_templates'       => GraphTemplate::class,
        'tags'                  => Tags::class,
        'colors'                => Color::class,
        'conversations'         => WaveTargetConversation::class,
        'conversations_private' => WaveTargetConversationPrivate::class,
        'conversations_global'  => WaveTargetConversationGlobal::class,
        'skills'                => Skill::class,
        'queues'                => Jobs::class,
        'failed_jobs'           => FailedJobs::class,
        'permissions'           => UserPermission::class,
        'variables'             => AlertVariable::class,
        'views'                 => View::class,
        'view-shares'           => ViewShare::class,
        'messages'              => Conversation::class,
    ];

    public static function getSimplePermissions(): array
    {
        $permissions = static::$simple_skell;

        foreach ($permissions as $key_1 => $actions) {
            foreach ($actions as $key_2 => $value) {
                $permissions[$key_1][$key_2] = PermissionMode::OFF;
            }
        }

        return $permissions;
    }

    public static function getAdvancedPermissions(): array
    {
        $permissions        = static::$advanced_skell;

        foreach ($permissions as $key_1 => $actions) {
            foreach ($actions as $key_2 => $value) {
                $permissions[$key_1][$key_2] = PermissionMode::OFF;
            }
        }

        return $permissions;
    }

    public static function getBackofficeMenuPermissions(): array
    {
        $permissions        = static::$backoffice_menu_skell;

        foreach ($permissions as $key_1 => $actions) {
            foreach ($actions as $key_2 => $value) {
                $permissions[$key_1][$key_2] = PermissionMode::OFF;
            }
        }

        return $permissions;
    }

    public static function getHomeboardPermissions(): array
    {
        $permissions        = static::$homeboard_skell;

        foreach ($permissions as $key_1 => $actions) {
            foreach ($actions as $key_2 => $value) {
                $permissions[$key_1][$key_2] = PermissionMode::OFF;
            }
        }

        return $permissions;
    }

    public static function moduleExists($module)
    {
        if (isset(static::$modules_classes[$module])) {
            return static::$modules_classes[$module];
        }

        return false;
    }

    public static function cleanSimplePermissions(array $skell): array
    {
        $clean_skell        = static::getSimplePermissions();

        foreach ($clean_skell as $module => $actions) {
            foreach ($actions as $action => $mode) {
                if (isset($skell[$module][$action]) && is_bool($skell[$module][$action])) {
                    $clean_skell[$module][$action] = $skell[$module][$action];
                }
            }
        }

        return $clean_skell;
    }

    public static function cleanAdvancedPermissions(array $skell): array
    {
        $clean_skell        = static::getAdvancedPermissions();

        foreach ($clean_skell as $module => $actions) {
            foreach ($actions as $action => $mode) {
                if (isset($skell[$module][$action])) {
                    $mode = $skell[$module][$action];
                    if (Action::isStatic($action) && is_bool($mode)) {
                        $clean_skell[$module][$action] = $mode;
                    } elseif ($skell[$module][$action] === PermissionMode::OFF ||
                        $skell[$module][$action] === PermissionMode::SELF ||
                        $skell[$module][$action] === PermissionMode::SOCIETY ||
                        $skell[$module][$action] === PermissionMode::ADMIN) {
                        $clean_skell[$module][$action] = $mode;
                    }
                }
            }
        }

        return $clean_skell;
    }

    public static function societiesRead(): array
    {
        return [
            'axes' => [
                Action::READ => PermissionMode::SOCIETY
            ],
            'axeDirectories' => [
                Action::READ => PermissionMode::SOCIETY
            ],
            'alias' => [
                Action::READ => PermissionMode::SOCIETY
            ],
            'tags' => [
                Action::READ => PermissionMode::SOCIETY
            ],
            'societies' => [
                Action::READ => PermissionMode::SOCIETY
            ],
            'mails' => [
                Action::READ => PermissionMode::ADMIN
            ],
            'mail_templates' => [
                Action::READ => PermissionMode::SOCIETY
            ],
            'messages' => [
                Action::READ => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function societiesDelete(): array
    {
        return [
            'axes' => [
                Action::DELETE => PermissionMode::ON
            ],
            'axeDirectories' => [
                Action::DELETE => PermissionMode::ON
            ],
            'alias' => [
                Action::DELETE => PermissionMode::ON
            ],
            'tags' => [
                Action::DELETE => PermissionMode::ON
            ],
            'societies' => [
                Action::DELETE => PermissionMode::ON
            ],
            'mail_templates' => [
                Action::DELETE => PermissionMode::ON
            ]
        ];
    }

    public static function societiesCreateUpdate(): array
    {
        return [
            'axes' => [
                Action::MODIFY => PermissionMode::ON
            ],
            'axeDirectories' => [
                Action::MODIFY => PermissionMode::ON
            ],
            'alias' => [
                Action::MODIFY => PermissionMode::ON
            ],
            'tags' => [
                Action::MODIFY => PermissionMode::ON
            ],
            'societies' => [
                Action::MODIFY => PermissionMode::ON
            ],
            'mail_templates' => [
                Action::MODIFY => PermissionMode::ON
            ]
        ];
    }

    public static function shopsRead(): array
    {
        return [
            'axes' => [
                Action::READ => PermissionMode::SOCIETY
            ],
            'axeDirectories' => [
                Action::READ => PermissionMode::SOCIETY
            ],
            'shops' => [
                Action::READ => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function shopsDelete(): array
    {
        return [
            'axes' => [
                Action::DELETE => PermissionMode::ON
            ],
            'axeDirectories' => [
                Action::DELETE => PermissionMode::ON
            ],
            'shops' => [
                Action::DELETE => PermissionMode::ON
            ]
        ];
    }

    public static function shopsCreateUpdate(): array
    {
        return [
            'axes' => [
                Action::MODIFY => PermissionMode::ON
            ],
            'axeDirectories' => [
                Action::MODIFY => PermissionMode::ON
            ],
            'shops' => [
                Action::MODIFY => PermissionMode::ON
            ]
        ];
    }

    public static function usersRead(): array
    {
        return [
            'users' => [
                Action::READ    => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function usersDelete(): array
    {
        return [
            'users' => [
                Action::DELETE    => PermissionMode::ON
            ]
        ];
    }

    public static function usersCreateUpdate(): array
    {
        return [
            'users' => [
                Action::MODIFY    => PermissionMode::ON
            ]
        ];
    }

    public static function surveysRead(): array
    {
        return [
            'sequences' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'questions' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'themes' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'jobs' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'criterion' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'criterionA' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'criterionB' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'surveys' => [
                Action::READ    => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function surveysDelete(): array
    {
        return [
            'sequences' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'questions' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'themes' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'jobs' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'criterion' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'criterionA' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'criterionB' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'surveys' => [
                Action::DELETE    => PermissionMode::ON
            ]
        ];
    }

    public static function surveysCreateUpdate(): array
    {
        return [
            'sequences' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'questions' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'themes' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'jobs' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'criterion' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'criterionA' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'criterionB' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'surveys' => [
                Action::MODIFY    => PermissionMode::ON
            ]
        ];
    }

    public static function missionsRead(): array
    {
        return [
            'missions' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'waves' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'targets' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'programs' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'scenarios' => [
                Action::READ    => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function missionsDelete(): array
    {
        return [
            'missions' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'waves' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'targets' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'programs' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'scenarios' => [
                Action::DELETE    => PermissionMode::ON
            ]
        ];
    }

    public static function missionsCreateUpdate(): array
    {
        return [
            'missions' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'waves' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'targets' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'programs' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'briefings' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'scenarios' => [
                Action::MODIFY    => PermissionMode::ON
            ]
        ];
    }

    public static function alertsRead(): array
    {
        return [
            'alerts' => [
                Action::READ    => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function alertsDelete(): array
    {
        return [
            'alerts' => [
                Action::DELETE    => PermissionMode::ON
            ]
        ];
    }

    public static function  alertsCreateUpdate(): array
    {
        return [
            'alerts' => [
                Action::MODIFY    => PermissionMode::ON
            ]
        ];
    }

    public static function  dashboardsRead(): array
    {
        return [
            'dashboards' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'graphs' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'graph_templates' => [
                Action::READ    => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function  dashboardsDelete(): array
    {
        return [
            'dashboards' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'graphs' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'graph_templates' => [
                Action::DELETE    => PermissionMode::ON
            ]
        ];
    }

    public static function  dashboardsCreateUpdate(): array
    {
        return [
            'dashboards' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'graphs' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'graph_templates' => [
                Action::MODIFY    => PermissionMode::ON
            ]
        ];
    }

    public static function  rolesRead(): array
    {
        return [
            'roles' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'users' => [
                Action::READ    => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function  rolesDelete(): array
    {
        return [
            'roles' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'users' => [
                Action::DELETE    => PermissionMode::ON
            ]
        ];
    }

    public static function  rolesCreateUpdate(): array
    {
        return [
            'roles' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'users' => [
                Action::MODIFY    => PermissionMode::ON
            ]
        ];
    }

    public static function  gainsRead(): array
    {
        return [
            'gains' => [
                Action::READ    => PermissionMode::SOCIETY
            ],
            'payments' => [
                Action::READ    => PermissionMode::SOCIETY
            ]
        ];
    }

    public static function  gainsDelete(): array
    {
        return [
            'gains' => [
                Action::DELETE    => PermissionMode::ON
            ],
            'payments' => [
                Action::DELETE    => PermissionMode::ON
            ]
        ];
    }

    public static function  gainsCreateUpdate(): array
    {
        return [
            'gains' => [
                Action::MODIFY    => PermissionMode::ON
            ],
            'payments' => [
                Action::MODIFY    => PermissionMode::ON
            ]
        ];
    }

    public static function messagesRead(): array
    {
        return [
            'messages' => [
                Action::READ   => PermissionMode::ON
            ]
        ];
    }

    public static function  messagesDelete(): array
    {
        return [
            'messages' => [
                Action::DELETE    => PermissionMode::ON
            ]
        ];
    }

    public static function  messagesCreateUpdate(): array
    {
        return [
            'messages' => [
                Action::MODIFY    => PermissionMode::ON
            ]
        ];
    }

    public static function  generateAdvancedPermissions(array $simple_permissions): array
    {
        $clean_skell        = static::getAdvancedPermissions();

        foreach ($simple_permissions as $module => $actions) {
            foreach ($actions as $action => $mode) {
                $function = ($action === Action::MODIFY)
                    ? Str::camel($module. ' create update')
                    : Str::camel($module. ' '. $action);
                if ($mode === PermissionMode::ON && method_exists(self::class, $function)) {
                    $new_permissions = call_user_func([self::class, $function]);

                    foreach ($new_permissions as $new_module => $new_actions) {
                        foreach ($new_actions as $new_action => $new_mode) {
                            $clean_skell[$new_module][$new_action] = $new_mode;
                        }
                    }
                }
            }
        }

        return $clean_skell;
    }

    public static function mergePermissions(array $permissions_1, array $permissions_2): array
    {
        foreach ($permissions_1 as $module => $actions)
        {
            foreach ($actions as $action => $mode)
            {

                if (array_key_exists($module, $permissions_2)) {
                $mode_2 = $permissions_2[$module][$action];

                if ($mode_2 > $mode)
                    $permissions_1[$module][$action] = $mode_2;
                }

            }
        }

        return $permissions_1;
    }

    public static function  areDifferent(array $permissions_1, array $permissions_2): bool
    {
        foreach ($permissions_1 as $module => $actions) {
            foreach ($actions as $action => $mode)
                if ($mode != $permissions_2[$module][$action]) {
                    return true;
                }
        }

        return false;
    }
}
