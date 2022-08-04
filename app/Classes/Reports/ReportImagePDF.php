<?php

namespace App\Classes\Reports;

use App\Classes\Factory\ReportPdfFactory;
use App\Classes\Helpers\FusionHelper;
use App\Classes\Helpers\GlobalScoreHelper;
use App\Classes\SmiceTCPDF;
use App\Exceptions\SmiceException;
use App\Http\Shops\Models\Shop;
use App\Models\Color;
use App\Models\Survey;
use App\Models\SurveyItem;
use Carbon\Carbon;

class ReportImagePDF extends ReportPDF
{
    /**
     * @param string $uuid
     * @return string
     * @throws SmiceException
     */
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
            ->get();

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

        $pdf = $this->createCover($survey, $target);

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

        $pdf->MultiCell($this->cellWidth, $this->cellMinHeight, '   '
            . mb_strtoupper($target['shop'], 'UTF-8'), 1, 'L', 1, 0, '', '', true, 0, false, true, 12, 'M'
        );
        $pdf->SetXY(
            $this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition + (
                $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
            ) - $this->yCoordStepBetweenCellAndItsTitle
        ); // 185
        $this->iCellBlockCounter++;

        # cell address
        # bottom part of page
        $shopData = Shop::select(['name', 'info', 'street', 'postal_code', 'city'])
            ->where('id', $target['shop_id'])->first();
        $pdf = $this->setAddress($pdf, $shopData);

        # cell date of visit
        if ($mission['date_visit']) {
            $visitDate = (isset($target['visit_date']))
                ? Carbon::parse($target['visit_date'])
                : Carbon::parse($target['date_status']);

            $dateFormat = 'd-m-Y';
            $visitDate = $visitDate->format($dateFormat);
            $pdf = $this->setDateVisit($pdf, $visitDate);
        }

        # info field of shop
        if (!empty($shopData->info)) {
            $pdf = $this->setShopInfo($pdf, $shopData);
        }

        # Add smice logo to the bottom of the page
        $bottomLogo = $this->basePath . $this->reportImagePath . 'Smice_logo_new_view_of_mystery_shopping.png';
        $pdf->Image(
            $bottomLogo, 84,
            $pdf->getPageHeight() - 16,
            42, 16, 'PNG', '', '', true, 300, 'C', false, false, 0, 'CM', false, false
        );

        $pdf->SetFont('gotham-light', '', 6, false);
        $pdf->SetTextColor(0,0,0);
        $pdf->Text(5, $pdf->getPageHeight() - 10, $target['id']);

        // restore margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        $pdf = $this->setSequences($pdf, $target, $questionsGroupedBySequence, $mission);

        // return report
        $pdfName = $this->returnReport($target, $pdf, $mission, ReportPdfFactory::TYPE_IMAGE, $forceupdate);

