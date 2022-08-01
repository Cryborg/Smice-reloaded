<?php

namespace App\Classes\Reports;

use App\Classes\Factory\ReportPdfFactory;
use App\Classes\Helpers\FusionHelper;
use App\Classes\Helpers\GlobalScoreHelper;
use App\Classes\SmiceTCPDF;
use App\Exceptions\SmiceException;
use App\Models\Survey;
use App\Models\SurveyItem;
use Carbon\Carbon;

class ReportNotCompliantPDF extends ReportPDF
{
    /**
     * @param string $uuid
     * @return string
     * @throws SmiceException
     */


    protected $formattedArr = [];

    public function generateReport(string $uuid, $forceupdate = false, $alert = null)
    {
        $target = $this->getTarget($uuid);

        $this->setLanguageCode($target);

        $this->getAlias($target);

        $this->getScores($target);

        $questions = \DB::table('show_question_criteria_report_pdf')
            ->select('*')
            ->where('display_report', true)
            ->where('user_id', $target['user_id'])
            ->where('uuid', $uuid)
            ->where('scoring', true)
            ->wherenotnull('question_score')
            ->get();

        $questions_nc = \DB::table('show_question_criteria_report_pdf')
            ->select('*')
            ->where('display_report', true)
            ->where('user_id', $target['user_id'])
            ->where('uuid', $uuid)
            ->where('scoring', true)
            ->where('question_score', '<', 100)
            ->get();

        array_push($this->responses, FusionHelper::fusionCriteriaResponse($questions, $this->language_code));
        array_push($this->responses_nc, FusionHelper::fusionCriteriaResponse($questions_nc, $this->language_code));

        # Regroup questions by sequence - this is used only to iterate over the questions
        // group elements by sequence_id: add elements to new array where key = sequence_id
        // and value = sequences questions
        list($mission, $questionsGroupedBySequence) = $this->setMission($questions);
        list($mission_nc, $questionsGroupedBySequence_nc) = $this->setMission($questions_nc);

        $global = GlobalScoreHelper::getGlobalScoreOneMission($uuid);

        # get sequences scores
        //$sequence_scores = $this->_getSequenceScoreFromUuid($uuid);

        # get all sequences
        $survey = Survey::find($target['survey_id'])
            ->retrieve(false, $target['scenario_id'], null, null, $target['shop_id'], false);
        $sequenceFirstLevel = SurveyItem::with('children')->where('survey_id', $target['survey_id'])->whereNull('parent_id')->orderby('order')->retrieveAll()->toArray();
        $this->sequences = $this->addSequenceline($sequenceFirstLevel, $target['survey_id'], $uuid);

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

        # we move the cursor in 10,170 to create the cells
        $pdf->SetXY(0, $this->yCoordCellStartPosition);
        $pdf->SetFillColor(0, 0, 0); // #000000
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->MultiCell(210, $this->cellMinHeight, config('dictionary.report_pdf.report_of_non_conformities')[$this->language_code], 0, 'C', 1, 0, 0, 150, true, 0, false, true, 12, 'M');
        # we move the cursor in 10,190 to create the cells
        $this->yCoordCellStartPosition += 10;
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
            $pdf = $this->addSequenceThemeJobCriteriaACriteriaBPages($pdf, $questions, $questionsGroupedBySequence);
        }

        $pdf = $this->setSequences($pdf, $target, $questionsGroupedBySequence_nc, $mission);

        // add signature
        $pdf = $this->setSignature($pdf, $target);

        // return report
        $pdfName = $this->returnReport($target, $pdf, $mission, ReportPdfFactory::TYPE_NOT_COMPLIANT, $forceupdate);

