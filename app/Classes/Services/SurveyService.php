<?php

namespace App\Classes\Services;

use App\Models\QuestionRowComment;
use App\Models\Survey;
use App\Models\SurveyItem;
use App\Models\Color;
use App\Models\Question;
use App\Models\QuestionRow;
use Illuminate\Support\Facades\DB;

class SurveyService extends SmiceService
{
    public function duplicate(Survey $survey, array $params)
    {
        /* @var $new_survey Survey */
        $new_survey = $survey->replicate();
        $name['fr'] = $new_survey->name['fr'] . ' copie du ' . date('Y-m-d');
        $new_survey->name = $name;
        $new_survey->created_by = $this->user->getKey();
        $new_survey->push();

        $survey_sequences = SurveyItem::where([
            'survey_id' => $survey->id,
            'type' => SurveyItem::ITEM_SEQUENCE,
            'parent_id' => null,
        ])->get();

        $this->duplicateSurveySequences($survey, $new_survey, $survey_sequences, $params);

        if($params['colors']){   
            $colors = Color::where('survey_id', $survey->id)->get();

            foreach($colors as $color){
                $new_color = new Color();
                $new_color->survey_id = $new_survey->id;
                $new_color->hex = $color->hex;
                $new_color->description = $color->description;
                $new_color->save();   
            }
        }

        return $new_survey;
    }

    /**
     * @param $id
     * @return SurveyItem
     */
    public function copySurveyItem($id)
    {
        /* @var $surveyItem SurveyItem */
        $surveyItem = SurveyItem::find($id);

        $question = Question::find($surveyItem->item_id);

        /* @var $questionNew Question */
        $questionNew = $question->replicate();
        $name = $question->name;
        $name[$this->user->language->code] = "copy - " .  $question->name[$this->user->language->code];
        $questionNew->name = $name;
        // The type must be specified with the value: 'answer_min' => 0
        // app/Classes/MockUp.php $types
        $questionNew->type = Question::TYPE_TEXT_AREA;
        $questionNew->save();

        Question::whereId($questionNew->id)->update(['type' => $question->type]);

        // For the table "question_row" you need to create records separately.
        // In other cases, entries are created via an event. (app/Models/Question::boot())
        $questionRows = QuestionRow::where('question_id', $question->id)->get();

        foreach ($questionRows as $questionRow) {
            $questionRowNew = new QuestionRow();
            $questionRowNew->fill($questionRow->toArray());
            $questionRowNew->question_id = $questionNew->id;
            if ($questionRowNew->save()) {
                foreach ($questionRow->comments as $questionRowComment) {
                    $questionRowCommentNew = new QuestionRowComment();
                    $questionRowCommentNew->fill($questionRowComment->toArray());
                    $questionRowCommentNew->question_row_id = $questionRowNew->id;
                    $questionRowCommentNew->save();
                }
            }
        }

        /* @var $surveyItemNew SurveyItem */
        $surveyItemNew = $surveyItem->replicate();
        $surveyItemNew->item_id = $questionNew->id;
        $surveyItemNew->save();

        // survey_item_axe
        $survey_item_axe = DB::table('survey_item_axe')
            ->where('survey_item_id', $id)
            ->get();

        $data_insert = [];
        foreach ($survey_item_axe as $item) {
            $data_insert[] = [
                'survey_item_id' => $surveyItemNew->id,
                'axe_id' => $item['axe_id'],
            ];
        }
        DB::table('survey_item_axe')->insert($data_insert);

        // survey_item_criterion_a
        $survey_item_criterion_a = DB::table('survey_item_criterion_a')
            ->where('survey_item_id', $id)
            ->get();

        $data_insert = [];
        foreach ($survey_item_criterion_a as $item) {
            $data_insert[] = [
                'survey_item_id' => $surveyItemNew->id,
                'criteria_a_id' => $item['criteria_a_id'],
            ];
        }
        DB::table('survey_item_criterion_a')->insert($data_insert);

        // survey_item_criterion_b
        $survey_item_criterion_a = DB::table('survey_item_criterion_b')
            ->where('survey_item_id', $id)
            ->get();

        $data_insert = [];
        foreach ($survey_item_criterion_a as $item) {
            $data_insert[] = [
                'survey_item_id' => $surveyItemNew->id,
                'criteria_b_id' => $item['criteria_b_id'],
            ];
        }
        DB::table('survey_item_criterion_b')->insert($data_insert);

        // survey_item_job
        $survey_item_criterion_a = DB::table('survey_item_job')
            ->where('survey_item_id', $id)
            ->get();

        $data_insert = [];
        foreach ($survey_item_criterion_a as $item) {
            $data_insert[] = [
                'survey_item_id' => $surveyItemNew->id,
                'job_id' => $item['job_id'],
            ];
        }
        DB::table('survey_item_job')->insert($data_insert);

        // survey_item_scenario
        $survey_item_criterion_a = DB::table('survey_item_scenario')
            ->where('survey_item_id', $id)
            ->get();

        $data_insert = [];
        foreach ($survey_item_criterion_a as $item) {
            $data_insert[] = [
                'survey_item_id' => $surveyItemNew->id,
                'scenario_id' => $item['scenario_id'],
            ];
        }
        DB::table('survey_item_scenario')->insert($data_insert);

        // survey_item_shop
        $survey_item_criterion_a = DB::table('survey_item_shop')
            ->where('survey_item_id', $id)
            ->get();

        $data_insert = [];
        foreach ($survey_item_criterion_a as $item) {
            $data_insert[] = [
                'survey_item_id' => $surveyItemNew->id,
                'shop_id' => $item['shop_id'],
            ];
        }
        DB::table('survey_item_shop')->insert($data_insert);

        // survey_item_theme
        $survey_item_criterion_a = DB::table('survey_item_theme')
            ->where('survey_item_id', $id)
            ->get();

        $data_insert = [];
        foreach ($survey_item_criterion_a as $item) {
            $data_insert[] = [
                'survey_item_id' => $surveyItemNew->id,
                'theme_id' => $item['theme_id'],
            ];
        }
        DB::table('survey_item_theme')->insert($data_insert);

        return $surveyItemNew;
    }

