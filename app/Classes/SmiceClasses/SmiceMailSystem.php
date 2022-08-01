<?php

namespace App\Classes\SmiceClasses;
use Cache;


class SmiceMailSystem extends SmiceMail
{
    /**
     * The id of the mail system sender
     */
    const ID_SERVER = 0;

    /**
     * The generic no-reply address
     */
    const NO_REPLY_EMAIL = 'no-reply@smice.com';

    /**
     * List of system templates
     */
    const WELCOME                   = 'welcome';
    const ACTIVATE_ACCOUNT          = 'activate_account';
    const NEW_ACCOUNT               = 'new_account';
    const PASSWORD                  = 'password';
    const MISSION_INVITATION        = 'first_invitation';
    const READ                      = 'read';
    const INVALIDATED               = 'invalidated';
    const SURVEY_WAITING            = 'survey_waiting';
    const MISSION_SELECTION         = 'selected';
    const MISSION_UNSELECTION       = 'unselected';
    const MISSION_MANUAL_SELECTION  = 'selected_manual';
    const MISSION_CANCELED          = 'canceled';
    const MISSION_REFUSED           = 'refused';
    const MISSION_REJECTED          = 'rejected';
    const BRIEFING_AVAILABLE        = 'briefing';
    const BRIEFING_FAILED           = 'failed_briefing';
    const SURVEY_XLSX               = 'survey_xlsx';
    const QUIZ_FAILED               = 'failed_quiz';
    const MISSION_CONTRIBUTORS      = 'to_contributors';
    const REPORT_LINK               = 'report_link';
    const REPORT_ZIP                = 'report_zip';
    const IMAGE_ZIP                 = 'image_zip';
    const PAYMENT_ERROR             = 'payment_error';
    const WEDOOGIFT                 = 'wedoogift';
    const AUTOMATIC                 = 'automatic';
    const MISSION_IN_REVIEW         = 'mission_in_review';
    const USER_DISACTIVATED         = 'user_disactivated';
    const MISSION_TEST_VALIDATED    = 'test_validated';
    const MISSION_TEST_REFUSED      = 'test_refused';
    const USER_MESSAGE              = 'user_message';
    const SMICER_CHANGE_DATE_FR     = 'smicer_change_date';
    const NO_IBAN                   = 'no_iban';
    const READ_INVESTIGATOR         = 'read_investigator';
    const SELECTED_INVESTIGATOR     = 'selected_investigator';
    const READ_SNCF                 = 'read_sncf';
    const NEW_USER_AFTER_15         = 'new_user_after_15';
    const NO_BIRTHPLACE             = 'no_birthplace';
    const NO_NI                     = 'no_ni';
    const MISSION_SURVEY_REMINDER   = 'survey_reminder';
    const SHARE_DASHBOARD           = 'share_dashboard';
    const ACTIONPLAN_ASSIGNED       = 'action_plan_assigned';
    const ACTIONPLAN_CLOSED         = 'action_plan_closed';
    const ACTIONPLAN_UPDATED        = 'action_plan_updated';
    const ACTIONPLAN_CREATED        = 'action_plan_created';

    protected function __construct($template_name, $language = 'fr')
    {
        parent::__construct();
        $mandrill = new \Mandrill(\Config::get('services.mandrill.secret'));
        $cachekey = 'getListTemplaes';
        $templates = Cache::get($cachekey , function () use ($cachekey, $mandrill) {
            $templates = $mandrill->templates->getList();
            Cache::put($cachekey, $templates, 30);
            return $templates;
        });
        $mailTemplate = $template_name . '_' . $language;
        $defaultTemplate = $template_name . '_fr';
        $key = array_search($mailTemplate, array_column($templates, 'name'));
        $template = $key ? $templates[$key] : $templates[array_search($defaultTemplate, array_column($templates, 'name'))];
        //fix *|MC_PREVIEW_TEXT|*
        $template['code'] = str_replace('*|MC_PREVIEW_TEXT|*', '', $template['code']);
        $this->html($template['code'])
            ->sender(self::ID_SERVER)
            ->from(self::NO_REPLY_EMAIL)
            ->header('Reply-To', '<>');
    }

    public function addMergeVars(array $vars)
    {
        foreach ($vars as $user_id => $var) {
            $user = $this->to->where('id', $user_id)->first();

            if ($user) {
                foreach ($var as $name => $content) {
                    $this->mergeVars($user['email'], $name, $content);
                }
            }
        }

        return $this;
    }

    public function addGlobalMergeVars(array $vars)
    {
        return $this->globalMergeVars($vars);
    }

    public static function send($template, \Closure $callback, $language)
    {
        $message = new self($template, $language);

        $callback($message);
        $message->pushOnQueue();
    }

    public static function later($delay, $template, \Closure $callback, $language)
    {
        $message = new self($template, $language);

        $message->delay($delay);
        $callback($message);

        $message->pushOnQueue();
    }
}