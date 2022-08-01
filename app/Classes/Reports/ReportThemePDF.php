<?php

namespace App\Classes\Reports;

use App\Classes\Factory\ReportPdfFactory;
use App\Classes\Helpers\FusionHelper;
use App\Classes\Helpers\GlobalScoreHelper;
use App\Exceptions\SmiceException;
use App\Models\Survey;
use App\Models\SurveyItem;
use Carbon\Carbon;

class ReportThemePDF extends ReportPDF
{
    /**
     * @param string $uuid
     * @return string
     * @throws SmiceException
     */
    public function generateReport(string $uuid, $forceupdate = false, $alert = null, $theme_id = null)
    {
        $target = $this->getTarget($uuid);

        $this->setLanguageCode($target);

        $this->getAlias($target);

        $this->getScores($target);

        $questions = \DB::table('show_question_criteria_report_pdf')
            ->select('*')
            ->where('display_report', true)
            ->where('theme_is_visible_on_top_report', true)
            ->where('user_id', $target['user_id'])
            ->where('uuid', $uuid);
        if ($theme_id > 0)
            $questions = $questions->where('theme_id', $theme_id);
        $questions = $questions->get();

        array_push($this->responses, FusionHelper::fusionCriteriaResponse($questions, $this->language_code));
        # Regroup questions by sequence - this is used only to iterate over the questions
        // group elements by sequence_id: add elements to new array where key = sequence_id
        // and value = sequences questions
        list($mission, $questionsGroupedBySequence) = $this->setMission($questions);

        $global = GlobalScoreHelper::getGlobalScoreOneMission($uuid);

        # get sequences scores
        //$sequence_scores = $this->_getSequenceScoreFromUuid($uuid);

        # get all sequences
        $survey = Survey::find($target['survey_id'])
            ->retrieve(false, $target['scenario_id'], null, null, $target['shop_id'], false);

        $sequenceFirstLevel = SurveyItem::with('children')->where('survey_id', $target['survey_id'])->whereNull('parent_id')->orderby('order')->retrieveAll()->toArray();
        $this->sequences = $this->addSequenceline($sequenceFirstLevel, $target['survey_id'], $uuid);

        //$this->_prepareItems($survey->items, $sequence_scores);

        $pdf = $this->createCover($survey, $target);

        # Global score
        if ($mission['show_score']) {
            $pdf = $this->setGlobalScore($pdf, $global);
        }

        # Set font and Write PDV name
        if (!empty($target['shop'])) {
            if (strlen($target['shop']) > 55) {
                $pdf->SetFont('gotham-medium', 'B', 10, true);
            } else if (strlen($target['shop']) > 40) {
                $pdf->SetFont('gotham-medium', 'B', 12, true);
            } else {
                $pdf->SetFont('gotham-medium', 'B', 17, true);
            }
        }

        $pdf->SetXY($this->xCoordCellStartPosition, $this->yCoordCellStartPosition);
        $pdf->SetFillColor(66, 66, 66); // #424242
        $pdf->SetTextColor(config('pdf.colors.white')[0]);

        $pdf->MultiCell(
            $this->cellWidth,
            $this->cellMinHeight,
            '   '
                . mb_strtoupper($target['shop'], 'UTF-8'),
            1,
            'L',
            1,
            0,
            '',
            '',
            true,
            0,
            false,
            true,
            12,
            'M'
        );
        $pdf->SetXY(
            $this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition + ($this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
            ) - $this->yCoordStepBetweenCellAndItsTitle
        ); // 185
        $this->iCellBlockCounter++;
        $waveTitle = trim($target['wave']);
        if (!empty($waveTitle) && $mission['wave_name']) {
            $pdf = $this->setWaveTitle($pdf, $waveTitle);
        }

        if ($target['anonymous_mode'] === false) {
            $smiceur_name = mb_strtoupper($target['user'], 'UTF-8');
        }

        # cell smiceur name
        if (!empty($smiceur_name) && $mission['smicer_name']) {
            $pdf = $this->setSmiceurName($pdf, $smiceur_name);
        }

        # cell mission name
        if ($mission['show_mission_name']) {
            $pdf = $this->setMissionName($pdf, $target['mission']);
        }

        # cell date of visit
        if ($mission['date_visit']) {
            $visitDate = (isset($target['visit_date']))
                ? Carbon::parse($target['visit_date'])
                : Carbon::parse($target['date_status']);

            $dateFormat = 'd-m-Y';
            $visitDate = $visitDate->format($dateFormat);
            $pdf = $this->setDateVisit($pdf, $visitDate);
        }

        # cell scenario

        $pdf = $this->setScenarioTitle($pdf, $target['scenario']);

        # Add smice logo to the bottom of the page
        $bottomLogo = $this->basePath . $this->reportImagePath . 'Smice_logo_new_view_of_mystery_shopping.png';
        $pdf->Image(
            $bottomLogo,
            84,
            $pdf->getPageHeight() - 16,
            42,
            16,
            'PNG',
            '',
            '',
            true,
            300,
            'C',
            false,
            false,
            0,
            'CM',
            false,
            false
        );

        $pdf->SetFont('gotham-light', '', 6, false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Text(5, $pdf->getPageHeight() - 10, $target['id']);

        // restore margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);


        // Add page sequence & theme
        if (!is_null($global) && ($mission['show_score_2'])) {
            $pdf = $this->setSequenceThemeJobCriteriaACriteriaBPages($pdf);
        }
        $questions = \DB::table('show_question_criteria_report_pdf')
            ->select('*')
            ->where('display_report', true)
            ->where('is_visible_on_top_report', true)
            ->where('user_id', $target['user_id'])
            ->where('uuid', $uuid);
        if ($theme_id > 0)
            $questions = $questions->where('theme_id', $theme_id);
        $questions = $questions->get();
        $this->responses = [];
        array_push($this->responses, FusionHelper::fusionCriteriaResponse($questions, $this->language_code));

        # Regroup questions by sequence - this is used only to iterate over the questions
        // group elements by sequence_id: add elements to new array where key = sequence_id
        // and value = sequences questions
        list($mission, $questionsGroupedBySequence) = $this->setMission($questions);
        if ($questions) {
            $pdf = $this->setFocusQuestions($pdf, $questionsGroupedBySequence, $target, $mission);
        }
        $this->responses = [];
        $questionsGroupedBySequence = [];
        $questions = \DB::table('show_question_criteria_report_pdf')
            ->select('*')
            ->where('display_report', true)
            ->where('theme_is_visible_on_top_report', true)
            ->where('user_id', $target['user_id'])
            ->where('uuid', $uuid);
        if ($theme_id > 0)
            $questions = $questions->where('theme_id', $theme_id);
        $questions = $questions->get();

        array_push($this->responses, FusionHelper::fusionCriteriaResponse($questions, $this->language_code));

        # Regroup questions by sequence - this is used only to iterate over the questions
        // group elements by sequence_id: add elements to new array where key = sequence_id
        // and value = sequences questions
        list($mission, $questionsGroupedBySequence) = $this->setMission($questions);

        $pdf = $this->setSequences($pdf, $target, $questionsGroupedBySequence, $mission, true);

        // add signature
        $pdf = $this->setSignature($pdf, $target);

        // return report
        $pdfName = $this->returnReport($target, $pdf, $mission, ReportPdfFactory::TYPE_STANDARD, $forceupdate);

        return $pdfName;
    }
}