    public function translate($id, $language)
    {
        $survey = Survey::find($id);
        $survey->name = $this->translateText($survey->name, $language);
        $survey->save();

        foreach ($survey->items as $surveyItem) {
            if (SurveyItem::ITEM_QUESTION == $surveyItem->type) {
                $surveyItem->question->update([
                    'name' => $this->translateText($surveyItem->question->name, $language),
                    'info' => $this->translateText($surveyItem->question->info, $language),
                    'answer_explanation' => $this->translateText($surveyItem->question->answer_explanation, $language),
                    'description' => $this->translateText($surveyItem->question->description, $language),
                ]);
                foreach ($surveyItem->question->answers as $questionRow) {
                    $questionRow->update([
                        'name' => $this->translateText($questionRow->name, $language),
                    ]);
                }
            } else {
                $surveyItem->sequence->update([
                    'name' => $this->translateText($surveyItem->sequence->name, $language),
                    'info' => $this->translateText($surveyItem->sequence->info, $language),
                ]);
            }

            if ($surveyItem->criteria) {
                $surveyItem->criteria->update([
                    'name' => $this->translateText($surveyItem->criteria->name, $language),
                ]);
            }
        }

        return $survey;
    }

    /**
     * @param $field
     * @param string $target
     * @param string $service
     * @return array
     */
    private function translateText($field, $target = 'en', $service = 'google')
    {
        if (is_array($field) && $field['fr']) {
            if ('google' == $service) {
                $result = \GooTranslate::translate($field['fr'], $target, 'fr');
                if (is_array($result)) {
                    $field[$target] = $result[0]->translatedText;
                }
            } else {
                $credentials = [
                    'key' => env('AWS_TRANSLATION_KEY'),
                    'secret' => env('AWS_TRANSLATION_SECRET'),
                ];
                $client = new \Aws\Translate\TranslateClient(['version' => 'latest', 'credentials' => $credentials, 'region' => 'eu-west-1']);

                $result = $client->TranslateText([
                    'SourceLanguageCode' => 'fr', // REQUIRED
                    'TargetLanguageCode' => $target, // REQUIRED
                    'Text' => $field['fr'], // REQUIRED
                ]);
                if (is_array($result)) {
                    $field[$target] = $result[0]->translatedText;
                }
            }
        }
        return $field;
    }

