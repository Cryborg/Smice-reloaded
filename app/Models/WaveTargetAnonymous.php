<?php

namespace App\Models;

/**
 * App\Models\WaveTargetAnonymous
 *
 * @property int $id
 * @property string $date_start
 * @property string $date_end
 * @property int|null $reviewer_id
 * @property int $wave_id
 * @property int $shop_id
 * @property int $mission_id
 * @property int|null $briefing_id
 * @property int|null $scenario_id
 * @property int|null $survey_id
 * @property int|null $quiz_id
 * @property int|null $user_id
 * @property int|null $reader_id
 * @property int|null $nb_quiz_error
 * @property string $name
 * @property string $type
 * @property string|null $hours
 * @property string $description
 * @property bool $has_briefing
 * @property bool $has_quiz
 * @property bool $answered_quiz
 * @property bool $answered_survey
 * @property bool $read_survey
 * @property mixed $filters
 * @property bool $ask_refund
 * @property bool $ask_proof
 * @property float|null $refund
 * @property int|null $compensation
 * @property string|null $category
 * @property bool $monday
 * @property bool $tuesday
 * @property bool $wednesday
 * @property bool $thursday
 * @property bool $friday
 * @property bool $saturday
 * @property bool $sunday
 * @property string $status
 * @property string|null $picture
 * @property string $date_status
 * @property string|null $visit_date
 * @property string|null $validation_mode
 * @property string|null $uuid
 * @property string|null $pdf_url
 * @property string|null $pdf_url_created_at
 * @property int|null $percentage_completeness
 * @property bool $is_paid
 * @property string $validation
 * @property bool $permanent_mission
 * @property float $frais_kms
 * @property float $score
 * @property int|null $program_id
 * @property string|null $program
 * @property bool|null $anonymous_mode
 * @property bool|null $ended
 * @property int|null $society_id
 * @property string|null $society
 * @property float|null $shop_lat
 * @property float|null $shop_lon
 * @property string|null $shop_place
 * @property string|null $city
 * @property string|null $pc
 * @property int|null $selected_user
 * @property float|null $global_score
 * @property int|null $propositions
 * @property int|null $accepted
 * @property int|null $invalidated
 * @property int|null $validated
 * @property string|null $image
 * @property string|null $survey_answered_at
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Claim[] $claim
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Answer[] $answers
 * @property-read \App\Models\User|null $boss
 * @property-read \App\Models\Briefing|null $briefing
 * @property-read \App\Models\Mission $mission
 * @property-read \App\Models\Survey|null $quiz
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\Scenario|null $scenario
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\Survey|null $survey
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tags[] $tag
 * @property-read \App\Models\Tags $tags
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \App\Models\Wave $wave
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTargetHistory[] $waveTargetHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PassageProof[] $passageProofs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveUser[] $waveUsers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTargetConversation[] $waveTargetConversations
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\Gain|null $gain
 * @property-read \App\Models\Signature $signature
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTargetConversationGlobal[] $waveTargetConversationsGlobal
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereAnsweredQuiz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereAnsweredSurvey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereAskProof($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereAskRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereAutomaticAssignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereBriefingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereCompensation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereDateStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereFraisKms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereFriday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereHasBriefing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereHasQuiz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereHasUserUuid($uuid)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereMonday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereNbQuizError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous wherePdfUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous wherePdfUrlCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous wherePercentageCompleteness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereQuizId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereReadSurvey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereReaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereSaturday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereSunday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereThursday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereTuesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereValidationMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereVisitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereWednesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereAnonymousMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereBoss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereEnded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereGlobalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereInvalidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereMission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous wherePc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereProgram($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous wherePropositions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereScenario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereSelectedUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereShop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereShopLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereShopLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereShopPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereSociety($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereSurveyAnsweredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereWave($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetAnonymous whereClaim($value)
 * @mixin \Eloquent
 */
class WaveTargetAnonymous extends WaveTarget
{
    protected $table                = 'show_targets_anonymous';

    protected $list_table           = 'show_targets_anonymous';
}