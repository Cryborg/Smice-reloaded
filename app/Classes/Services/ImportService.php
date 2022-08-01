<?php

namespace App\Classes\Services;

use App\Models\Answer;
use App\Models\AnswerDeleted;
use App\Models\AnswerComment;
use App\Models\AnswerCommentDeleted;
use App\Models\AnswerImage;
use App\Models\AnswerImageDeleted;
use App\Models\AnswerFile;
use App\Models\AnswerFileDeleted;
use App\Models\Question;
use App\Models\QuestionRow;
use App\Models\WaveTarget;
use App\Exceptions\SmiceException;

use Maatwebsite\Excel\Facades\Excel;

class ImportService extends SmiceService
{
    public function saveBeforeDelete($data)
    {
        if ($data->count() > 0) {
            AnswerDeleted::insert($data->toArray());
            foreach ($data as $s) {
                $answer_id[] = $s['id'];
            }
            $save_comments = AnswerComment::whereIn('answer_id', $answer_id)->get();
            if ($save_comments->count() > 0) {
                AnswerCommentDeleted::insert($save_comments->toArray());
                AnswerComment::whereIn('answer_id', $answer_id)->delete();
            }
            $save_images = AnswerImage::whereIn('answer_id', $answer_id)->get();
            if ($save_images->count() > 0) {
                AnswerImageDeleted::insert($save_images->toArray());
                AnswerImage::whereIn('answer_id', $answer_id)->delete();
            }

            $save_files = AnswerFile::whereIn('answer_id', $answer_id)->get();
            if ($save_files->count() > 0) {
                AnswerFileDeleted::insert($save_files->toArray());
                AnswerFile::whereIn('answer_id', $answer_id)->delete();
            }
        }
    }