        return $pdfName;
    }

    protected function addNotCompliantSequenceTitle($sequence, $pdf, $questionsGroupedBySequence)
    {
        $this->level = $this->level + 3;
        $this->addNotCompliantSequence($pdf, $sequence, $questionsGroupedBySequence);
        if (isset($sequence['data'])) {
            $this->level = $this->level + 3;
            foreach ($sequence['data'] as $s) {
                $this->addNotCompliantSequenceTitle($s, $pdf, $questionsGroupedBySequence);
            }
        }
        $this->level = $this->level - 3;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $questions
     * @param $questionsGroupedBySequence
     * @return SmiceTCPDF
     */
    protected function addSequenceThemeJobCriteriaACriteriaBPages($pdf, $questions, $questionsGroupedBySequence)
    {
        $pdf->AddPage();
        # Background for sequences & themes score page
        $pdf->Rect(
            0,
            0,
            $pdf->getPageWidth(),
            $pdf->getPageHeight(),
            'DF',
            config('pdf.styles.empty_border'),
            config('pdf.colors.lightGrayRGB')
        );
        // after call: SetPageMark() - borders for text will be rendered on top of "background"(image or rectangle)
        $pdf->setPageMark();

        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, 0, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 19, true);
        $pdf->SetXY(15, 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.number_of_non_conformities_by')[$this->language_code]
                . mb_strtoupper($this->sequence_alias, 'UTF-8'),
            null,
            0,
            'L'
        );

        $this->cursor = 40;

        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->SetXY(183, 29, true);
        $pdf->MultiCell(20, 6, config('dictionary.report_pdf.number_of_criteria')[$this->language_code], 0, 'C', 1);
        foreach ($this->sequences as $sequence) {
            $this->level = 0;
            $this->addNotCompliantSequenceTitle($sequence, $pdf, $questionsGroupedBySequence);
        }


        if (!empty($this->sequences)) {
            $pdf->SetXY(70, $this->cursor + 2);
            $pdf->SetFillColor(139, 188, 38);  // blue
            $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

            $pdf->SetFont('gotham-light', '', 12, true);
            $pdf->SetTextColor(51, 51, 52);
            $pdf->SetXY(73, $this->cursor + 0.3);
            $pdf->Write(0, config('dictionary.report_pdf.compliance')[$this->language_code], null, 0);

            $pdf->SetXY(100, $this->cursor + 2);
            $pdf->SetFillColor(223, 84, 31);   // red
            $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

            $pdf->SetFont('gotham-light', '', 12, true);
            $pdf->SetTextColor(51, 51, 52);
            $pdf->SetXY(103, $this->cursor + 0.3);
            $pdf->Write(0, config('dictionary.report_pdf.noncompliance')[$this->language_code], null, 0);
        }

        if (($this->nb_seq > 15) || (count($this->theme_score) > 15)) {
            if (count($this->theme_score) > 0) {
                $pdf->AddPage();
                $this->cursor = 0;
                # Background for sequences & themes score page
                $pdf->Rect(
                    0,
                    0,
                    $pdf->getPageWidth(),
                    $pdf->getPageHeight(),
                    'DF',
                    config('pdf.styles.empty_border'),
                    config('pdf.colors.lightGrayRGB')
                );
                // after call: SetPageMark() - borders for text will be rendered on top of "background"(image or rectangle)
                $pdf->setPageMark();
            }
        } else {
            $this->cursor += 10;
        }

        if (count($this->theme_score) > 0) {
            $this->addThemeScores($pdf, $questions);
        }

        if (count($this->job_score) > 0) {
            if ($this->cursor < 250) {
                $this->cursor += 10;
            }
            $this->addJobScores($pdf, $questions);
        }

        if (count($this->criteriaa_score) > 0) {
            if ($this->cursor < 250) {
                $this->cursor += 10;
            }
            $this->addCriteriaAScores($pdf, $questions);
        }

        if (count($this->criteriab_score) > 0) {
            if ($this->cursor < 250) {
                $this->cursor += 10;
            }
            $this->addCriteriaBScores($pdf, $questions);
        }

        return $pdf;
    }

    function GetChildNC($val)
    {

        if (!empty($val)) {
            //add first level
            if (isset($val['data'])) {

                $this->formattedArr[] = $val['id'];
                foreach ($val['data'] as $data) {
                    $this->returnArr[] = $this->GetChildNC($data);  // call recursive function
                }
            } else {
                $this->formattedArr[] = $val['id'];
            }
        }
        return $this->formattedArr;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $sequence
     * @param $level
     * @param $questions
     */
    protected function addNotCompliantSequence($pdf, $sequence, $addNotCompliantSequence)
    {
        if (count($sequence) > 0 && $sequence['display_report']) {
            //get all question in this sequence and sub sequence
            $questions = [];
            $this->formattedArr = array();
            $AllSeqId = $this->GetChildNC($sequence);
            foreach ($AllSeqId as $s) {
                if (isset($addNotCompliantSequence[$s])) {
                    $questions = $questions + $addNotCompliantSequence[$s];
                }
            }
            $this->nb_seq++;
            #show score of sequence for scoring sequence only
            $s_name = $sequence['name'];
            if ($s_name) {
                $s_name = substr($s_name, 0, 80);
            }

            $pdf->SetFont('gotham-light', '', 11, true);
            $pdf->SetXY($this->level, $this->cursor);
            $pdf->SetFillColor(255, 255, 255);

            if (strlen($s_name) > 30) {
                $height = 12;
                $positionY = 3;
            } else {
                $height = 6;
                $positionY = 0;
            }

            $pdf->MultiCell(105 - $this->level, $height, mb_strtoupper($s_name, 'UTF-8'), 0, 'R', 1);

            $blue = $red = 0;
            $count = count($questions);
            foreach ($questions as $question) {
                if ($question['question_score'] >= 100) {
                    $blue++;
                } else {
                    $red++;
                }
            }

            if ($count) {
                $blueWidth = $redWidth = 0;
                if ($blue) {
                    $blueWidth = 80 / $count * $blue;
                    $pdf->SetXY(105, $this->cursor);
                    $pdf->SetFillColor(139, 188, 38);  // blue
                    $pdf->MultiCell($blueWidth, $height, null, 0, 'C', 1, 0);
                    $pdf->SetXY(103 + $blueWidth / 2, $this->cursor + $positionY);
                    $pdf->Write(0, $blue, null, 0, '');
                }

                if ($red) {
                    $redWidth = 80 / $count * $red;
                    $pdf->SetXY(105 + $blueWidth, $this->cursor);
                    $pdf->SetFillColor(223, 84, 31);  #red
                    $pdf->MultiCell($redWidth, $height, null, 0, 'C', 1, 0);
                    $pdf->SetXY(103 + $blueWidth + $redWidth / 2, $this->cursor + $positionY);
                    $pdf->Write(0, $red, null, 0, '');
                }
            } else { // add space for sequence with more than one line
                $pdf->SetXY(105, $this->cursor);
                $pdf->SetFillColor(229, 229, 229);
                $pdf->MultiCell(80, $height, null, 0, 'C', 1, 0);
            }

            $pdf->SetXY(185, $this->cursor + $positionY);
            $pdf->Write(0, $count, null, 0, 'C');

            $this->cursor += 1 + $height;

            if ($this->cursor > 250) {
                $pdf->AddPage();
                $this->cursor = 0;
                # Background for sequences & themes score page
                $pdf->Rect(
                    0,
                    0,
                    $pdf->getPageWidth(),
                    $pdf->getPageHeight(),
                    'DF',
                    config('pdf.styles.empty_border'),
                    config('pdf.colors.lightGrayRGB')
                );
                // after call: SetPageMark() - borders for text will be rendered on top of "background"(image or rectangle)
                $pdf->setPageMark();
                $this->cursor = 10;
            }
        }
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $questions
     */
    private function addThemeScores($pdf, $questions)
    {
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, $this->cursor, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 19, true);
        $pdf->SetXY(15, $this->cursor + 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.number_of_non_conformities_by')[$this->language_code] . mb_strtoupper($this->theme_alias, 'UTF-8'),
            null,
            0,
            'L'
        );
        $pdf->Ln();
        $pdf->SetTextColor(116, 141, 150);
        $pdf->SetFont('gotham-light', '', 14, true);
        $pdf->Write(0, config('dictionary.report_pdf.mission_result_txt')[$this->language_code], null, 0, 'L');
        $style = ['width' => 0.1, 'color' => [140, 148, 157]];
        $pdf->Line(0, $this->cursor + 30, 140, $this->cursor + 30, $style);
        $this->cursor += 40;

        $pdf->SetFillColor(config('pdf.colors.lightGrayRGB')[0], config('pdf.colors.lightGrayRGB')[1], config('pdf.colors.lightGrayRGB')[2]);
        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->SetXY(183, $this->cursor);
        $pdf->MultiCell(20, 6, config('dictionary.report_pdf.number_of_criteria')[$this->language_code], 0, 'C', 1);

        $this->cursor += 12;
        foreach ($this->theme_score as $theme_name => $score) {
            $this->addThemeScore($pdf, $theme_name, $questions);
        }

        $pdf->SetXY(70, $this->cursor + 2);
        $pdf->SetFillColor(139, 188, 38);  // blue
        $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

        $pdf->SetFont('gotham-light', '', 12, true);
        $pdf->SetXY(73, $this->cursor + 0.3);
        $pdf->Write(0, config('dictionary.report_pdf.compliance')[$this->language_code], null, 0);

        $pdf->SetXY(100, $this->cursor + 2);
        $pdf->SetFillColor(223, 84, 31);   // red
        $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

        $pdf->SetFont('gotham-light', '', 12, true);
        $pdf->SetXY(103, $this->cursor + 0.3);
        $pdf->Write(0, config('dictionary.report_pdf.noncompliance')[$this->language_code], null, 0);
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $theme_name
     * @param $questions
     */
    private function addThemeScore($pdf, $theme_name, $questions)
    {
        $t_name = substr($theme_name, 0, 40);
        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetXY(10, $this->cursor);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(47, 59, 67);

        if (strlen($t_name) > 35) {
            $height = 12;
            $positionY = 3;
        } else {
            $height = 6;
            $positionY = 0;
        }

        $pdf->MultiCell(95, $height, mb_strtoupper($t_name, 'UTF-8'), 0, 'R', 1);

        $blue = $red = $count = 0;
        foreach ($questions as $question) {
            $theme = json_decode($question['theme_name']);
            if (isset($theme->{$this->language_code}) && $theme->{$this->language_code} == $theme_name) {
                $count++;
                if ($question['question_score'] >= 100) {
                    $blue++;
                } else {
                    $red++;
                }
            }
        }

        if ($count) {
            $blueWidth = $redWidth = 0;
            if ($blue) {
                $blueWidth = 80 / $count * $blue;
                $pdf->SetXY(105, $this->cursor);
                $pdf->SetFillColor(139, 188, 38);  // blue
                $pdf->MultiCell($blueWidth, $height, null, 0, 'C', 1, 0);
                $pdf->SetXY(103 + $blueWidth / 2, $this->cursor + $positionY);
                $pdf->Write(0, $blue, null, 0, '');
            }

            if ($red) {
                $redWidth = 80 / $count * $red;
                $pdf->SetXY(105 + $blueWidth, $this->cursor);
                $pdf->SetFillColor(223, 84, 31);  #CE5151
                $pdf->MultiCell($redWidth, $height, null, 0, 'C', 1, 0);
                $pdf->SetXY(103 + $blueWidth + $redWidth / 2, $this->cursor + $positionY);
                $pdf->Write(0, $red, null, 0, '');
            }
        }

        $pdf->SetXY(185, $this->cursor + $positionY);
        $pdf->Write(0, $count, null, 0, 'C');

        $this->cursor += 1 + $height;

        if ($this->cursor > 250) {
            $pdf->AddPage();
            $this->cursor = 0;
            # Background for sequences & themes score page
            $pdf->Rect(
                0,
                0,
                $pdf->getPageWidth(),
                $pdf->getPageHeight(),
                'DF',
                config('pdf.styles.empty_border'),
                config('pdf.colors.lightGrayRGB')
            );
            // after call: SetPageMark() - borders for text will be rendered on top of "background"(image or rectangle)
            $pdf->setPageMark();
            $this->cursor = 10;
        }
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $questions
     */
    private function addJobScores($pdf, $questions)
    {
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, $this->cursor, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 19, true);
        $pdf->SetXY(15, $this->cursor + 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.number_of_non_conformities_by')[$this->language_code] . mb_strtoupper($this->job_alias, 'UTF-8'),
            null,
            0,
            'L'
        );
        $pdf->Ln();
        $pdf->SetTextColor(116, 141, 150);
        $pdf->SetFont('gotham-light', '', 14, true);
        $pdf->Write(0, config('dictionary.report_pdf.mission_result_txt')[$this->language_code], null, 0, 'L');
        $style = ['width' => 0.1, 'color' => [140, 148, 157]];
        $pdf->Line(0, $this->cursor + 30, 140, $this->cursor + 30, $style);
        $this->cursor += 40;

        $pdf->SetFillColor(config('pdf.colors.lightGrayRGB')[0], config('pdf.colors.lightGrayRGB')[1], config('pdf.colors.lightGrayRGB')[2]);
        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->SetXY(183, $this->cursor);
        $pdf->MultiCell(20, 6, config('dictionary.report_pdf.number_of_criteria')[$this->language_code], 0, 'C', 1);

        $this->cursor += 12;
        foreach ($this->job_score as $job_name => $score) {
            $this->addJobScore($pdf, $job_name, $questions);
        }

        $pdf->SetXY(70, $this->cursor + 2);
        $pdf->SetFillColor(139, 188, 38);  // blue
        $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

        $pdf->SetFont('gotham-light', '', 12, true);
        $pdf->SetXY(73, $this->cursor + 0.3);
        $pdf->Write(0, config('dictionary.report_pdf.compliance')[$this->language_code], null, 0);

        $pdf->SetXY(100, $this->cursor + 2);
        $pdf->SetFillColor(223, 84, 31);   // red
        $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

        $pdf->SetFont('gotham-light', '', 12, true);
        $pdf->SetXY(103, $this->cursor + 0.3);
        $pdf->Write(0, config('dictionary.report_pdf.noncompliance')[$this->language_code], null, 0);
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $job_name
     * @param $questions
     */
    private function addJobScore($pdf, $job_name, $questions)
    {
        $job_name = substr($job_name, 0, 40);
        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetXY(10, $this->cursor);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(47, 59, 67);

        if (strlen($job_name) > 35) {
            $height = 12;
            $positionY = 3;
        } else {
            $height = 6;
            $positionY = 0;
        }

        $pdf->MultiCell(95, $height, mb_strtoupper($job_name, 'UTF-8'), 0, 'R', 1);

        $blue = $red = $count = 0;
        foreach ($questions as $question) {
            $job = json_decode($question['job_name']);
            if (isset($job->{$this->language_code}) && $job->{$this->language_code} == $job_name) {
                $count++;
                if ($question['question_score'] >= 100) {
                    $blue++;
                } else {
                    $red++;
                }
            }
        }

        if ($count) {
            $blueWidth = $redWidth = 0;
            if ($blue) {
                $blueWidth = 80 / $count * $blue;
                $pdf->SetXY(105, $this->cursor);
                $pdf->SetFillColor(139, 188, 38);  // blue
                $pdf->MultiCell($blueWidth, $height, null, 0, 'C', 1, 0);
                $pdf->SetXY(103 + $blueWidth / 2, $this->cursor + $positionY);
                $pdf->Write(0, $blue, null, 0, '');
            }

            if ($red) {
                $redWidth = 80 / $count * $red;
                $pdf->SetXY(105 + $blueWidth, $this->cursor);
                $pdf->SetFillColor(223, 84, 31);  #CE5151
                $pdf->MultiCell($redWidth, $height, null, 0, 'C', 1, 0);
                $pdf->SetXY(103 + $blueWidth + $redWidth / 2, $this->cursor + $positionY);
                $pdf->Write(0, $red, null, 0, '');
            }
        }

        $pdf->SetXY(185, $this->cursor + $positionY);
        $pdf->Write(0, $count, null, 0, 'C');

        $this->cursor += 1 + $height;

        if ($this->cursor > 250) {
            $pdf->AddPage();
            $this->cursor = 0;
            # Background for sequences & themes score page
            $pdf->Rect(
                0,
                0,
                $pdf->getPageWidth(),
                $pdf->getPageHeight(),
                'DF',
                config('pdf.styles.empty_border'),
                config('pdf.colors.lightGrayRGB')
            );
            // after call: SetPageMark() - borders for text will be rendered on top of "background"(image or rectangle)
            $pdf->setPageMark();
            $this->cursor = 10;
        }
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $questions
     */
    private function addCriteriaAScores($pdf, $questions)
    {
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, $this->cursor, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 19, true);
        $pdf->SetXY(15, $this->cursor + 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.number_of_non_conformities_by')[$this->language_code] . mb_strtoupper($this->criteria_a_alias, 'UTF-8'),
            null,
            0,
            'L'
        );
        $pdf->Ln();
        $pdf->SetTextColor(116, 141, 150);
        $pdf->SetFont('gotham-light', '', 14, true);
        $pdf->Write(0, config('dictionary.report_pdf.mission_result_txt')[$this->language_code], null, 0, 'L');
        $style = ['width' => 0.1, 'color' => [140, 148, 157]];
        $pdf->Line(0, $this->cursor + 30, 140, $this->cursor + 30, $style);
        $this->cursor += 40;

        $pdf->SetFillColor(config('pdf.colors.lightGrayRGB')[0], config('pdf.colors.lightGrayRGB')[1], config('pdf.colors.lightGrayRGB')[2]);
        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->SetXY(183, $this->cursor);
        $pdf->MultiCell(20, 6, config('dictionary.report_pdf.number_of_criteria')[$this->language_code], 0, 'C', 1);

        $this->cursor += 12;
        foreach ($this->criteriaa_score as $ca_name => $ca_score) {
            $this->addCriteriaAScore($pdf, $ca_name, $questions);
        }

        $pdf->SetXY(70, $this->cursor + 2);
        $pdf->SetFillColor(139, 188, 38);  // blue
        $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

        $pdf->SetFont('gotham-light', '', 12, true);
        $pdf->SetXY(73, $this->cursor + 0.3);
        $pdf->Write(0, config('dictionary.report_pdf.compliance')[$this->language_code], null, 0);

        $pdf->SetXY(100, $this->cursor + 2);
        $pdf->SetFillColor(223, 84, 31);   // red
        $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

        $pdf->SetFont('gotham-light', '', 12, true);
        $pdf->SetXY(103, $this->cursor + 0.3);
        $pdf->Write(0, config('dictionary.report_pdf.noncompliance')[$this->language_code], null, 0);
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $ca_name
     * @param $questions
     */
    private function addCriteriaAScore($pdf, $ca_name, $questions)
    {
        $ca_name = substr($ca_name, 0, 40);
        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetXY(10, $this->cursor);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(47, 59, 67);

        if (strlen($ca_name) > 35) {
            $height = 12;
            $positionY = 3;
        } else {
            $height = 6;
            $positionY = 0;
        }

        $pdf->MultiCell(95, $height, mb_strtoupper($ca_name, 'UTF-8'), 0, 'R', 1);

        $blue = $red = $count = 0;
        foreach ($questions as $question) {
            $criteria_a = json_decode($question['criteria_a_name']);
            if (isset($criteria_a->{$this->language_code}) && $criteria_a->{$this->language_code} == $ca_name) {
                $count++;
                if ($question['question_score'] >= 100) {
                    $blue++;
                } else {
                    $red++;
                }
            }
        }

        if ($count) {
            $blueWidth = $redWidth = 0;
            if ($blue) {
                $blueWidth = 80 / $count * $blue;
                $pdf->SetXY(105, $this->cursor);
                $pdf->SetFillColor(139, 188, 38);  // blue
                $pdf->MultiCell($blueWidth, $height, null, 0, 'C', 1, 0);
                $pdf->SetXY(103 + $blueWidth / 2, $this->cursor + $positionY);
                $pdf->Write(0, $blue, null, 0, '');
            }

            if ($red) {
                $redWidth = 80 / $count * $red;
                $pdf->SetXY(105 + $blueWidth, $this->cursor);
                $pdf->SetFillColor(223, 84, 31);  #CE5151
                $pdf->MultiCell($redWidth, $height, null, 0, 'C', 1, 0);
                $pdf->SetXY(103 + $blueWidth + $redWidth / 2, $this->cursor + $positionY);
                $pdf->Write(0, $red, null, 0, '');
            }
        }

        $pdf->SetXY(185, $this->cursor + $positionY);
        $pdf->Write(0, $count, null, 0, 'C');

        $this->cursor += 1 + $height;

        if ($this->cursor > 250) {
            $pdf->AddPage();
            $this->cursor = 0;
            # Background for sequences & themes score page
            $pdf->Rect(
                0,
                0,
                $pdf->getPageWidth(),
                $pdf->getPageHeight(),
                'DF',
                config('pdf.styles.empty_border'),
                config('pdf.colors.lightGrayRGB')
            );
            // after call: SetPageMark() - borders for text will be rendered on top of "background"(image or rectangle)
            $pdf->setPageMark();
            $this->cursor = 10;
        }
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $questions
     * @return SmiceTCPDF
     */
    private function addCriteriaBScores($pdf, $questions)
    {
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, $this->cursor, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 19, true);
        $pdf->SetXY(15, $this->cursor + 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.number_of_non_conformities_by')[$this->language_code]
                . mb_strtoupper($this->criteria_b_alias, 'UTF-8'),
            null,
            0,
            'L'
        );
        $pdf->Ln();
        $pdf->SetTextColor(116, 141, 150);
        $pdf->SetFont('gotham-light', '', 14, true);
        $pdf->Write(0, config('dictionary.report_pdf.mission_result_txt')[$this->language_code], null, 0, 'L');
        $style = ['width' => 0.1, 'color' => [140, 148, 157]];
        $pdf->Line(0, $this->cursor + 30, 140, $this->cursor + 30, $style);
        $this->cursor += 40;

        $pdf->SetFillColor(config('pdf.colors.lightGrayRGB')[0], config('pdf.colors.lightGrayRGB')[1], config('pdf.colors.lightGrayRGB')[2]);
        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->SetXY(183, $this->cursor);
        $pdf->MultiCell(20, 6, config('dictionary.report_pdf.number_of_criteria')[$this->language_code], 0, 'C', 1);

        $this->cursor += 12;
        foreach ($this->criteriab_score as $cb_name => $cb_score) {
            $this->addCriteriaBScore($pdf, $cb_name, $questions);
        }

        $pdf->SetXY(70, $this->cursor + 2);
        $pdf->SetFillColor(139, 188, 38);  // blue
        $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

        $pdf->SetFont('gotham-light', '', 12, true);
        $pdf->SetXY(73, $this->cursor + 0.3);
        $pdf->Write(0, config('dictionary.report_pdf.compliance')[$this->language_code], null, 0);

        $pdf->SetXY(100, $this->cursor + 2);
        $pdf->SetFillColor(223, 84, 31);   // red
        $pdf->MultiCell(3, 3, '', 0, 'C', 1, 0);

        $pdf->SetFont('gotham-light', '', 12, true);
        $pdf->SetXY(103, $this->cursor + 0.3);
        $pdf->Write(0, config('dictionary.report_pdf.noncompliance')[$this->language_code], null, 0);
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $cb_name
     * @param $questions
     */
    private function addCriteriaBScore($pdf, $cb_name, $questions)
    {
        $cb_name = substr($cb_name, 0, 40);
        $pdf->SetFont('gotham-light', '', 11, true);
        $pdf->SetXY(10, $this->cursor);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(47, 59, 67);

        if (strlen($cb_name) > 35) {
            $height = 12;
            $positionY = 3;
        } else {
            $height = 6;
            $positionY = 0;
        }

        $pdf->MultiCell(95, $height, mb_strtoupper($cb_name, 'UTF-8'), 0, 'R', 1);

        $blue = $red = $count = 0;
        foreach ($questions as $question) {
            $criteria_b = json_decode($question['criteria_b_name']);
            if (isset($criteria_b->{$this->language_code}) && $criteria_b->{$this->language_code} == $cb_name) {
                $count++;
                if ($question['question_score'] >= 100) {
                    $blue++;
                } else {
                    $red++;
                }
            }
        }

        if ($count) {
            $blueWidth = $redWidth = 0;
            if ($blue) {
                $blueWidth = 80 / $count * $blue;
                $pdf->SetXY(105, $this->cursor);
                $pdf->SetFillColor(139, 188, 38);  // blue
                $pdf->MultiCell($blueWidth, $height, null, 0, 'C', 1, 0);
                $pdf->SetXY(103 + $blueWidth / 2, $this->cursor + $positionY);
                $pdf->Write(0, $blue, null, 0, '');
            }

            if ($red) {
                $redWidth = 80 / $count * $red;
                $pdf->SetXY(105 + $blueWidth, $this->cursor);
                $pdf->SetFillColor(223, 84, 31);  #CE5151
                $pdf->MultiCell($redWidth, $height, null, 0, 'C', 1, 0);
                $pdf->SetXY(103 + $blueWidth + $redWidth / 2, $this->cursor + $positionY);
                $pdf->Write(0, $red, null, 0, '');
            }
        }

        $pdf->SetXY(185, $this->cursor + $positionY);
        $pdf->Write(0, $count, null, 0, 'C');

        $this->cursor += 1 + $height;

        if ($this->cursor > 250) {
            $pdf->AddPage();
            $this->cursor = 0;
            # Background for sequences & themes score page
            $pdf->Rect(
                0,
                0,
                $pdf->getPageWidth(),
                $pdf->getPageHeight(),
                'DF',
                config('pdf.styles.empty_border'),
                config('pdf.colors.lightGrayRGB')
            );
            // after call: SetPageMark() - borders for text will be rendered on top of "background"(image or rectangle)
            $pdf->setPageMark();
            $this->cursor = 10;
        }
    }
}
