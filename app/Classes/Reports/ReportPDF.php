<?php

namespace App\Classes\Reports;

use App\Classes\Helpers\ColorsHelper;
use App\Classes\Services\SmiceService;
use App\Classes\SmiceTCPDF;
use App\Exceptions\SmiceException;
use App\Models\Alias;
use App\Models\Color;
use App\Models\CriteriaA;
use App\Models\CriteriaB;
use App\Models\Mission;
use App\Models\PassageProof;
use App\Models\SelectedColor;
use App\Models\Sequence;
use App\Models\Society;
use App\Models\SurveyItem;
use App\Models\Theme;
use App\Models\User;
use App\Models\WaveTarget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use \Koerel\PdfUnite\PdfUnite;
use Illuminate\Support\Facades\Storage;

abstract class ReportPDF extends SmiceService implements ReportPDFInterface
{
    protected $language_code = null;

    protected $sequences = [];
    protected $sequences_3d = [];
    protected $cursor;

    protected $x = 0;
    protected $y = 0;
    protected $nb_seq = 0;
    protected $level = 0;
    protected $firstlevel = 0;
    protected $secondlevel = 0;
    protected $thirdlevel = 0;

    protected $sequence_alias = 'sequence';
    protected $theme_alias = 'theme';
    protected $criteria_a_alias = 'critere a';
    protected $criteria_b_alias = 'critere b';
    protected $shop_alias = 'point de vente';
    protected $wave_alias = 'vague';
    protected $tag_alias = 'tag';
    protected $job_alias = 'metier';

    protected $basePath;
    protected $reportImagePath = '/resources/reports/pdf/mission_report/img/';

    protected $cellWidth = 170; // cell in title page in bottom part of page
    protected $cellMinHeight = 12; // Cell minimum height.
    protected $xCoordCellStartPosition = 20;
    protected $yCoordCellStartPosition = 165;
    protected $yCoordStepBetweenCellsWithTitle = 25;
    protected $yCoordStepBetweenCellAndItsTitle = 10;
    protected $iCellBlockCounter = 1;

    protected $theme_score = [];
    protected $job_score = [];
    protected $criteriaa_score = [];
    protected $criteriab_score = [];

    protected $responses = [];
    protected $responses_nc = [];
    protected $imageReport = [];

    protected $parent_name = null;
    protected $sequence_order = null;
    protected $questioninsequence = false;
    protected $filter_theme = false;
    protected $coordinates = [];
    protected $response_detail = [];
    /**
     * ReportPDF constructor.
     */
    public function __construct(User $user = null)
    {
        parent::__construct($user);
        $this->basePath = base_path();
    }

    /**
     * @param string $uuid
     * @return string
     * @throws SmiceException
     */
    public function generateReport(string $uuid, $forceupdate = false, $alert = null)
    {
    }