    private function duplicateSurveySequences(Survey $survey, Survey $new_survey, $survey_sequences, array $params, $parent_id = NULL)
    {
        $copy_sequences = array_get($params, 'sequences');
        $copy_questions = array_get($params, 'questions');

        $display_conditions = array_get($params, 'display_conditions');

        $copy_themes = array_get($params, 'themes');
        $copy_jobs = array_get($params, 'jobs');
        $copy_criteria_a = array_get($params, 'criteria_a');
        $copy_criteria_b = array_get($params, 'criteria_b');

        foreach ($survey_sequences as $survey_sequence) {
            /* @var $survey_sequence SurveyItem */
            if ($copy_sequences) {
                /* @var $new_survey_sequence SurveyItem */
                $new_survey_sequence = $survey_sequence->replicate();
                $new_survey_sequence->survey_id = $new_survey->id;
                $new_survey_sequence->parent_id = $parent_id;
                $new_survey_sequence->push();
                $copy_sequence_id = $new_survey_sequence->id;
                $axe = SurveyItem::where(['id' => $survey_sequence->id])->first()->axes;
                $shop = SurveyItem::where(['id' => $survey_sequence->id])->first()->shops;
                $scenario = SurveyItem::where(['id' => $survey_sequence->id])->first()->scenarios;
                if (count($axe) && $display_conditions) {
                    $new_survey_sequence->axes()->sync($axe);
                }
                if (count($shop) && $display_conditions) {
                    $new_survey_sequence->shops()->sync($shop);
                }
                if (count($scenario) && $display_conditions) {
                    $new_survey_sequence->scenarios()->sync($scenario);
                }
            } else {
                $copy_sequence_id = $survey_sequence->id;
            }

            # Reading questions for the current sequence
            $survey_items = SurveyItem::where([
                'survey_id' => $survey->id,
                'parent_id' => $survey_sequence->id,
                'type' => SurveyItem::ITEM_QUESTION,
            ])->get();
            foreach ($survey_items as $survey_item) {
                $theme = SurveyItem::where(['id' => $survey_item->id])->first()->themes;
                $axe = SurveyItem::where(['id' => $survey_item->id])->first()->axes;
                $criterion_a = SurveyItem::where(['id' => $survey_item->id])->first()->criterionA;
                $criterion_b = SurveyItem::where(['id' => $survey_item->id])->first()->criterionB;
                $job = SurveyItem::where(['id' => $survey_item->id])->first()->jobs;
                $scenario = SurveyItem::where(['id' => $survey_item->id])->first()->scenarios;
                $shop = SurveyItem::where(['id' => $survey_item->id])->first()->shops;
                if ($copy_questions) {
                    $new_survey_item = $survey_item->replicate();
                    $new_survey_item->survey_id = $new_survey->id;
                    $new_survey_item->parent_id = $copy_sequence_id;
                    $new_survey_item->save();
                }
                if (count($theme) && $copy_questions && $copy_themes) {
                    $new_survey_item->themes()->sync($theme);
                }

                // to delete ?
                if (count($axe) && $copy_questions && $display_conditions) {
                    $new_survey_item->axes()->sync($axe);
                }

                if (count($shop) && $copy_questions && $display_conditions) {
                    $new_survey_item->shops()->sync($shop);
                }

                if (count($criterion_a) && $copy_questions && $copy_criteria_a) {
                    $new_survey_item->criterionA()->sync($criterion_a);
                }

                if (count($criterion_b) && $copy_questions && $copy_criteria_b) {
                    $new_survey_item->criterionB()->sync($criterion_b);
                }

                if (count($job) && $copy_questions && $copy_jobs) {
                    $new_survey_item->jobs()->sync($job);
                }

                if (count($scenario) && $copy_questions && $display_conditions) {
                    $new_survey_item->scenarios()->sync($scenario);
                }
            }

            $child_survey_sequences = $survey_sequence->items()->where('type', SurveyItem::ITEM_SEQUENCE)->get();
            if (!empty($child_survey_sequences)) {
                $this->duplicateSurveySequences($survey, $new_survey, $child_survey_sequences, $params, $copy_sequence_id);
            }
        }
    }
}