    public function importMissions($file)
    {
        $updated = [];
        
        \Excel::load($file, function ($doc) use (&$updated) {
            /* @var $doc Spreadsheet */
            $sheet = $doc->getSheetByName('Export'); // sheet with name data, but you can also use sheet indexes.
            $data = $sheet->toArray();
            $lastCol = 0;
            for ($i = 10; $i < count($data[0]); $i++) {
                if ($i % 4 === 0 && is_null($data[0][$i])) {
                    $lastCol = $i;
                }
            }
            $question_type = [];
            for ($i = 18; $i < count($data); $i++) {
                for ($j = 11; $j <= $lastCol; $j++) {
                    if (!is_null($data[0][$j]) && !is_null($data[$i][$j])) { // for each question (each column in excel) // to do : check null value to empty answer if need
                        $waveTargetId = $data[$i][0];
                        $questionId = $data[0][$j];
                        $question_type = isset($question_type[$questionId]) ? $question_type[$questionId] : Question::findorFail($questionId)->toArray(); //check if one answer already set for this question and this mission
                        $question_type[$questionId] = $question_type;
                        if ($question_type['type'] === 'checkbox') {
                            $answerValue = explode(";", $data[$i][$j]);
                            $commentValue = explode(";", $data[$i][$j + 1]);
                        }
                        else {
                            $answerValue = [];
                            $commentValue = [];
                            $answerValue[] = $data[$i][$j];
                            $commentValue[] = $data[$i][$j + 1];
                        }
                       
                        $userid = $data[$i][5];
                        $wt = WaveTarget::find($waveTargetId);
                        //if user want clean answer
                        if ($answerValue[0] === 'PLEASE_DELETE') {
                            $a = Answer::whereWaveTargetId($waveTargetId)
                                ->whereUserId($userid)
                                ->whereQuestionId($questionId)
                                ->get();
                            if ($a->count() > 0) {
                                $this->saveBeforeDelete($a);
                                Answer::whereWaveTargetId($waveTargetId)
                                    ->whereUserId($userid)
                                    ->whereQuestionId($questionId)
                                    ->delete();
                            }
                        }
                        if ($wt) {
                            if (in_array($question_type['type'], [
                                Question::TYPE_CHECKBOX
                            ])) {
                                $save_answer = [];
                                $q = QuestionRow::whereQuestionId($questionId)->get();
                                foreach ($q as $questionRow) { //read all answer available for this question
                                    foreach ($answerValue as $k => $answer) { //foreach answer, excel can have more than one answer : answ1;answ2.... same for comment
                                        //check if answer already set
                                        $t = $questionRow->name[$this->user->language->code];
                                        if ($questionRow->name[$this->user->language->code] == $answer) { // answer value is find in possible answer
                                            //search if answer already set
                                            $answer = Answer::whereWaveTargetId($waveTargetId) //check if this answer already set for this question and this mission
                                                ->whereUserId($userid)
                                                ->whereQuestionId($questionId)
                                                ->whereQuestionRowId($questionRow->id)
                                                ->first();
                                            if (isset($commentValue[$k]) && $commentValue[$k] == "")
                                                $commentValue[$k] = null;
                                            if (!$answer) { //if not we add answer
                                                $answer = new Answer();
                                                $answer->question_row_id = $questionRow->id;
                                                $answer->comment = isset($commentValue[$k]) ? $commentValue[$k] : null;
                                                $answer->survey_id = $wt['survey_id'];
                                                $answer->user_id = $userid;
                                                $answer->question_id = $questionId;
                                                $answer->value = true;
                                                $answer->wave_target_id = $waveTargetId;
                                                $answer->save();
                                                $save_answer[] = $questionRow->id;
                                            } else {
                                                if (isset($commentValue[$k])) {
                                                    if ($answer->comment !== $commentValue[$k]) { //answer already set, check if comment has change
                                                        $answer->comment = $commentValue[$k];
                                                        $answer->save();
                                                    }
                                                }
                                                $save_answer[] = $questionRow->id;
                                            }
                                            $updated[] = $answer;
                                        }
                                    }
                                }
                                //clean all other answers
                                $a = Answer::whereWaveTargetId($waveTargetId)
                                    ->whereUserId($userid)
                                    ->whereQuestionId($questionId)
                                    ->wherenotin('question_row_id', $save_answer)
                                    ->get();
                                if ($a->count() > 0) {
                                    $this->saveBeforeDelete($a);
                                    Answer::whereWaveTargetId($waveTargetId)
                                        ->whereUserId($userid)
                                        ->whereQuestionId($questionId)
                                        ->wherenotin('question_row_id', $save_answer)
                                        ->delete();
                                }
                            } else if (in_array($question_type['type'], [
                                Question::TYPE_RADIO,
                                Question::TYPE_SELECT,
                            ])) {
                                $q = QuestionRow::whereQuestionId($questionId)->get();
                                foreach ($q as $questionRow) { //read all answer available for this question
                                    foreach ($answerValue as $k => $answer) { //foreach answer, excel can have more than one answer : answ1;answ2.... same for comment
                                        //check if answer already set
                                        if ($questionRow->name[$this->user->language->code] == $answer) { // answer value is find in possible answer
                                            //search if answer already set
                                            $answer = Answer::whereWaveTargetId($waveTargetId) //check if this answer already set for this question and this mission
                                                ->whereUserId($userid)
                                                ->whereQuestionId($questionId)
                                                ->first();
                                            if ($commentValue[$k] == "")
                                                $commentValue[$k] = null;
                                            if (!$answer) { //if not we add answer
                                                $answer = new Answer();
                                                $answer->question_row_id = $questionRow->id;
                                                $answer->comment = $commentValue[$k];
                                                $answer->survey_id = $wt['survey_id'];
                                                $answer->user_id = $userid;
                                                $answer->question_id = $questionId;
                                                $answer->wave_target_id = $waveTargetId;
                                                $answer->save();
                                            } else { //answer already set, check if comment has change
                                                $change = false;
                                                if ($answer->comment !== $commentValue[$k]) {
                                                    $change = true;
                                                    $answer->comment = $commentValue[$k];
                                                }
                                                if ($answer->question_row_id !== $questionRow->id) {
                                                    $change = true;
                                                    $answer->question_row_id = $questionRow->id;
                                                }
                                                if ($change)
                                                    $answer->save();
                                            }
                                            $updated[] = $answer;
                                        }
                                    }
                                }
                            } else if (!in_array($question_type['type'], [ // all other type without  TYPE_MATRIX_CHECKBOX & TYPE_MATRIX_RADIO
                                Question::TYPE_MATRIX_CHECKBOX,
                                Question::TYPE_MATRIX_RADIO
                            ])) {
                                foreach ($answerValue as $k => $answer) { //foreach answer, excel can have more than one answer : answ1;answ2.... same for comment
                                    $answer = Answer::whereWaveTargetId($waveTargetId) //check if this answer already set for this question and this mission
                                        ->whereUserId($userid)
                                        ->whereQuestionId($questionId)
                                        ->first();
                                    if (!array_key_exists($k , $commentValue)) {
                                        throw new SmiceException(
                                            SmiceException::HTTP_BAD_REQUEST,
                                            SmiceException::E_VALIDATION,
                                            'Commentaire manquant pour la/les réponse(s) ' . implode(",", $answerValue)
                                        );
                                    }
                                    if ($commentValue[$k] == "")
                                        $commentValue[$k] = null;
                                    if (!$answer) { //if not we add answer
                                        $answer = new Answer();
                                        $answer->value = $answerValue[$k];
                                        $answer->comment = $commentValue[$k];
                                        $answer->survey_id = $wt['survey_id'];
                                        $answer->user_id = $userid;
                                        $answer->question_id = $questionId;
                                        $answer->wave_target_id = $waveTargetId;
                                        $answer->save();
                                    } else { //answer already set, check if comment has change
                                        $change = false;
                                        if ($answer->comment !== $commentValue[$k]) {
                                            $change = true;
                                            $answer->comment = $commentValue[$k];
                                        }
                                        if ($answer->value !== $answerValue[$k]) {
                                            $change = true;
                                            $answer->value = $answerValue[$k];
                                        }
                                        if ($change)
                                            $answer->save();
                                    }
                                    $updated[] = $answer;
                                }
                            }
                        }
                    }
                }
            }
        });

        return ['updated' => $updated];
    }
}