    public function getTarget(string $uuid)
    {
        $target = \DB::table('show_targets')
            ->where('uuid', $uuid)
            ->where('status', 'read')
            ->first();

        if ($target === NULL) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE,
                'Survey was not found or was not read'
            );
        }

        return $target;
    }

    public function setLanguageCode($target)
    {
        if (!$this->user && !is_null($target['user_id'])) {
            $user = User::find($target['user_id']);
            $this->language_code = $user->language->code;
        } else {
            $this->language_code = $this->user->language->code;
        }
    }

    public function getScores($target)
    {
        # get theme score
        $this->theme_score = $this->_getReportThemeScore($target['id'], $this->language_code);

        # get job score
        $this->job_score = $this->_getReportJobScore($target['id'], $this->language_code);

        # get critere A score
        $this->criteriaa_score = $this->_getReportCAScore($target['id'], $this->language_code);

        # get critere B score
        $this->criteriab_score = $this->_getReportCBScore($target['id'], $this->language_code);
    }

    public function createCover($survey, $target)
    {
        # Generate PDF report
        $pdf = new SmiceTCPDF();

        # construct report - start
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);

        // set margins for Title page
        $pdf->SetMargins(0, 0, 0);

        # set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Smice');
        $pdf->SetTitle('Report Smice');
        $pdf->SetSubject('Mission(s) Summarized Report');

        // Add Title page
        $pdf->AddPage();

        $pdf->SetAutoPageBreak(false);

        # Middle image back to top

        $questLogo = str_replace('api.smice.com', 'ik.imagekit.io/smice', $survey->image);
        $questLogo = str_replace('smice/images/', 'smice/images/tr:n-pdf_report/', $questLogo);

        if (!empty($questLogo)) {
            $pdf = $this->setLogo($pdf, $questLogo);
        }

        # Client logo placeholder
        $pdf->Rect(79, 0, 52, 72, 'DF', config('pdf.styles.empty_border'), config('pdf.colors.white'));
        $pdf->Ln();

        # Rest of title page fill with #E5E5E5
        $currX = $pdf->GetX();
        $currY = $pdf->GetY();
        $pdf->SetXY(0, 150, true);
        $pdf->Rect(
            0,
            150,
            $pdf->getPageWidth(),
            $pdf->getPageHeight() - 150,
            'DF',
            config('pdf.styles.empty_border'),
            config('pdf.colors.lightGrayRGB')
        );
        $pdf->SetXY($currX, $currY);
        // after call: SetPageMark() - borders for text will be rendered on top of "background"(image or rectangle)
        $pdf->setPageMark();

        # Add client logo after call SetPageMark() method
        $society = Society::find($target['society_id']);
        $clientsLogo = str_replace('api.smice.com', 'ik.imagekit.io/smice', $society->logo);
        $clientsLogo = str_replace('smice/images/', 'smice/images/tr:n-pdf_report/', $clientsLogo);
        if ($clientsLogo !== NULL && $clientsLogo !== "") {
            // if clients logo contain transparency - tcpdf will render it with this transparency
            // to render image opaque it must be processed before insertion
            // setAlpha() method not working with images where embedded transparency
            $pdf->Image(
                $clientsLogo,
                0,
                0,
                51,
                71,
                '',
                '',
                '',
                true,
                300,
                'C',
                false,
                false,
                0,
                'CB',
                false,
                false
            );
        }

        // if visit_date is set, we read it else we read report send date

        # Title of the mission
        # Plus sign
        $currX = $pdf->GetX();
        $currY = $pdf->GetY();

        $pdf->SetFont('helvetica', 'B', 48, true);
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->SetXY(54, 85, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetXY($currX, $currY);

        # Title text
        $pdf->SetFont('helvetica', '', 28);
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->Write(207, config('dictionary.report_pdf.program_txt')[$this->language_code], null, 0, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 25);
        $pdf->Write(228, json_decode($target['program'])->{$this->language_code}, null, 0, 'C');
        $pdf->Ln(2);

        return $pdf;
    }

    /**
     * @param $target
     */
    public function getAlias($target)
    {
        # Get alias ex: shop for clubmed is "village"
        $alias = Alias::where('society_id', $target['society_id'])->first()->toArray();
        if ($alias['sequence'][$this->language_code] !== '') {
            $this->sequence_alias = $this->TranslateString($alias['sequence']);
        }

        if ($alias['theme'][$this->language_code] !== '') {
            $this->theme_alias = $this->TranslateString($alias['theme']);
        }

        if ($alias['criteria_a'][$this->language_code] !== '') {
            $this->criteria_a_alias = $this->TranslateString($alias['criteria_a']);
        }

        if ($alias['criteria_b'][$this->language_code] !== '') {
            $this->criteria_b_alias = $this->TranslateString($alias['criteria_b']);
        }

        if ($alias['shop'][$this->language_code] !== '') {
            $this->shop_alias = $this->TranslateString($alias['shop']);
        }

        if ($alias['wave'][$this->language_code] !== '') {
            $this->wave_alias = $this->TranslateString($alias['wave']);
        }

        if ($alias['tag'][$this->language_code] !== '') {
            $this->tag_alias = $this->TranslateString($alias['tag']);
        }

        if ($alias['job'][$this->language_code] !== '') {
            $this->job_alias = $this->TranslateString($alias['job']);
        }
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param string $name
     */
    public function addShopFooter(SmiceTCPDF $pdf, $name, $shiftUp = false)
    {
        $topPosition = $pdf->getPageHeight() - 23;
        $topPosition = ($shiftUp) ? $topPosition - 5 : $topPosition;
        $textWidth = $pdf->getPageWidth() - 20;
        $style = ['width' => 0.1, 'color' => [140, 148, 157]];
        $pdf->Line(0, $topPosition, $textWidth, $topPosition, $style);

        $pdf->SetFont('gotham-medium', 'B', 10, true);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->MultiCell($textWidth, 10, $name, 0, 'L', 0, 1, 10, $topPosition);
        $pdf->SetY(15);
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param Collection $colors
     * @param boolean $shiftUp
     */
    public function addColorsFooter($pdf, $colors, $shiftUp = false)
    {
        if ($colors->count() > 0) {
            $oneColorWidth = (int)($pdf->getPageWidth() / $colors->count());
            $leftColorMargin = 5;
            $topColorMargin = 4;
            $rightColorMargin = 1;
            $topPosition = (int)($pdf->getPageHeight() - 22 + $topColorMargin);
            $colorSquareDimension = 3;


            foreach ($colors as $key => $color) {
                $description = json_decode($color->description, true);
                $leftSquarePosition = $oneColorWidth * $key + $leftColorMargin;
                $textWidth = (int)($oneColorWidth - $colorSquareDimension - $leftColorMargin * 2);
                $rightSquarePosition = $leftSquarePosition + $colorSquareDimension + $rightColorMargin;
                $pdf->Rect(
                    $leftSquarePosition,
                    ($shiftUp) ? $topPosition - 3 : $topPosition,
                    $colorSquareDimension,
                    $colorSquareDimension,
                    'DF',
                    config('pdf.styles.empty_border'),
                    ColorsHelper::hex2Rgb($color->hex)
                );
                $pdf->SetFont('gotham-medium', 'B', 8, true);
                $pdf->SetTextColor(94, 94, 94);
                $rightSquarePosition = ($shiftUp) ? $rightSquarePosition - 5 : $rightSquarePosition;
                $pdf->MultiCell(
                    $textWidth,
                    1,
                    $this->TranslateString($description),
                    0,
                    'L',
                    0,
                    1,
                    $rightSquarePosition,
                    ($shiftUp) ? $topPosition - 5 : $topPosition
                );
            }
            $pdf->SetXY(0, 5, true);
        }
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $sequence
     * @param $level
     */
    private function _addSequence($pdf, $sequence, $level)
    {
        if ($sequence['quantity'] > 0 && $sequence['display_report']) {
            $this->nb_seq++;
            #show score of sequence for scoring sequence only
            $s_name = $sequence["name"];
            if ($s_name) {
                $s_name = substr($s_name, 0, 80);
            }
            if (strlen($s_name) > 42) {
                $height = 12;
            } else {
                $height = 6;
            }
            $pdf->SetFont('gotham-light', '', 11, true);
            $pdf->SetXY($level, $this->cursor);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(47, 59, 67);
            $pdf->MultiCell(5, $height, '', 0, 'L', 1, 0);
            $pdf->MultiCell(125 - $level, $height, mb_strtoupper($s_name, 'UTF-8'), 0, 'L', 1);

            $pdf->SetXY(130, $this->cursor);
            if (isset($sequence['score'])) {
                $pdf->SetFillColor(0, 120, 157);
            }

            #max width of score bar is 50
            #score is between 0 and 100%
            if (isset($sequence['score'])) {
                $score_bar = $sequence['score'] / 2;
            } else {
                $score_bar = null;
            }

            if ($score_bar > 0) {
                $pdf->MultiCell($score_bar, $height, '', 0, 'L', 1, 0);
            } else {
                $pdf->MultiCell(0.1, $height, '', 0, 'L', 1, 0);
            }
            #score of sequence
            $pdf->SetFont('gotham-medium', 'B', 11, true);
            if (!is_null($sequence['score'])) {
                $sequence['score'] = number_format($sequence['score'], 1);
                $pdf->Write(0, $sequence['score'] . '%', null, 0, 'L');
            }
            if (strlen($s_name) > 42) {
                $this->cursor += 14;
            } else {
                $this->cursor += 7;
            }

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
     * @param null $x
     * @param null $y
     */
    private function savePosition($x = null, $y = null)
    {
        if ($x > $this->x) {
            $this->x = $x;
        }

        if ($y > $this->y) {
            $this->y = $y;
        }
    }


    protected function _getAllSequenceScoreFromUuid($uuid, $sequences_ids)
    {
        $result = \DB::table('show_scoring')
            ->selectRaw('
                CASE WHEN SUM(score) > 0 
                THEN SUM(score) / SUM(CAST(weight AS FLOAT)) 
                ELSE 0 
                END AS score, 
                COUNT(wave_target_id) as quantity')
            ->where('scoring', true)
            ->where('uuid', $uuid)
            ->where('weight', '>', 0)
            ->whereIn('sequence_id', $sequences_ids);
        $result = $result->first();
        return $result;
    }

    /**
     * @param $target_ids
     * @param string $language_code
     * @return array
     */
    private function _getReportCBScore($target_ids, $language_code = 'fr')
    {
        $critere_score = \DB::table('show_scoring_multi_without_bonus')
            ->select('criteria_b_id', 'criteria_b_name')
            ->selectRaw('
        CASE WHEN SUM(question_score) > 0 
        THEN SUM(question_score) / SUM(CAST(question_weight AS FLOAT)) 
        ELSE null 
        END AS score, 
        COUNT(wave_target_id) as quantity')
            ->where('scoring', true)
            ->whereNotNull('score')
            ->whereNotNull('criteria_b_id')
            ->groupBy('criteria_b_name', 'criteria_b_id')
            ->where('wave_target_id', $target_ids)
            ->get();

        $h = $t = $tab_series = [];
        foreach ($critere_score as $item) {
            $critere_name = $this->TranslateString(json_decode($item['criteria_b_name'], true));
            $t[$item['criteria_b_id']][$critere_name][] = $item['score'];
        }
        foreach ($t as $key => $value) {
            foreach ($value as $k => $v) {
                $h[$k] = (round(array_sum($v) / count($v), 1));
            }
        }
        return $h;
    }

    /**
     * @param $target_ids
     * @param string $language_code
     * @return array
     */
    private function _getReportCAScore($target_ids, $language_code = 'fr')
    {

        $critere_score = \DB::table('show_scoring_multi_without_bonus')
            ->select('criteria_a_id', 'criteria_a_name')
            ->selectRaw('
        CASE WHEN SUM(question_score) > 0 
        THEN SUM(question_score) / SUM(CAST(question_weight AS FLOAT)) 
        ELSE null 
        END AS score, 
        COUNT(wave_target_id) as quantity')
            ->where('scoring', true)
            ->whereNotNull('score')
            ->whereNotNull('criteria_a_id')
            ->groupBy('criteria_a_name', 'criteria_a_id')
            ->where('wave_target_id', $target_ids)
            ->get();


        $h = $t = $tab_series = [];
        foreach ($critere_score as $item) {
            $critere_name = $this->TranslateString(json_decode($item['criteria_a_name'], true));
            $t[$item['criteria_a_id']][$critere_name][] = $item['score'];
        }
        foreach ($t as $key => $value) {
            foreach ($value as $k => $v) {
                $h[$k] = (round(array_sum($v) / count($v), 1));
            }
        }
        return $h;
    }

    /**
     * @param $target_ids
     * @param string $language_code
     * @return array
     */
    private function _getReportJobScore($target_ids, $language_code = 'fr')
    {

        $job_score = \DB::table('show_scoring_multi_without_bonus')
            ->select('job_id', 'job_name')
            ->selectRaw('
        CASE WHEN SUM(question_score) > 0 
        THEN SUM(question_score) / SUM(CAST(question_weight AS FLOAT)) 
        ELSE null 
        END AS score, 
        COUNT(wave_target_id) as quantity')
            ->where('scoring', true)
            ->whereNotNull('score')
            ->whereNotNull('job_id')
            ->groupBy('job_name', 'job_id')
            ->where('wave_target_id', $target_ids)
            ->get();

        $h = $t = $tab_series = [];
        foreach ($job_score as $item) {
            $job_name = $this->TranslateString(json_decode($item['job_name'], true));
            $t[$item['job_id']][$job_name][] = $item['score'];
        }
        foreach ($t as $key => $value) {
            foreach ($value as $k => $v) {
                $h[$k] = (round(array_sum($v) / count($v), 1));
            }
        }
        return $h;
    }

    /**
     * @param $target_ids
     * @param string $language_code
     * @return array
     */
    private function _getReportThemeScore($target_ids, $language_code = 'fr')
    {
        $theme_score = \DB::table('show_scoring_multi_without_bonus')
            ->select('theme_name', 'theme_id')
            ->selectRaw('
                CASE WHEN SUM(question_score) > 0 
                THEN SUM(question_score) / SUM(CAST(question_weight AS FLOAT)) 
                ELSE null 
                END AS score, 
                COUNT(wave_target_id) as quantity')
            ->where('scoring', true)
            ->whereNotNull('score')
            ->whereNotNull('theme_id')
            ->groupBy('theme_name', 'theme_id', 'theme_order')
            ->orderBy('theme_order')
            ->where('wave_target_id', $target_ids)
            ->get();

        $h = $t = $tab_series = [];
        foreach ($theme_score as $item) {
            $theme_name = json_decode($item['theme_name'], true)[$language_code];
            $t[$item['theme_id']][$theme_name][] = $item['score'];
        }
        foreach ($t as $key => $value) {
            foreach ($value as $k => $v) {
                $h[$k] = (round(array_sum($v) / count($v), 1));
            }
        }
        return $h;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $text
     * @param float $y
     * @param int $shift
     * @return float
     */
    private function setLabel($pdf, $text, $y, $shift = 0)
    {
        $length = 3 + strlen($text) * 2.4;
        if ($text != '') {
            $pdf->setCellPaddings(2, 0, 0, 0);
            $pdf->SetFont('helvetica', '', 10, true);
            $pdf->SetFillColor(
                config('pdf.colors.sequence_header')[0],
                config('pdf.colors.sequence_header')[1],
                config('pdf.colors.sequence_header')[2]
            );
            $pdf->SetTextColor(255, 255, 255);
            if ($shift) $pdf->SetX($shift);
            $shift = $pdf->GetX() + $length;
            $pdf->MultiCell($length, 4, $text, config('pdf.styles.empty_border'), 'L', 1, 1, $pdf->GetX(), $y);
            return $shift + 2;
        }
        $pdf->setCellPaddings(5, 2, 2, 2);
        return $shift;
    }

    /**
     * @param integer $surveyItemId
     * @return array
     */
    protected function getLabels($surveyItemId)
    {
        $labels = [];

        $themeNames = Theme::select('name')->whereHas('surveyItem', function ($query) use ($surveyItemId) {
            $query->where('survey_item_id', $surveyItemId);
        })->get()->toArray();
        $labels = array_merge($labels, $themeNames);

        $criteriaANames = CriteriaA::select('name')->whereHas('surveyItem', function ($query) use ($surveyItemId) {
            $query->where('survey_item_id', $surveyItemId);
        })->get()->toArray();
        $labels = array_merge($labels, $criteriaANames);

        $criteriaBNames = CriteriaB::select('name')->whereHas('surveyItem', function ($query) use ($surveyItemId) {
            $query->where('survey_item_id', $surveyItemId);
        })->get()->toArray();
        $labels = array_merge($labels, $criteriaBNames);

        return $labels;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $questLogo
     * @return mixed
     */
    private function setLogo($pdf, $questLogo)
    {
        list($image_w, $image_h) = @getimagesize($questLogo);
        if ($image_w + $image_h > 0) {
            # if 0 no image get by getimagesize
            if (5.2 > $image_w / $image_h) {
                list($w, $h) = [0, 150];
            } else {
                list($w, $h) = [$pdf->getPageWidth(), 0];
            }

            $pdf->Image(
                $questLogo,
                0,
                0,
                $w,
                $h,
                '',
                '',
                '',
                2,
                300,
                'C',
                false,
                false,
                0,
                'CM',
                false,
                false
            );
            $pdf->setPageMark();

            $pdf->setAlpha(0.7);
            $maskFileName = $this->basePath . $this->reportImagePath . 'Qestionnaire_background_example_Amorino_mask.png';
            $pdf->Image(
                $maskFileName,
                0,
                0,
                $w,
                $h,
                '',
                '',
                '',
                2,
                300,
                'C',
                false,
                false,
                0,
                'CM',
                false,
                false
            );
            $pdf->setPageMark();
            $pdf->setAlpha(1);
        }

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $global
     * @return mixed
     */
    protected function setGlobalScore($pdf, $global)
    {
        $pdf->SetFont('helvetica', 'B', 55);
        $pdf->SetTextColor(254, 147, 1); // #FE9301
        if (!is_null($global)) {
            $pdf->Write(260, $global . '%', null, 0, 'C');
        }
        $pdf->Ln();

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param string $waveTitle
     * @return SmiceTCPDF
     */
    protected function setWaveTitle($pdf, $waveTitle)
    {
            $pdf->SetFillColor(31, 111, 120); // #1F6F78
            $pdf->MultiCell(
                $this->cellWidth,
                $this->cellMinHeight,
                '   ' . mb_strtoupper($waveTitle, 'UTF-8'),
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
        return $pdf;

        
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $smiceur_name
     * @return SmiceTCPDF
     */
    protected function setSmiceurName($pdf, $smiceur_name)
    {
        $pdf->SetXY(
            $this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition
                + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
                - $this->yCoordStepBetweenCellAndItsTitle
        ); // 185
        $pdf->SetFont('gotham-medium', 'B', 17, true);
        $pdf->SetTextColor(94, 94, 94); // #5E5E5E
        $pdf->Write(0, config('dictionary.report_pdf.smicer_txt')[$this->language_code], null, 0, 'L');
        $pdf->SetXY(
            $this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition +
                $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
        ); // 195
        $pdf->SetFont('Helvetica', 'B', 17);
        $pdf->SetFillColor(31, 111, 120); // #1F6F78
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->MultiCell(
            $this->cellWidth,
            $this->cellMinHeight,
            '   ' . $smiceur_name,
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
        $this->iCellBlockCounter++;

        return $pdf;
    }

    protected function setMissionName($pdf, $mission_name)
    {
        $pdf->SetXY(
            $this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition
                + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
                - $this->yCoordStepBetweenCellAndItsTitle
        ); // 185
        $pdf->SetFont('Helvetica', 'B', 17);
        $pdf->SetFillColor(31, 111, 120); // #1F6F78
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->MultiCell(
            $this->cellWidth,
            $this->cellMinHeight,
            '   ' . $mission_name,
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
        $this->iCellBlockCounter++;

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $visitDate
     * @return SmiceTCPDF
     */
    protected function setDateVisit($pdf, $visitDate)
    {
        $pdf->SetXY(
            $this->xCoordCellStartPosition,
            $this->yCoordCellStartPosition
                + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
                - $this->yCoordStepBetweenCellAndItsTitle
        ); // 185
      //  $pdf->SetFont('gotham-medium', 'B', 17, true);
      //  $pdf->SetTextColor(94, 94, 94); // #5E5E5E
       // $pdf->Write(0, config('dictionary.report_pdf.visit_date_txt')[$this->language_code], null, 0, 'L');
       // $pdf->SetXY(
       //     $this->xCoordCellStartPosition,
       //     $this->yCoordCellStartPosition
       //         + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
       // ); // 195
        $pdf->SetFont('Helvetica', 'B', 17);
        $pdf->SetFillColor(31, 111, 120); // #1F6F78
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->MultiCell(
            $this->cellWidth,
            $this->cellMinHeight,
            '   ' . $visitDate,
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
        $this->iCellBlockCounter++;

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $scenario
     * @return SmiceTCPDF
     */
    protected function setScenarioTitle($pdf, $scenario)
    {
        if (isset($scenario)) {
            $scenario = json_decode($scenario)->{$this->language_code};
            $scenarioTitle = trim($scenario);
            if (!empty($scenarioTitle)) {
                $pdf->SetXY(
                    $this->xCoordCellStartPosition,
                    $this->yCoordCellStartPosition
                        + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
                        - $this->yCoordStepBetweenCellAndItsTitle
                ); // 215
                $pdf->SetFont('gotham-medium', 'B', 17, true);
                $pdf->SetTextColor(94, 94, 94); // #5E5E5E
                $pdf->Write(0, config('dictionary.report_pdf.scenario_txt')[$this->language_code], null, 0, 'L');
                $pdf->SetXY(
                    $this->xCoordCellStartPosition,
                    $this->yCoordCellStartPosition
                        + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
                ); // 225
                $pdf->SetFont('Helvetica', 'B', 17);
                $pdf->SetFillColor(73, 85, 106); // #49556A
                $pdf->SetTextColor(config('pdf.colors.white')[0]);
                $pdf->MultiCell(
                    $this->cellWidth,
                    $this->cellMinHeight,
                    '   ' . mb_strtoupper($scenarioTitle, 'UTF-8'),
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
                $this->iCellBlockCounter++;
            }
        }

        return $pdf;
    }

    protected function setFirstPageQuestion($pdf, $questions)
    {
        if (isset($questions)) {
            foreach ($questions as $q) {
                $a = $q['answer_value'];
                $aTitle = trim($a);
                if (!empty($aTitle)) {
                    $pdf->SetXY(
                        $this->xCoordCellStartPosition,
                        $this->yCoordCellStartPosition
                            + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
                            - $this->yCoordStepBetweenCellAndItsTitle
                    ); // 215
                    $pdf->SetFont('gotham-medium', 'B', 17, true);
                    $pdf->SetTextColor(94, 94, 94); // #5E5E5E
                    $pdf->Write(0, json_decode($q['question_name'])->{$this->language_code}, null, 0, 'L');
                    $pdf->SetXY(
                        $this->xCoordCellStartPosition,
                        $this->yCoordCellStartPosition
                            + $this->yCoordStepBetweenCellsWithTitle * $this->iCellBlockCounter
                    ); // 225
                    $pdf->SetFont('Helvetica', 'B', 17);
                    $pdf->SetFillColor(73, 85, 106); // #49556A
                    $pdf->SetTextColor(config('pdf.colors.white')[0]);
                    $pdf->MultiCell(
                        $this->cellWidth,
                        $this->cellMinHeight,
                        '   ' . mb_strtoupper($aTitle, 'UTF-8'),
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
                    $this->iCellBlockCounter++;
                }
            }
        }

        return $pdf;
    }

    protected function addSequenceTitle($sequence, $pdf)
    {

        $this->level = $this->level + 3;
        $this->_addSequence($pdf, $sequence, $this->level);
        if (isset($sequence['data'])) {
            $this->level = $this->level + 3;
            foreach ($sequence['data'] as $s) {
                $this->addSequenceTitle($s, $pdf);
            }
        }
        $this->level = $this->level - 3;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @return SmiceTCPDF
     */
    protected function setSequenceThemeJobCriteriaACriteriaBPages($pdf)
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
        $pdf->SetFont('gotham-medium', 'B', 23, true);
        $pdf->SetXY(15, 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.score_by_txt')[$this->language_code]
                . mb_strtoupper($this->sequence_alias, 'UTF-8'),
            null,
            0,
            'L'
        );
        $pdf->Ln();
        $pdf->SetTextColor(116, 141, 150);
        $pdf->SetFont('gotham-light', '', 14, true);
        $pdf->Write(0, config('dictionary.report_pdf.mission_result_txt')[$this->language_code], null, 0, 'L');
        $style = ['width' => 0.1, 'color' => [140, 148, 157]];
        $pdf->Line(0, 30, 140, 30, $style);
        // each sequence
        $this->cursor = 40;
        foreach ($this->sequences as $sequence) {
            $this->level = 0;
            $this->addSequenceTitle($sequence, $pdf);
        }

        if (($this->nb_seq > 20) || (count($this->theme_score) > 15)) {
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
            $pdf = $this->setThemeScores($pdf);
        }

        if (count($this->job_score) > 0) {
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

        if (count($this->job_score) > 0) {
            $pdf = $this->setJobScores($pdf);
        }

        if (count($this->criteriaa_score) > 0) {
            $pdf = $this->setCriteriaAScores($pdf);
        }

        if (count($this->criteriab_score) > 0) {
            $pdf = $this->setCriteriaBScores($pdf);
        }

        return $pdf;
    }


    /**
     * @param SmiceTCPDF $pdf
     * @return mixed
     */
    protected function setThemeScores($pdf)
    {
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, $this->cursor, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 23, true);
        $pdf->SetXY(15, $this->cursor + 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.score_by_txt')[$this->language_code] . mb_strtoupper($this->theme_alias, 'UTF-8'),
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
        foreach ($this->theme_score as $theme_name => $score) {
            $pdf = $this->setThemeScore($pdf, $theme_name, $score);
        }

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $theme_name
     * @param $score
     * @return SmiceTCPDF
     */
    private function setThemeScore($pdf, $theme_name, $score)
    {
        $pdf->SetFont('gotham-light', '', 12, true);
        $t_name = substr($theme_name, 0, 40);
        $pdf->SetXY(10, $this->cursor);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(47, 59, 67);
        $pdf->MultiCell(5, 6, '', 0, 'L', 1, 0);
        $pdf->MultiCell(115, 6, mb_strtoupper($t_name, 'UTF-8'), 0, 'L', 1);

        $pdf->SetXY(130, $this->cursor);
        $pdf->SetFillColor(31, 111, 120);
        #max width of score bar is 50
        #score is between 0 and 100%
        $score_bar = $score / 2;
        if ($score_bar == 0) {
            $score_bar = 0.1;
        }

        $pdf->MultiCell($score_bar, 6, '', 0, 'L', 1, 0);
        $theme['score'] = number_format($score, 1);
        #score of theme
        $pdf->SetFont('gotham-medium', 'B', 12, true);
        $pdf->Write(0, $score . '%', null, 0, 'L');

        $this->cursor += 8;
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

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @return SmiceTCPDF
     */
    protected function setJobScores($pdf)
    {
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, $this->cursor, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 23, true);
        $pdf->SetXY(15, $this->cursor + 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.score_by_txt')[$this->language_code] . mb_strtoupper($this->job_alias, 'UTF-8'),
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
        foreach ($this->job_score as $job_name => $score) {
            $pdf = $this->setJobScore($pdf, $job_name, $score);
        }

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $job_name
     * @param $score
     * @return SmiceTCPDF
     */
    private function setJobScore($pdf, $job_name, $score)
    {
        $pdf->SetFont('gotham-light', '', 12, true);
        $j_name = substr($job_name, 0, 40);
        $pdf->SetXY(10, $this->cursor);
        $pdf->SetFillColor(config('pdf.colors.white')[0]);
        $pdf->SetTextColor(47, 59, 67);
        $pdf->MultiCell(5, 5, '', 0, 'L', 1, 0);
        $pdf->MultiCell(115, 5, mb_strtoupper($j_name, 'UTF-8'), 0, 'L', 1);

        $pdf->SetXY(130, $this->cursor);
        $pdf->SetFillColor(31, 111, 120);
        #max width of score bar is 50
        #score is between 0 and 100%
        $score_bar = $score / 2;
        if ($score_bar == 0) {
            $score_bar = 0.1;
        }

        $pdf->MultiCell($score_bar, 5, '', 0, 'L', 1, 0);
        $theme['score'] = number_format($score, 1);
        #score of theme
        $pdf->SetFont('gotham-medium', 'B', 12, true);
        $pdf->Write(0, $score . '%', null, 0, 'L');

        $this->cursor += 8;
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

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @return SmiceTCPDF
     */
    protected function setCriteriaAScores($pdf)
    {
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, $this->cursor, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 23, true);
        $pdf->SetXY(15, $this->cursor + 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.score_by_txt')[$this->language_code] . mb_strtoupper($this->criteria_a_alias, 'UTF-8'),
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
        foreach ($this->criteriaa_score as $ca_name => $ca_score) {
            $pdf = $this->setCriteriaAScore($pdf, $ca_name, $ca_score);
        }

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $ca_name
     * @param $ca_score
     * @return SmiceTCPDF
     */
    private function setCriteriaAScore($pdf, $ca_name, $ca_score)
    {
        $pdf->SetFont('gotham-light', '', 12, true);
        $ca_name = substr($ca_name, 0, 40);
        $pdf->SetXY(10, $this->cursor);
        $pdf->SetFillColor(config('pdf.colors.white')[0]);
        $pdf->SetTextColor(47, 59, 67);
        $pdf->MultiCell(5, 5, '', 0, 'L', 1, 0);
        $pdf->MultiCell(115, 5, mb_strtoupper($ca_name, 'UTF-8'), 0, 'L', 1);

        $pdf->SetXY(130, $this->cursor);
        $pdf->SetFillColor(31, 111, 120);
        #max width of score bar is 50
        #score is between 0 and 100%
        $score_bar = $ca_score / 2;
        if ($score_bar == 0) {
            $score_bar = 0.1;
        }

        $pdf->MultiCell($score_bar, 5, '', 0, 'L', 1, 0);
        #score of cas
        $pdf->SetFont('gotham-medium', 'B', 12, true);
        $pdf->Write(0, number_format($ca_score, 1) . '%', null, 0, 'L');

        $this->cursor += 8;
        if ($this->cursor > 220) {
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

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @return SmiceTCPDF
     */
    protected function setCriteriaBScores($pdf)
    {
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, $this->cursor, true);
        $pdf->Write(0, '+', null, 0);
        $pdf->SetFont('gotham-medium', 'B', 23, true);
        $pdf->SetXY(15, $this->cursor + 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.score_by_txt')[$this->language_code]
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
        foreach ($this->criteriab_score as $cb_name => $cb_score) {
            $pdf = $this->setCriteriaBScore($pdf, $cb_name, $cb_score);
        }

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $cb_name
     * @param $cb_score
     * @return SmiceTCPDF
     */
    private function setCriteriaBScore($pdf, $cb_name, $cb_score)
    {
        $pdf->SetFont('gotham-light', '', 12, true);
        $cb_name = substr($cb_name, 0, 40);
        $pdf->SetXY(10, $this->cursor);
        $pdf->SetFillColor(config('pdf.colors.white')[0]);
        $pdf->SetTextColor(47, 59, 67);
        $pdf->MultiCell(5, 5, '', 0, 'L', 1, 0);
        $pdf->MultiCell(115, 5, mb_strtoupper($cb_name, 'UTF-8'), 0, 'L', 1);

        $pdf->SetXY(130, $this->cursor);
        $pdf->SetFillColor(31, 111, 120);
        #max width of score bar is 50
        #score is between 0 and 100%
        $score_bar = $cb_score / 2;
        if ($score_bar == 0) {
            $score_bar = 0.1;
        }

        $pdf->MultiCell($score_bar, 5, '', 0, 'L', 1, 0);
        #score of cas
        $pdf->SetFont('gotham-medium', 'B', 12, true);
        $pdf->Write(0, number_format($cb_score, 1) . '%', null, 0, 'L');

        $this->cursor += 8;
        if ($this->cursor > 220) {
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

        return $pdf;
    }

    protected function QuestionPresentInSequence($sequence, $questionsGroupedBySequence)
    {

        if (!isset($sequence['data'])) {
            if (isset($questionsGroupedBySequence[$sequence['id']])) {
                $this->questioninsequence = true;
            } else {
                $this->questioninsequence = false;
            }
        } else {
            foreach ($sequence['data'] as $d) {
                $this->QuestionPresentInSequence($d, $questionsGroupedBySequence);
                if ($this->questioninsequence)
                    return true;
            }
        }
        return $this->questioninsequence;
    }

    protected function addSequence($sequence, $questionsGroupedBySequence, $colors, $target, $pdf, $mission)
    {

        //check if question have subsequence with question
        if (isset($sequence['data'])) {
            foreach ($sequence['data'] as $s) {
                $this->addSequence($s, $questionsGroupedBySequence, $colors, $target, $pdf, $mission);
            }
        } else {
            if (isset($questionsGroupedBySequence[$sequence['id']])) {
                // Add page for new sequence
                $sequenceName = $sequence['name'];

                $pdf = $this->addSequencePage($pdf, $sequence, $target, $colors, $mission);

                $questions = [];
                if (isset($questionsGroupedBySequence[$sequence['id']])) {
                    $questions = $questionsGroupedBySequence[$sequence['id']];
                }

                $pdf = $this->setQuestions($pdf, $questions, $sequence, $colors, $sequenceName, $target, $mission);

                $pdf->SetXY(10, $this->y + 5);

                if ((count($this->imageReport) > 0)) {
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
     * @param $target
     * @return mixed
     */
    protected function setSignature($pdf, $target)
    {
        $i = $target;
        $proof = PassageProof::where('wave_target_id', $target['id'])->first();
        if ($proof['signature']) {
            $proof['signature'] = str_replace('api.smice.com', 'ik.imagekit.io/smice', $proof['signature']);
            $proof['signature'] = str_replace('smice/images/', 'smice/images/tr:n-pdf_report/', $proof['signature']);

            $pdf->Image($proof['signature'], '', '', 100);
        }
        return $pdf;
    }

    protected function setFocusQuestions($pdf, $questionsGroupedBySequence, $target, $mission)
    {
        $this->filter_theme = true;
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
        $pdf->SetFont('gotham-medium', 'B', 23, true);
        $pdf->SetXY(15, 10, true);
        $pdf->SetTextColor(51, 51, 52);
        $pdf->Write(
            0,
            config('dictionary.report_pdf.focus_txt')[$this->language_code],
            null,
            0,
            'L'
        );
        $pdf->Ln();
        // each sequence
        $colors = Color::where('survey_id', $target['survey_id'])->get();
        foreach ($questionsGroupedBySequence as $sequence) {
            $questions = $sequence;
            $this->setQuestions($pdf, $questions, $sequence, $colors, null, $target, $mission);
        }

        $this->cursor = 40;
        return $pdf;
        //
    }



    /**
     * @param SmiceTCPDF $pdf
     * @param $sequence
     * @param $name
     * @param $target
     * @param $colors
     * @return SmiceTCPDF
     */
    private function addSequencePage($pdf, $sequence, $target, $colors, $mission)
    {
        $pdf->AddPage();

        // Fill background with #E5E5E5
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
        $pdf->Rect(
            0,
            0,
            $pdf->getPageWidth(),
            32,
            'DF',
            config('pdf.styles.empty_border'),
            config('pdf.colors.sequence_header')
        );
        $pdf->SetFont('helvetica', '', 48, true);
        $pdf->SetTextColor(207, 95, 56); // #CF5F38
        $pdf->SetXY(5, 0, true);
        $pdf->Write(0, '+', null, 0);
        // Write sequence name
        $pdf->SetFont('gotham-medium', 'I', 10, true);
        $pdf->SetXY(15, 5);
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->MultiCell(165, 5, mb_strtoupper($sequence['parent'], 'UTF-8'), 0, 'L', 0);
        # Sequence name
        $pdf->SetFont('gotham-medium', 'B', 23, true);
        $pdf->SetXY(15, 10);
        $pdf->SetTextColor(config('pdf.colors.white')[0]);
        $pdf->MultiCell(165, 5, mb_strtoupper($sequence['name'], 'UTF-8'), 0, 'L', 0);
        $pdf->Ln();

        # Label "sequence details"
        if (!is_null($sequence['score'])) {
            if ($sequence['quantity'] > 0) {
                $pdf->SetXY(165, 10, true);
                $pdf->SetFont('gotham-medium', 'B', 10, true);
                $pdf->SetTextColor(config('pdf.colors.white')[0]);
                $pdf->MultiCell(
                    50,
                    5,
                    config('dictionary.report_pdf.global_score_txt')[$this->language_code],
                    0,
                    'C',
                    0
                );
                $pdf->SetXY(165, 15, true);
                $pdf->SetFont('gotham-medium', 'B', 25, true);
                $pdf->SetTextColor(
                    config('pdf.colors.orange')[0],
                    config('pdf.colors.orange')[1],
                    config('pdf.colors.orange')[2]
                );
                $pdf->MultiCell(50, 5, round($sequence['score'], 1) . ' %', 0, 'C', 0);
            }
        }
        //add shop name to page footer
        $this->addShopFooter($pdf, mb_strtoupper($target['shop'], 'UTF-8'));
        // add colors footer if colors are set
        if ($mission['show_legend']) {
            $this->addColorsFooter($pdf, $colors);
        }

        $pdf->SetXY(10, 35, true);

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $questions
     * @param $sequence
     * @param $colors
     * @param $sequenceName
     * @param $target
     * @return SmiceTCPDF
     */
    protected function setQuestions($pdf, $questions, $sequence, $colors, $sequence_name, $target, $mission)
    {
        $questionNumber = 1;
        $this->coordinates = ['x' => 0, 'y' => 0];
        foreach ($questions as $question) {
            //Get question name
            $question_name = json_decode($question['question_name'])->{$this->language_code};
            //$question_name = $sequence['numbering'] . $questionNumber . ' ' . $question_name;

            //Get question info
            if ($question['question_info']) {
                $question_info = json_decode($question['question_info'])->{$this->language_code};
            } else {
                $question_info = null;
            }



            $pdf->SetTextColor(config('pdf.colors.blackRGB')[0]);
            $questionId = $question['id_question'];
            $response = $this->responses[0][$questionId][0];

            $limit_page = 230;

            $userComment = '';

            //change limit to add page break
            if (isset($response['comment'])) {
                $userComment = $response['comment'];
                $limit_page -= (strlen($userComment) / 100) * 4;
                $limit_page -= (strlen($response['reponse']) / 100) * ($response['type'] === 'checkbox' ? 10 : 4);
            }
            $limit_page -= (strlen($question_info) / 100) * 4;
            $limit_page -= (strlen($response['reponse']) / 100) * 4;

            if ($question['type'] === 'checkbox') {
                $limit_page -= count(($this->responses[0])[$questionId]) * 10;
            }

            $answerStyle = [
                'font' => 'gotham-medium',
                'background' => config('pdf.colors.background_answer'),
                'text_color' => config('pdf.colors.white'),
                'align' => 'C',
                'width' => 35,
                'font_size' => 10,
                'font_style' => ''
            ];


            $this->coordinates = array_merge($this->coordinates, [
                'question_width' => 0,
                'question_bottom' => 0,
                'left_bottom' => 0,
                'right_bottom' => 0
            ]);

            if ($pdf->GetY() > $limit_page) {
                $pdf = $this->_addPage($pdf, $sequence_name, $target, $colors, $mission);
            }
            $pdf->Ln(7);

            $pdf = $this->addQuestion($pdf, $sequence_name, $question_name, $question);


            $this->response_detail = [];
            $this->coordinates['score_bottom'] = 0;
            foreach (($this->responses[0])[$questionId] as $response) {
                $pdf = $this->setResponse($pdf, $response, $question, $question_name, $userComment, $answerStyle, $question_info, $mission);
            }

            # if question info to explain what we evaluate



            # if other than text area, fill enpty space on left or right
            if ($question['type'] !== 'checkbox')
                $pdf->SetXY(10, $this->coordinates['question_bottom']);
            if ($this->response_detail['comment'] !== '' && $this->response_detail['comment'] !== ' ' && $this->response_detail['comment'] !== '  ') {
                $pdf->SetFont('gotham-light', '', 10, true);
                $pdf->SetFillColor(config('pdf.colors.white')[0]);
                $pdf->SetTextColor(
                    config('pdf.colors.sequence_header')[0],
                    config('pdf.colors.sequence_header')[1],
                    config('pdf.colors.sequence_header')[2]
                );
                // affichage de du commentaire "1"
                $pdf->MultiCell(
                    $this->coordinates['question_width'],
                    10,
                    '«' . $this->response_detail['comment'] . '»',
                    config('pdf.styles.empty_border'),
                    'L',
                    1
                );
                $this->coordinates['right_bottom'] = $pdf->GetY();

                $this->savePosition('', $this->coordinates['right_bottom']);
            } else {
                $pdf->SetXY(10, $this->y + 5);
            }


            if ($this->response_detail['comments']) {
                $pdf->SetFont('gotham-light', '', 8, false);
                $pdf->SetFillColor(config('pdf.colors.orange')[0]);
                $pdf->SetTextColor(
                    config('pdf.colors.sequence_header')[0],
                    config('pdf.colors.sequence_header')[1],
                    config('pdf.colors.sequence_header')[2]
                );
                foreach ($this->response_detail['comments'] as $comments) {
                    if ($question['response_value'] === null) {
                        $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => config('pdf.colors.blackRGB')));
                    } else if ($question['response_value'] < 70) {
                        $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => config('pdf.colors.ko')));
                    } else {
                        $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => config('pdf.colors.ok')));
                    }

                    $pdf->SetFillColor(config('pdf.colors.background_question')[0], config('pdf.colors.background_question')[1], config('pdf.colors.background_question')[2]);
                    // affichage de du commentaire "1"
                    $pdf->MultiCell(
                        $this->coordinates['question_width'] - 0.5,
                        10,
                        $this->TranslateString($comments['questionrowcomment'][0]['name']),
                        1,
                        'C',
                        1,
                        0,
                        '',
                        '',
                        true
                    );
                    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => config('pdf.colors.blackRGB')));
                    $pdf->Ln(10);
                    $this->savePosition('', $this->coordinates['right_bottom']);
                }
            }
            $questionNumber++;
        }

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $limitPage
     * @param $target
     * @param $colors
     * @param $name
     * @param $labels
     * @param $questionName
     * @param $question
     * @return array
     */
    private function addQuestion($pdf, $name, $question_name, $question)
    {
        // get all labels for question
        $labels = $this->getLabels($question['survey_item_id']);

        // show labels
        $labelsY = $pdf->GetY();
        $labelShift = 0;
        foreach ($labels as $label) {
            $name = $this->TranslateString($label['name']);
            $labelShift = $this->setLabel($pdf, $name, $labelsY, $labelShift);
        }
        if ($question['bonus_question']) {
            $pdf->SetFontSize(7);
            $pdf->SetTextColor(254, 147, 1); // #FE9301
            $pdf->SetFillColor(
                config('pdf.colors.background_question')[0],
                config('pdf.colors.background_question')[1],
                config('pdf.colors.background_question')[2]
            );
            $pdf->SetX($labelShift);
            $pdf->MultiCell(50, 4, "Question bonus", config('pdf.styles.empty_border'), 'L', 1, 1, $pdf->GetX(), $labelsY);
        }

        $is_scoring = true;
        //check if current question is scoring
        if ($question['scoring'] == false || $question['question_score'] === null) {
            $is_scoring = false;
        }

        //check if answer must be write on full width or just in small right box
        $is_full_width = false;
        if (($question['type'] === 'checkbox') || ($question['type'] === 'text_area') || ($question['type'] === 'text')) {
            $is_full_width = true;
        }


        // find question response by questionId
        $this->x = $this->y = $i = 0;
        $question_width = $pdf->getPageWidth() - 55;
        if ($is_full_width) {
            $question_width = $pdf->getPageWidth() - 20;
        }

        # question name
        $pdf->SetFont('gotham-medium', 'B', 12, true);
        $pdf->SetFillColor(
            config('pdf.colors.background_question')[0],
            config('pdf.colors.background_question')[1],
            config('pdf.colors.background_question')[2]
        );
        $pdf->SetTextColor(
            config('pdf.colors.sequence_header')[0],
            config('pdf.colors.sequence_header')[1],
            config('pdf.colors.sequence_header')[2]
        );
        $pdf->setCellPaddings(5, 2, 2, 2);
        $this->coordinates = ['x' => $pdf->GetX(), 'y' => $pdf->GetY()];
        $this->cursor = $this->coordinates['y'];

        $pdf->MultiCell($question_width, 10, $question_name, config('pdf.styles.empty_border'), 'L', 1);



        $this->coordinates['left_bottom'] = $this->coordinates['question_bottom'] = $pdf->GetY();
        $this->coordinates['question_width'] = $question_width;

        $this->savePosition('', $pdf->GetY());

        return $pdf;
    }

    protected function _AddQuestionInfo($pdf, $question_info)
    {
        $pdf->SetFont('gotham-light', '', 7, true);
        if ($question_info != strip_tags($question_info)) {
            //$pdf->WriteHTMLv2($question_info);
            //$pdf->MultiCell(
            //    0, 0, '', '', 'L', 1
            //);
        } else {
            $pdf->MultiCell(
                $this->coordinates['question_width'],
                5,
                $question_info,
                config('pdf.styles.empty_border'),
                'L',
                1
            );
            if (strlen($question_info) > 1) {
                $pdf->Ln(10);
                $this->coordinates['question_bottom'] = $pdf->GetY() - 10;
            }
        }
        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $response
     * @param $question
     * @param $questionName
     * @param $userComment
     * @param $answerStyle
     * @param $coordinates
     * @param $question_info
     * @return array
     */
    protected function setResponse($pdf, $response, $question, $questionName, $userComment, $answerStyle, $question_info, $mission)
    {


        $this->response_detail = [
            'id_response' => $response['id_reponse'],
            'response' => $response['reponse'],
            'type' => $response['type'],
            'date' => $response['date'],
            'comment' => $response['comment'],
            'comments' => $response['comments'],
            'image' => $response['image'],

        ];
        // collect images attached to user response
        if (isset($response['image'])) {
            foreach ($response['image'] as $img) {
                $this->collectImages($img, $questionName, $userComment, $question);
            }
        }

        $is_full_width = false;
        $is_scoring = true;
        # if text area or answer is too long no right box full width
        if (($this->response_detail['type'] === 'text_area')
            || ($this->response_detail['type'] === 'text')
            || ($this->response_detail['type'] === 'checkbox')
        ) {
            $is_full_width = true;
        }
        if ($question['scoring'] === false || $question['question_score'] === NULL || $mission['hide_score']) {
            $is_scoring = false;
        }

        if ($is_full_width) {

            $answerStyle = array_merge($answerStyle, [
                'font' => 'gotham-light',
                'align' => 'L',
                'font_size' => 10,
                'font_style' => 'I',
                'background' => config('pdf.colors.background_answer'),
                'text_color' => config('pdf.colors.blackRGB'),
                'width' => $this->coordinates['question_width'] - $pdf->getPageWidth() - 20
            ]);
            if (!$is_scoring) {
                $this->coordinates['question_width'] = $pdf->getPageWidth() - 20;
            }
        }



        $this->coordinates['left_bottom'] = $this->coordinates['right_bottom'] = 0;

        #if not textarea, right box with answer & score
        if (!$is_full_width) {
            $pdf->SetXY($this->coordinates['x'] + $this->coordinates['question_width'], $this->coordinates['y']);
        } else {
            #text area : display answer info before full answer
            if ($question_info && $question_info !== "")
                $pdf = $this->_AddQuestionInfo($pdf, $question_info);
            $this->coordinates['right_bottom'] = $pdf->GetY();
        }
        $picCoordX = $this->coordinates['x'] + $this->coordinates['question_width'];
        $this->savePosition('', $pdf->GetY());
        # if scoring & answer not N.A add box with score
        if ($is_scoring) {
            $pdf->SetFont('gotham-medium', 'B', 12, true);
            $pdf->SetFillColor(config('pdf.colors.white')[0]);
            $pdf->SetTextColor(config('pdf.colors.blackRGB')[0]);
            $this->coordinates['y'] = $pdf->GetY();
            //show score if not 0 or 100
            $pourcentage = "%";
            if ($this->response_detail['type'] === 'satisfaction' || $mission['experience']) {
                $pourcentage = ""; //remove pourcentage
            }
            if ($question['society_id'] === 113 || $mission['experience']) {
                //sncf show only color for score 0 & 100
                if (!$question['scoring']) {
                    $pdf->MultiCell(
                        $answerStyle['width'] - 2,
                        10,
                        config('dictionary.report_pdf.no_scoring'),
                        config('pdf.styles.empty_border'),
                        'C',
                        1
                    );
                }
                $pdf->SetXY($pdf->getPageWidth() - 45, $this->coordinates['y']);
                $score_width = 35;
            } else {
                /*                 if (strlen($response_detail['response']) > 45) {
                    $pdf->SetFont('gotham-medium', 'B', 8, true);
                }
                else {
                    $pdf->SetFont('gotham-medium', 'B', 12, true);
                } */
                $pdf->MultiCell(
                    $answerStyle['width'] - 2,
                    10,
                    $question['response_value'] . $pourcentage,
                    config('pdf.styles.empty_border'),
                    'C',
                    1
                );
                $pdf->SetXY($pdf->getPageWidth() - 12, $this->coordinates['y']);
                $score_width = 2;
            }
            if ($this->response_detail['type'] !== 'satisfaction') {
                if ($question['response_value'] < 70) {
                    $pdf->SetFillColor(
                        config('pdf.colors.ko')[0],
                        config('pdf.colors.ko')[1],
                        config('pdf.colors.ko')[2]
                    );
                } else {
                    $pdf->SetFillColor(
                        config('pdf.colors.ok')[0],
                        config('pdf.colors.ok')[1],
                        config('pdf.colors.ok')[2]
                    );
                }
            }
            if ($question['type'] == 'radio') {
                $selectedColor = SelectedColor::where('answer_id', $question['question_row_id'])
                    ->with('color')->first();
                if ($selectedColor) {
                    if (isset($selectedColor->color)) {
                        $color = ColorsHelper::hex2Rgb($selectedColor->color->hex);
                        $pdf->SetFillColor($color[0], $color[1], $color[2]);
                    }
                }
            }
            $pdf->MultiCell($score_width, 10, '', config('pdf.styles.empty_border'), 'C', 1);
            $this->coordinates['score_bottom'] = $pdf->GetY();
            $this->savePosition('', $pdf->GetY());
        } else {
            # no scoring mode, show only response
            $text = null;
            if ($this->response_detail['type'] === 'checkbox') {
                # if checkbox add, check mark behind the response
                $this->response_detail['response'] = ">  " . $this->response_detail['response'];
            };
            $pdf->SetFont($answerStyle['font'], $answerStyle['font_style'], $answerStyle['font_size'], true);
            $pdf->SetFillColor($answerStyle['background'][0], $answerStyle['background'][1], $answerStyle['background'][2]);
            $pdf->SetTextColor($answerStyle['text_color'][0], $answerStyle['text_color'][1], $answerStyle['text_color'][2]);
            $pdf->MultiCell(
                $answerStyle['width'],
                10,
                $this->response_detail['response'],
                config('pdf.styles.empty_border'),
                $answerStyle['align'],
                1
            );
            $this->coordinates['right_bottom'] = $pdf->GetY();
            $this->savePosition('', $pdf->GetY());
        }
        # picto image
        if (!empty($this->response_detail['image']) && count($this->response_detail['image']) > 0) {
            $picPhoto = $this->basePath . $this->reportImagePath . 'pictogram.png';
            $pdf->Image(
                $picPhoto,
                $picCoordX - 9,
                $this->coordinates['question_bottom'] + 2.7,
                11,
                8.8,
                'PNG',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0,
                '',
                false,
                false
            );
        }

        # display answer before question information if not text_area
        if (!$is_full_width) {
            $pdf->SetXY($pdf->GetX(), $this->coordinates['question_bottom']);
            $pdf->SetTextColor(config('pdf.colors.blackRGB')[0]);
            $pdf->SetFillColor(
                config('pdf.colors.background_question')[0],
                config('pdf.colors.background_question')[1],
                config('pdf.colors.background_question')[2]
            );
            if ($question_info && $question_info !== "")
                $pdf = $this->_AddQuestionInfo($pdf, $question_info);
            $this->coordinates['left_bottom'] = $pdf->GetY();
            $this->savePosition('', $this->coordinates['left_bottom']);

            $this->cursor += 5;
        }

        # answer box
        if ($is_scoring) {
            if ($mission['experience']) {
                $response_detail_txt = null;
            } else {
                $response_detail_txt = $this->response_detail['response'];
            }
            $pdf = $this->setAnswerBox($pdf, $answerStyle, $response_detail_txt);
        }
        if ($this->coordinates['left_bottom'] > $this->coordinates['right_bottom'])
            $pdf->SetY($this->coordinates['left_bottom']);
        else
            $pdf->SetY($this->coordinates['right_bottom']);

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $name
     * @param $target
     * @param $colors
     * @return SmiceTCPDF
     */
    private function _addPage($pdf, $name, $target, $colors, $mission)
    {
        $pdf->AddPage();
        // Fill background with #E5E5E5
        $pdf->Rect(
            0,
            0,
            $pdf->getPageWidth(),
            $pdf->getPageHeight(),
            'DF',
            config('pdf.styles.empty_border'),
            config('pdf.colors.lightGrayRGB')
        );
        //add shop name to page footer
        $this->addShopFooter($pdf, mb_strtoupper($target['shop'], 'UTF-8'), true);
        // add colors footer if colors are set
        if ($mission['show_legend']) {
            $this->addColorsFooter($pdf, $colors, true);
        }

        $pdf->setPageMark();
        # Sequence name
        if ($this->filter_theme === false) {
            $pdf->SetFont('gotham-medium', 'B', 14, true);
            $pdf->SetTextColor(config('pdf.colors.blackRGB')[0]);
            $pdf->Write(0, mb_strtoupper($name, 'UTF-8'), null, 0, 'L');
            $pdf->Ln(7);
            $style = ['width' => 0.1, 'color' => [140, 148, 157]];
            $pdf->Line(0, 25, 140, 25, $style);
            $pdf->Ln(10);
        }


        return $pdf;
    }

    /**
     * @param $target
     * @param SmiceTCPDF $pdf
     * @param $mission
     * @return string
     * @throws SmiceException
     */
    protected function returnReport($target, $pdf, $mission, $type, $forceupdate = false)
    {
        $pdfName = json_decode($target['program'])->{$this->language_code} . '-' . $target['wave'] . '-'
            . str_replace('/', '', $target['shop']) . '-' . $target['visit_date'] . '-' . $target['id'] . '-' . $type . '.pdf';

        $file = storage_path('app/public/PDF/') . $pdfName;
        if (file_exists($file)) {
            unlink($file);
        }
        $pdf->Output($file, 'F');
        if ($forceupdate || !Storage::exists('/public/PDF/' . $pdfName)) { //create report only if not exit
            sleep(1);
            Storage::put('/public/PDF/' . $pdfName, file_get_contents($file));
        }

        if (file_exists($file)) {
            if ($mission['document']) {
                $unite = new PdfUnite();
                $pdf_lastpage = str_replace('https://api.smice.com/documents/', '', $mission['document']);
                $contents = file_get_contents($mission['document']);
                Storage::disk('local')->put('documents/' . $pdf_lastpage, $contents);
                $pdf_merge = $unite->join(storage_path('app/public/PDF/') . $pdfName, storage_path('app/documents/') . $pdf_lastpage, storage_path('app/public/PDF/') . 'merge_test.pdf')->output();
                file_put_contents($file, $pdf_merge);
                Storage::put('/public/PDF/' . $pdfName, $pdf_merge);
            }
        } else {
            throw new SmiceException(
                SmiceException::HTTP_INTERNAL_SERVER_ERROR,
                SmiceException::E_SERVER,
                'SmiceTCPDF ERROR: PDF document wasn\'t generated.'
            );
        }

        WaveTarget::whereId($target['id'])->update([
            'pdf_url' => $pdfName,
            'pdf_url_created_at' => Carbon::now()
        ]);

        return $pdfName;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $sequenceName
     * @return SmiceTCPDF
     */
    protected function setImageReport($pdf, $type, $sequenceName)
    {
        $imagesCount = count($this->imageReport);
        $pagesCount = ceil($imagesCount / 4);

        for ($i = 1; $i <= $pagesCount; $i++) {
            // calc indexes - they need to get first 4 images
            // indexes are zero based !!
            $baseIdx = $i * 4;

            // Add new page with photo gallery
            $pdf->AddPage();
            # Background for photo gallery page
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

            # Background for top label
            $pdf->Rect(
                0,
                0,
                $pdf->getPageWidth(),
                32,
                'DF',
                config('pdf.styles.empty_border'),
                config('pdf.colors.darkBlueRGB')
            );
            # Top page label
            $pdf->SetFont('helvetica', '', 48, true);
            $pdf->SetTextColor(207, 95, 56); // #CF5F38
            $pdf->SetXY(5, 0, true);
            $pdf->Write(0, '+', null, 0);
            $pdf->SetFont('gotham-medium', 'B', 23, true);
            $pdf->SetTextColor(255, 255, 255); // #FFFFFF
            $pdf->SetXY(15, 10, true);
            $pdf->Write(0, mb_strtoupper($sequenceName, 'UTF-8'), null, 0);

            $this->coordinates = [
                'x1' => 15,
                'x2' => 101 + config('pdf.dimensions.distanceBetweenPlaceholders'),
                'x3' => 15,
                'x4' => 101 + config('pdf.dimensions.distanceBetweenPlaceholders'),
                'y1' => 47,
                'y2' => 47,
                'y3' => 47 + config('pdf.dimensions.squareHeight') + config('pdf.dimensions.distanceBetweenPlaceholders'),
                'y4' => 47 + config('pdf.dimensions.squareHeight') + config('pdf.dimensions.distanceBetweenPlaceholders'),
            ];

            for ($j = 0; $j < 4; $j++) {
                $imgId = $baseIdx - 4 + $j;
                if ($imgId < $imagesCount) {
                    $pdf = $this->setImagesSquares($pdf, $imgId, $j);
                    $pdf = $this->addImage($pdf, $imgId, $j);
                }
            }
        }
        $this->imageReport = [];

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param $coordinates
     * @param $answerStyle
     * @param $this->response_detail
     * @return array
     */
    protected function setAnswerBox($pdf, $answerStyle, $response_detail_txt)
    {
        $pdf->SetXY($this->coordinates['question_width'] + 10, $this->coordinates['score_bottom']);
        $pdf->SetFont($answerStyle['font'], $answerStyle['font_style'], $answerStyle['font_size'], true);
        $pdf->SetFillColor($answerStyle['background'][0], $answerStyle['background'][1], $answerStyle['background'][2]);
        $pdf->SetTextColor($answerStyle['text_color'][0], $answerStyle['text_color'][1], $answerStyle['text_color'][2]);
        $this->coordinates['x'] = $pdf->GetX();
        $this->coordinates['y'] = $pdf->GetY();

        $pdf->MultiCell(
            $answerStyle['width'],
            10,
            $response_detail_txt,
            config('pdf.colors.empty_border'),
            $answerStyle['align'],
            1
        );
        $this->coordinates['right_bottom'] = $pdf->GetY();
        $this->savePosition('', $pdf->GetY());

        return $pdf;
    }

    /**
     * @param $img
     * @param $questionName
     * @param $userComment
     * @param $question
     */
    protected function collectImages($img, $questionName, $userComment, $question)
    {
        $userAnswer = '';
        $answerId = $img['answer_id'];
        //add image kit url
        $img['url'] = str_replace('api.smice.com', 'ik.imagekit.io/smice', $img['url']);
        $img['url'] = str_replace('smice/images/', 'smice/images/tr:n-pdf_report/', $img['url']);
        $element = [
            $questionName,
            $userAnswer,
            $userComment,
            $img['url'],
            $answerId,
            $question['question_row_id'],
            'score' => $question['response_value'],
            'scoring' => ($question['scoring']) ? true : false
        ];

        if (empty($this->imageReport)) {
            $this->imageReport[] = $element;
        } else {
            array_push($this->imageReport, $element);
        }
    }

    /**
     * @param array $responses
     * @return array
     */
    protected function setMission($responses)
    {
        $question_id = [];
        $mission = null;
        $questionsGroupedBySequence = [];
        foreach ($responses as $key => $item) {
            // get mission params
            if ($mission === null) {
                $mission = Mission::select(['mission_name', 'smicer_name', 'wave_name', 'date_visit', 'document', 'show_score', 'show_score_2', 'experience', 'show_legend', 'hide_score', 'show_mission_name'])
                    ->where('id', $item['mission_id'])
                    ->first()
                    ->toArray();
            }
            if (!array_keys($question_id, $item['id_question'])) {
                $questionsGroupedBySequence[$item['sequence_id']][$key] = $item;
            }
            array_push($question_id, $item['id_question']);
        }

        if ($mission === null) {
            $mission = [
                'mission_name' => false,
                'smicer_name' => false,
                'wave_name' => false,
                'date_visit' => false,
                'document' => false,
                'show_score' => true,
                'show_score_2' => true,
                'show_mission_name' => true,
                'hide_score' => false,
                'experience' => false,
                'show_legend' => false
            ];
        }

        return [$mission, $questionsGroupedBySequence];
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param int $imageId
     * @param int $index
     * @param array $coordinates
     * @return SmiceTCPDF
     */
    private function addImage($pdf, $imageId, $index)
    {
        $width = config('pdf.dimensions.squareSide') + config('pdf.dimensions.distanceBetweenPlaceholders');
        $height = config('pdf.dimensions.squareHeight') + config('pdf.dimensions.distanceBetweenPlaceholders');
        $x = $this->coordinates['x' . ($index + 1)];
        $y = $this->coordinates['y' . ($index + 1)];
        $image_url = $this->imageReport[$imageId][3];
        // Start Transformation
        $pdf->StartTransform();
        $exif = @exif_read_data($image_url);
        $rotation = "tr:rt-0";
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 8:
                    $rotation = "tr:rt-90";
                    break;
                case 3:
                    $rotation = "tr:rt-180";
                    break;
                case 6:
                    $rotation = "tr:rt-270";
                    break;
            }
        }
        //search and replace transfoamrtion string
        $image_url = str_replace('tr:n-pdf_report', 'tr:n-pdf_report,' . $rotation, $image_url);
        $a = $image_url;
        // Scale by 80% centered by ($x1,$y1) which is the lower left corner of the rectangle
        $pdf->ScaleXY(80, $x + ($width / 2), $y + ($height / 2));
        //check if image is correct : image_url
        if (strlen($image_url) > 1) {
            $pdf->Image(
                $image_url,
                $x,
                $y - 7 - config('pdf.dimensions.distanceBetweenPlaceholders'),
                $width,
                $height,
                '',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0,
                config('pdf.styles.fitBox'),
                false,
                false
            );
        }
        // Stop Transformation
        $pdf->StopTransform();

        // Add user's photo and "Question name : answer and comment"
        $imageLegend = trim(($this->imageReport[$imageId])[0]);
        $userAnswer = trim(($this->imageReport[$imageId])[1]);
        $userComment = trim(($this->imageReport[$imageId])[2]);


        if (!empty($userAnswer)) {
            $imageLegend = $imageLegend . ' : ' . $userAnswer;
        }

        if (!empty($userComment)) {
            $separator = '; ';

            if (empty($userAnswer)) {
                $separator = '';
                $imageLegend = $imageLegend . ' : ';
            }

            $imageLegend = $imageLegend . $separator . $userComment;
        }

        if (strlen($imageLegend) > config('pdf.dimensions.characterLimit')) {
            $imageLegend = mb_strimwidth($imageLegend, 0, config('pdf.dimensions.characterLimit'), '...', 'UTF-8');
        }
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY($x, ($y + config('pdf.dimensions.squareHeight')
            - config('pdf.dimensions.distanceBetweenPlaceholders') * 3)
            - config('pdf.dimensions.yCoordCorrectionForLegend'), true);
        $pdf->MultiCell(
            config('pdf.dimensions.squareSide') - 4,
            config('pdf.dimensions.distanceBetweenPlaceholders') * 3,
            $imageLegend,
            0,
            'C',
            false
        );

        return $pdf;
    }

    /**
     * @param SmiceTCPDF $pdf
     * @param int $imgId
     * @param int $index
     * @param array $coordinates
     * @return SmiceTCPDF
     */
    private function setImagesSquares($pdf, $imgId, $index)
    {
        $x = $this->coordinates['x' . ($index + 1)];
        $y = $this->coordinates['y' . ($index + 1)];
        $pdf->Rect(
            $x,
            $y,
            config('pdf.dimensions.squareSide'),
            config('pdf.dimensions.squareHeight'),
            'DF',
            config('pdf.styles.empty_border'),
            config('pdf.colors.greyGreenRGB')
        ); // top left
        $e = $this->imageReport;
        if ($this->imageReport[$imgId]['scoring'] && (isset($this->imageReport[$imgId]['score']))) {
            $color = ($this->imageReport[$imgId]['score'] < 70)
                ? config('pdf.colors.ko')
                : config('pdf.colors.ok');
            //get color set by user in survey
            $selectedColor = SelectedColor::where('answer_id', $this->imageReport[$imgId][5])
                ->with('color')->first();
            if ($selectedColor) {
                if (isset($selectedColor->color)) {
                    $color = ColorsHelper::hex2Rgb($selectedColor->color->hex);
                }
            }
            $pdf->Rect(
                $x,
                $y,
                config('pdf.dimensions.scoreBarWidth'),
                config('pdf.dimensions.squareHeight'),
                'DF',
                config('pdf.styles.empty_border'),
                $color
            );
        }

        return $pdf;
    }

    protected function addSynteticSequence($sequence, $questionsGroupedBySequence, $pdf)
    {

        //check if question have subsequence with question
        $present = $this->QuestionPresentInSequence($sequence, $questionsGroupedBySequence);
        if ($present) {
            if (isset($sequence['data'])) {
                foreach ($sequence['data'] as $s) {
                    $this->addSynteticSequence($s, $questionsGroupedBySequence, $pdf);
                }
            } else {
                $pdf->AddPage();
                // Fill background with #E5E5E5
                $pdf->Rect(
                    0,
                    0,
                    $pdf->getPageWidth(),
                    $pdf->getPageHeight(),
                    'DF',
                    config('pdf.styles.empty_border'),
                    config('pdf.colors.lightGrayRGB')
                );

                $pdf->setPageMark();
                // Add page for new sequence
                //$sequenceName = $sequence['numbering'] . ' ' . $sequence['sequence'][$this->language_code];
                $sequenceName = $sequence['name'];
                $sequenceName = '   ' . mb_strtoupper($sequenceName, 'UTF-8');

                $pdf->SetFillColor(66, 66, 66); // #424242
                $pdf->SetTextColor(config('pdf.colors.white')[0]);

                $pdf->MultiCell(160, $this->cellMinHeight, $sequenceName, 0, 'L', 1, 0, '', '', true, 0, false, true, 12, 'M');

                if (isset($sequence['score'])) {
                    $pdf->SetFontSize(20);
                    $pdf->SetTextColor(254, 147, 1); // #FE9301
                }

                #score is between 0 and 100%
                $score_bar = (isset($sequence['score']))
                    ? number_format($sequence['score'], 1) . '%'
                    : '';

                $pdf->MultiCell(30, $this->cellMinHeight, $score_bar, 0, 'R', 1, 0, '', '', true, 0, false, true, 12, 'M');

                $questions = [];
                if (isset($questionsGroupedBySequence[$sequence['id']])) {
                    $questions = $questionsGroupedBySequence[$sequence['id']];
                }

                $questionNumber = 1;
                foreach ($questions as $question) {
                    $pdf->Ln($this->cellMinHeight + 2);

                    $questionName = json_decode($question['question_name'])->{$this->language_code};
                    $questionName = '   ' . $sequence['numbering'] . $questionNumber . ' ' . $questionName;

                    $pdf->SetFont('gotham-medium', 'B', 10, true);
                    $pdf->SetFillColor(
                        config('pdf.colors.background_question')[0],
                        config('pdf.colors.background_question')[1],
                        config('pdf.colors.background_question')[2]
                    );
                    $pdf->SetTextColor(
                        config('pdf.colors.sequence_header')[0],
                        config('pdf.colors.sequence_header')[1],
                        config('pdf.colors.sequence_header')[2]
                    );

                    $pdf->MultiCell(170, $this->cellMinHeight, $questionName, 0, 'L', 1, 0, '', '', true, 0, false, true, 10, 'M');



                    if ($question['response_value'] < 70 && $question['response_value'] !== null) {
                        $pdf->SetFillColor(
                            config('pdf.colors.ko')[0],
                            config('pdf.colors.ko')[1],
                            config('pdf.colors.ko')[2]
                        );
                    } else if ($question['response_value'] >= 70) {
                        $pdf->SetFillColor(
                            config('pdf.colors.ok')[0],
                            config('pdf.colors.ok')[1],
                            config('pdf.colors.ok')[2]
                        );
                    }

                    if ($question['type'] == 'radio') {
                        $selectedColor = SelectedColor::where('answer_id', $question['question_row_id'])
                            ->with('color')->first();
                        if ($selectedColor) {
                            if (isset($selectedColor->color)) {
                                $color = ColorsHelper::hex2Rgb($selectedColor->color->hex);
                                $pdf->SetFillColor($color[0], $color[1], $color[2]);
                            }
                        }
                    }



                    $pdf->MultiCell(20, $this->cellMinHeight, '', 0, 'L', 1, 0, '', '', true, 0, false, true, 12, 'M');

                    $this->savePosition('', $pdf->GetY());

                    $questionNumber++;
                }
                $pdf->Ln($this->cellMinHeight + 2);
            }
        }
    }

    /**
     * Use for syntetic report
     * @param SmiceTCPDF $pdf
     * @param $$questionsGroupedBySequence
     * @return SmiceTCPDF
     */
    protected function setSequencesAndQuestions($pdf, $questionsGroupedBySequence)
    {

        foreach ($this->sequences as $sequence) {
            $this->addSynteticSequence($sequence, $questionsGroupedBySequence, $pdf);
        }

        return $pdf;
    }

    protected function addSequenceLine($sequenceFirstLevel, $survey_id, $uuid)
    {
        $line = [];
        foreach ($sequenceFirstLevel as $k => $sequence) {
            //
            $this->parent_name = null;
            //lecture du score pour la ligne au global
            $subSequences = SurveyItem::with('children')->where('survey_id', $survey_id)->where('item_id', $sequence['item_id'])->orderBy('order')->retrieveAll()->toArray();

            //Recursivité de la sequence
            $line[] = $this->FormatSequence($subSequences, $uuid);
        }
        return $line;
    }

    protected function FormatSequence($subSequences, $uuid)
    {

        if (!empty($subSequences)) {
            foreach ($subSequences as $val) {
                if ($val['children']) {
                    $allchild = $this->GetChild($subSequences);
                    $allchild = array_flatten($allchild);
                    $col = $this->AddSequenceLineScore($allchild, $val['item_id'], $uuid, $val);
                    $col['data']  = $this->SubFormatSequence($val['children'], $uuid);
                    $this->line = $col;
                } else {
                    $this->line = $this->AddSequenceLineScore([$val['item_id']], $val['item_id'], $uuid, $val);
                }
            }
        }
        return $this->line;
    }

    protected function GetChild($arr)
    {
        $this->formattedArr = array();
        if (!empty($arr)) {
            foreach ($arr as $val) {
                //add first level
                if ($val['children']) {

                    $this->formattedArr[] = [$val['item_id']];
                    $this->returnArr = $this->GetChild($val['children']);  // call recursive function
                    if (!empty($this->returnArr)) {
                        $this->formattedArr[] = $this->returnArr;
                    }
                } else {
                    $this->formattedArr[] = [$val['item_id']];
                }
            }
        }
        return $this->formattedArr;
    }

    protected function SubFormatSequence($arr, $uuid)
    {
        if (!empty($arr)) {
            foreach ($arr as $val) {
                if ($val['children']) {
                    $allchild = $this->GetChild($arr);
                    $allchild = array_flatten($allchild);
                    $col = $this->AddSequenceLineScore($allchild, $val['item_id'], $uuid, $val);
                    $col['data'] = $this->SubFormatSequence($val['children'], $uuid);
                    $line[] = $col;
                } else {
                    $line[] = $this->AddSequenceLineScore([$val['item_id']], $val['item_id'], $uuid, $val);
                }
            }
            return  $line;
        }
    }

    protected function AddSequenceLineScore($formatSequence, $current_seq, $uuid, $survey_item)
    {
        $name = sequence::find($current_seq);
        if (count($formatSequence) > 1) {
            $this->parent_name = $this->TranslateString($name['name']);
            $this->sequence_order = 0;
        }
        $this->sequence_order++;
        $line["numbering"] = $this->sequence_order;
        $line["id"] = $current_seq;
        $line["parent"] = $this->parent_name;
        $line["display_report"] = $survey_item['display_report'];
        $line["name"] = $this->TranslateString($name['name']);
        $score = $this->_getAllSequenceScoreFromUuid($uuid, $formatSequence);

        $line["score"] = is_null($score['score']) ? null : round($score['score']);
        $line["quantity"] = $score['quantity'];
        return $line;
    }

    protected function TranslateString($string)
    {
        if (isset($string[$this->language_code])) {
            return $string[$this->language_code];
        } else if (isset($string['fr'])) {
            return $string['fr'];
        } else {
            return "no translation for this item";
        }
    }
}