        return $pdfName;
    }

    protected function addSequence ($sequence, $questionsGroupedBySequence, $colors, $target, $pdf, $mission) {

        //check if question have subsequence with question
        $present = $this->QuestionPresentInSequence($sequence, $questionsGroupedBySequence);
        if ($present) {
            if (isset($sequence['data'])) {
                foreach ($sequence['data'] as $s) {
                    $this->addSequence($s, $questionsGroupedBySequence, $colors, $target, $pdf, $mission);
                }
            }
            else {
                // Add page for new sequence
                $sequenceName = $sequence['name'];

                $questions = [];
                if (isset($questionsGroupedBySequence[$sequence['id']])) {
                    $questions = $questionsGroupedBySequence[$sequence['id']];
                }

                $pdf = $this->setQuestions($pdf, $questions, $sequence, $colors, $sequenceName, $target, $mission);

                $pdf->SetXY(10, $this->y + 5);

                if (count($this->imageReport) > 0) {
                    $pdf = $this->setImageReport($pdf, null, $sequenceName);
                }
            }

        }
}

    /**
     * @param SmiceTCPDF $pdf
     * @param $target
     * @param $questionsGroupedBySequence
     * @return mixed
     */
    protected function setSequences($pdf, $target, $questionsGroupedBySequence, $mission)
    {
        $colors = Color::where('survey_id', $target['survey_id'])->get();
        foreach ($this->sequences as $sequence) {
            $this->addSequence($sequence, $questionsGroupedBySequence, $colors, $target, $pdf, $mission);
        }

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $questions
     * @param $sequence
     * @param $type
     * @param $colors
     * @param $sequenceName
     * @param $target
     * @return SmiceTCPDF
     */
    protected function setQuestions($pdf, $questions, $sequence, $colors, $sequenceName, $target, $mission)
    {
        $questionNumber = 1;
        $coordinates = ['x' => 0, 'y' => 0];
        foreach ($questions as $question) {
            $question_name = json_decode($question['question_name'])->{$this->language_code};
            $question_name = $sequence['numbering'] . $questionNumber . ' ' . $question_name;
            $question_info = json_decode($question['question_info'])->{$this->language_code};

            $pdf->SetTextColor(config('pdf.colors.blackRGB')[0]);
            $questionId = $question['id_question'];
            $response = ($this->responses[0])[$questionId][0];


            $userComment = '';
            if (isset($response['comment'])) {
                $userComment = $response['comment'];
            }

            $answerStyle = [
                'font' => 'gotham-medium',
                'background' => config('pdf.colors.background_answer'),
                'text_color' => config('pdf.colors.white'),
                'align' => 'C',
                'width' => 35,
                'font_size' => 12,
                'font_style' => ''
            ];


            $coordinates = array_merge($coordinates, [
                'question_width' => 0,
                'question_bottom' => 0,
                'left_bottom' => 0,
                'right_bottom' => 0
            ]);

            $response_detail = [];
            $coordinates['score_bottom'] = 0;
            foreach (($this->responses[0])[$questionId] as $response) {
                list($pdf, $response_detail) = $this->setResponse($pdf, $response, $question, $question_name, $userComment, $answerStyle, $question_info, $mission);
            }

            $questionNumber++;
        }

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $response
     * @param $question
     * @param $questionName
     * @param $userComment
     * @param $type
     * @param $answerStyle
     * @param $coordinates
     * @param $question_info
     * @return array
     */
    protected function setResponse($pdf, $response, $question, $questionName, $userComment, $answerStyle,
                                   $question_info, $mission)
    {
        $response_detail = [
            'id_response' => $response['id_reponse'],
            'response' => $response['reponse'],
            'type' => $response['type'],
            'date' => $response['date'],
            'comment' => $response['comment'],
            'image' => $response['image'],
        ];

        // collect images attached to user response
        if (isset($response['image'])) {
            foreach ($response['image'] as $img) {
                $this->collectImages($img, $questionName, $userComment, $question);
            }
        }

        $scoring = 0;

        # answer box
        if ($scoring == 1) {
            list($pdf, $coordinatesUpdate) = $this->setAnswerBox($pdf, $answerStyle, $response_detail);
            $this->coordinates = array_merge($this->coordinates, $coordinatesUpdate);
        }

        return [$pdf, $response_detail];
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $shopData
     * @return SmiceTCPDF
     */
    private function setAddress($pdf, $shopData)
    {
        $pdf->SetXY($this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition
            + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
            - $this->yCoordStepBetweenCellAndItsTitle
        ); // 185
        $pdf->SetFont('gotham-medium', 'B', 17, true);
        $pdf->SetTextColor(94, 94, 94); // #5E5E5E
        $pdf->Write(0, config('dictionary.report_pdf.address_txt')[$this->language_code], null, 0, 'L');
        $pdf->SetXY($this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition
            + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
        ); // 195
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetFillColor(31, 111, 120); // #1F6F78
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $shop_address = mb_strtoupper(
            $shopData->street . ' - ' . $shopData->postal_code . ' ' . $shopData->city, 'UTF-8'
        );
        $pdf->MultiCell(
            $this->cellWidth,
            $this->cellMinHeight,
            '   ' . $shop_address,
            1, 'L', 1, 0, '', '', true, 0, false, true, 12, 'M'
        );
        $this->iCellBlockCounter++;

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $shopData
     * @return SmiceTCPDF
     */
    private function setShopInfo($pdf, $shopData)
    {
        $pdf->SetXY($this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition
            + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
            - $this->yCoordStepBetweenCellAndItsTitle
        ); // 215
        $pdf->SetFont('gotham-medium', 'B', 17, true);
        $pdf->SetTextColor(94, 94, 94); // #5E5E5E
        $pdf->Write(
            0, mb_strtoupper(config('dictionary.report_pdf.reseauTxt')[$this->language_code], 'UTF-8'), null, 0, 'L'
        );
        $pdf->SetXY($this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition
            + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
        ); // 225
        $pdf->SetFont('Helvetica', 'B', 17);
        $pdf->SetFillColor(73, 85, 106); // #49556A
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->MultiCell(
            $this->cellWidth,
            $this->cellMinHeight,
            '   ' . mb_strtoupper($shopData->info, 'UTF-8'),
            1, 'L', 1, 0, '', '', true, 0, false, true, 12, 'M'
        );

        return $pdf;
    }
}
