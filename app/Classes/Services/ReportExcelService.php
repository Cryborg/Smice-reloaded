<?php

namespace App\Classes\Services;

use App\Classes\Helpers\ArrayHelper;
use App\Http\Shops\Models\Shop;
use App\Models\Answer;
use App\Models\AnswerImage;
use App\Models\Society;
use App\Models\Survey;
use App\Models\SurveyItem;
use App\Models\User;
use Cache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportExcelService extends SmiceService
{
    private $sequence_name;
    private $sequence_id;
    private $sequence_weight;
    private $xls_header;
    private $questioncolumn;
    private $nc = [];
    private $x = 'L';
    private $e = 0;
    private $data = [];
    private $missionsData = [];
    private $histo = [];
    private $ttl_cache = 60 * 7;
    private $sequences = null;

    public function exportSurveyNC(array $item, User $user, $id, string $shop_name, $time)
    {
        $this->user = $user;
        $this->data = $item;
        $template_path = base_path() . '/resources/templates/xlsx/ex_plan_action.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($template_path);
        $sheet = $spreadsheet->getActiveSheet()->setAutoFilter('A14:P14');
        $y = 15;
        $ok = ['font' => ['color' => ['rgb' => '008200']]];
        $ko = ['font' => ['color' => ['rgb' => 'FF0000']]];
        $black = ['font' => ['color' => ['rgb' => '000000']]];
        $mission_to_read = [];

        $waves = $this->generateWaves();
        foreach ($waves as $wave) {
            $mission_to_read[] = $wave['wave_target'];
        }
        //remove current wave
        array_shift($waves);

        $shopAxes = Shop::with('axes')->where('id', $item['shop_id'])->first()->toArray();
        $axes = (isset($shopAxes['axes'])) ? ArrayHelper::getIds($shopAxes['axes']) : NULL;
        $userGroups = User::with('groups')->where('id', $item['user_id'])->first()->toArray();
        $groups = (isset($userGroups['groups'])) ? ArrayHelper::getIds($userGroups['groups']) : NULL;
        $sheet->setCellValue('A1', $item['shop']);
        $sheet->setCellValue('A2', $item['program']);
        $sheet->setCellValue('E2', $item['wave']);
        //$score = GlobalScoreHelper::getGlobalScoreOneMission($item['uuid']);
        $sheet->setCellValue('D4', 'TAUX DE CONFORMITE : ');


        if ($waves) {
            $sheet->setCellValue('H1', $item['wave']);
            $sheet->setCellValue('G12', 'Nb de NC résolues depuis ' . $waves[0]['name2']);
        }
        $survey_id = $item['survey_id'];
        //find company name
        $survey = Survey::find($survey_id);
        $society = Society::find($survey['society_id']);

        $sheet->setCellValue('G5', 'Hors ' . $society['name']);


        $survey = Survey::find($survey_id)->retrieve(false, $item['scenario_id'], $axes, $groups, $item['shop_id'])->toArray();

        if (isset($survey['items'])) {
            $items = $survey['items'];
            unset($survey['items']);
        }
        $this->_prepareItems($items);
        $this->generateQuestionRowDataOneRecord($mission_to_read);
        //on parcours les réponses du questionnare et on ajoute la réponse pour la vague courante
        foreach ($this->xls_header as $item) {
            //check if anwer is present
            $array = [
                'sequence_name' => $item['sequence_name'],
                'theme' => $item['theme'],
                'question' => $item['question'],
            ];
            if (isset($this->missionsData[$id]['answers_data'][$item['question_id']])) {
                $array['answer'] = $this->missionsData[$id]['answers_data'][$item['question_id']]['answer'];
                $array['comment'] = $this->missionsData[$id]['answers_data'][$item['question_id']]['comment'];
                $array['images'] = $this->missionsData[$id]['answers_data'][$item['question_id']]['images'];
                $array['score'] = $this->missionsData[$id]['answers_data'][$item['question_id']]['score'];
            } else {
                $array['answer'] = $array['comment'] = $array['images'] = $array['score'] = NULL;
            }

            $this->nc[$item['sequence']][] = $array;
        }

        foreach ($waves as $wave) {
            $sheet->setCellValue($wave['column'] . ($y - 1), $wave['name']);
        }

        foreach ($waves as $column => $wave) {
            foreach ($this->xls_header as $item) {
                //check if anwer is present
                $array = [
                    'sequence_name' => $item['sequence_name'],
                    'theme' => $item['theme'],
                    'question' => $item['question'],
                ];
                if (isset($this->missionsData[$wave['wave_target']]['answers_data'][$item['question_id']])) {
                    $array['answer'] = $this->missionsData[$wave['wave_target']]['answers_data'][$item['question_id']]['answer'];
                    $array['comment'] = $this->missionsData[$wave['wave_target']]['answers_data'][$item['question_id']]['comment'];
                    $array['images'] = $this->missionsData[$wave['wave_target']]['answers_data'][$item['question_id']]['images'];
                    $array['score'] = $this->missionsData[$wave['wave_target']]['answers_data'][$item['question_id']]['score'];
                } else {
                    $array['answer'] = $array['comment'] = $array['images'] = $array['score'] = NULL;
                }

                $this->histo[$column][$item['sequence']][] = $array;
            }
        }


        foreach ($this->nc as $key => $seq) {
            $nbnc = 0;
            //new sequence show sum of nc
            $sheet->getRowDimension($y)->setRowHeight(200);
            $sheet->getStyle('A' . $y . ':P' . $y)->getAlignment()->setHorizontal('center')->setVertical('center');

            foreach ($this->nc[$key] as $sumnc) {
                if (($sumnc['score'] == 0) && (!is_null($sumnc['score']))) {
                    $nbnc++;
                }
            }
            $sheet->setCellValue('A' . $y, $sumnc['sequence_name']);
            $sheet->setCellValue('B' . $y, '');
            $sheet->setCellValue('C' . $y, 'Nombre de non-conformités');
            $sheet->setCellValue('D' . $y, $nbnc);
            $y++;

            foreach ($seq as $item) {
                $sheet->getRowDimension($y)->setRowHeight(200);
                $sheet->getStyle('A' . $y . ':P' . $y)->getAlignment()->setHorizontal('center')->setVertical('center');
                $sheet->setCellValue('A' . $y, $item['sequence_name']);
                $sheet->setCellValue('B' . $y, $item['theme']);
                $sheet->setCellValue('C' . $y, $item['question']);
                if (($item['score'] == 0) && (!is_null($item['score']))) {
                    $sheet->setCellValue('D' . $y, 'Non Conforme');
                    $sheet->getStyle('D' . $y)->ApplyFromArray($ko);
                } else if (($item['score'] == 100) && (!is_null($item['score']))) {
                    $sheet->setCellValue('D' . $y, 'Conforme');
                    $sheet->getStyle('D' . $y)->ApplyFromArray($ok);
                } else {
                    $sheet->setCellValue('D' . $y, $item['answer']);
                    $sheet->getStyle('D' . $y)->ApplyFromArray($black);
                }
                if ($item['images'] && $item['score'] !== 100) {
                    $url = ($item['images'][0]['url']);
                    $file = basename($url);
                    if (@file_get_contents($url)) {
                        if (!stristr($url, '/documents/')) {
                            $drawing = new Drawing();
                            //save in local from s3 storage
                            //save image from imagekit.io
                            $url = str_replace('api.smice.com', 'ik.imagekit.io/smice', $url);
                            $url = str_replace('smice/images/', 'smice/images/tr:n-pdf_report/', $url);
                            $contents = file_get_contents($url);
                            Storage::disk('local')->put('images/' . $file, $contents);
                            $drawing->setPath(storage_path('app/images/') . $file);
                            $drawing->setHeight(250);
                            $drawing->setCoordinates('E' . $y);
                            $drawing->setWorksheet($spreadsheet->getActiveSheet());
                        }
                    }
                }
                $sheet->setCellValue('G' . $y, $item['comment']);
                $y++;
            }
        }
        foreach ($this->histo as $k => $value) {
            $y = 15;
            //new sequence show sum of nc
            foreach ($value as $key => $seq) {
                $nbnc = 0;
                foreach ($seq as $sumnc) {
                    if (($sumnc['score'] == 0) && (!is_null($sumnc['score']))) {
                        $nbnc++;
                    }
                }
                $sheet->setCellValue($waves[$k]['column'] . $y, $nbnc);
                $y++;

                foreach ($seq as $item) {
                    if (($item['score'] == 0) && (!is_null($item['score']))) {
                        $sheet->setCellValue($waves[$k]['column'] . $y, 'Non Conforme');
                        $sheet->getStyle($waves[$k]['column'] . $y)->ApplyFromArray($ko);
                    } else if (($item['score'] == 100) && (!is_null($item['score']))) {
                        $sheet->setCellValue($waves[$k]['column'] . $y, 'Conforme');
                        $sheet->getStyle($waves[$k]['column'] . $y)->ApplyFromArray($ok);
                    } else {
                        $sheet->setCellValue($waves[$k]['column'] . $y, $item['answer']);
                        $sheet->getStyle($waves[$k]['column'] . $y)->ApplyFromArray($black);
                    }
                    $y++;
                }
            }
        }

        $filename = $shop_name . $time;
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/public/') . $filename . '.xlsx');
        $file = storage_path('app/public/') . $filename . '.xlsx';
        Storage::put('/public/XLS/' . $filename . '.xlsx', file_get_contents($file));

        return new Response(['filename' => $filename . '.xlsx']);
    }

    public function exportSurveyXls(array $data, User $user, $sequences = null)
    {
        $this->user = $user;
        $this->data = $data;
        if (isset($this->data['survey_id'])) { // get only one mission
            $this->survey_id = $this->data['survey_id'];
        } else {
            $this->survey_id = $this->data[0]['survey_id']; //data have more than one mission
        }
        if ($sequences) {
            $this->sequences = SurveyItem::with('children')->where('survey_id', $this->survey_id)->where('type', 'sequence')->wherein('item_id', ArrayHelper::getIds($sequences))->orderBy('order')
                ->retrieveAll()
                ->toArray();
        }

        $this->generateSurveyWithPossibleAnswers();
        $this->generateMissionsData();
        $this->generateQuestionRowData();

        //set titme
        $y_head = [
            'question_id',
            'question',
            'sequence',
            'sequence_name',
            'sequence_weight',
            'criterion_weight',
            'question_weight',
            'question_type',
            'criterion',
            'answer_options',
            'sub-sequence',
            'question_number',
            'theme',
            'job',
            'criterionA',
            'criterionB',
            'allow_comment'
        ];
        $x_head = [
            'mission_id',
            'Date',
            'Program',
            'Wave',
            'Mission',
            'User',
            'Gender',
            'Birth day',
            'Shop',
            'Status',
        ];

        $filename = 'export' . time();

        Excel::create($filename, function ($excel) use (&$y_head, &$x_head) {

            $excel->sheet('Export', function ($sheet) use (&$y_head, &$x_head) {
                // Font family
                $sheet->setFontFamily('Comic Sans MS');

                // Set font with ->setStyle()`
                $sheet->setStyle([
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 12,
                        'bold' => true
                    ],
                    'borders' => [
                        'allborders' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => ['rgb' => '9c9c9c']
                        ]
                    ]
                ]);
                $sheet->cell('18', function ($row) {
                    $row->setBackground('#CCCCCC');
                });
                $sheet->cell('K1:K17', function ($row) {
                    $row->setBackground('#CCCCCC');
                    $row->setFontSize(10);
                    $row->setFontWeight(false);
                });
                $sheet->getColumnDimension('K')->setWidth(20);
                $x = 'A';
                $y = 0;
                foreach ($y_head as $h => $value) {
                    $y++;
                    $sheet->setCellValue('K' . $y, $value);
                }

                foreach ($x_head as $h => $value) {
                    $sheet->setCellValue($x . '18', $value);
                    $x++;
                }

                //all question name
                $x = 'L';
                foreach ($this->xls_header as $h => $value) {
                    $y = 0;
                    foreach ($value as $d => $v) {
                        if ($d == 'question') {
                            $question = $v;
                        }
                        $y++;
                        $sheet->setCellValue($x . $y, $v);
                    }
                    $x++;
                    $sheet->setCellValue($x . '2', $question . '(comment)');
                    $x++;
                    $sheet->setCellValue($x . '2', $question . '(sub answer)');
                    $x++;
                    $sheet->setCellValue($x . '2', $question . '(sub answer comment)');
                    $x++;
                }

                //all answers
                // Font family
                $sheet->setFontFamily('Comic Sans MS');

                // Set font with ->setStyle()`
                $sheet->setStyle([
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 10,
                        'bold' => false
                    ]
                ]);
                $y = 19;
                foreach ($this->missionsData as $value) {
                    //general data
                    $x = 'A';
                    foreach ($value['general_data'] as $v) {
                        $sheet->setCellValue($x . $y, $v);
                        $sheet->setSize($x . $y, 25, 18);
                        $x++;
                    }
                    if (!empty($value['answers_data'])) {
                        foreach ($value['answers_data'] as $answers => $v) {
                            if (isset($this->questioncolumn[$answers])) {
                                $sheet->setCellValue($this->questioncolumn[$answers] . $y, $v['answer']);
                                $x = $this->questioncolumn[$answers];
                                $x++;
                                $sheet->setCellValue($x . $y, $v['comment']);
                                $x++;
                                $sheet->setCellValue($x . $y, $v['sub_answer']);
                                $x++;
                                $sheet->setCellValue($x . $y, $v['sub_answer_comment']);
                            }
                        }
                    }
                    $y++;
                }
            });

            $excel->sheet('Export score', function ($sheet) use (&$y_head, &$x_head) {
                // Font family
                $sheet->setFontFamily('Comic Sans MS');

                // Set font with ->setStyle()`
                $sheet->setStyle([
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 12,
                        'bold' => true
                    ]
                ]);
                $sheet->cell('18', function ($row) {
                    $row->setBackground('#CCCCCC');
                });
                $sheet->cell('K1:K17', function ($row) {
                    $row->setBackground('#CCCCCC');
                    $row->setFontSize(10);
                    $row->setFontWeight(false);
                });
                $sheet->getColumnDimension('K')->setWidth(20);
                $x = 'A';
                $y = 0;
                foreach ($y_head as $h => $value) {
                    $y++;
                    $sheet->setCellValue('K' . $y, $value);
                }
                foreach ($x_head as $h => $value) {
                    $sheet->setCellValue($x . '18', $value);
                    $x++;
                }

                //all question name
                $x = 'L';
                foreach ($this->xls_header as $value) {
                    $y = 0;
                    foreach ($value as $d => $v) {
                        if ($d == 'question') {
                            $question = $v;
                        }
                        $y++;
                        $sheet->setCellValue($x . $y, $v);
                        $sheet->setSize($x . $y, 25, 18);
                    }
                    $x++;
                    $sheet->setCellValue($x . '2', $question . '(comment)');
                    $x++;
                    $sheet->setCellValue($x . '2', $question . '(sub answer)');
                    $x++;
                    $sheet->setCellValue($x . '2', $question . '(sub answer comment)');
                    $x++;
                }

                //all answers
                // Font family
                $sheet->setFontFamily('Comic Sans MS');

                // Set font with ->setStyle()`
                $sheet->setStyle([
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 10,
                        'bold' => false
                    ]
                ]);
                $y = 19;
                foreach ($this->missionsData as $value) {
                    //general data
                    $x = 'A';
                    foreach ($value['general_data'] as $v) {
                        $sheet->setCellValue($x . $y, $v);
                        $x++;
                    }
                    if (!empty($value['answers_data'])) {
                        foreach ($value['answers_data'] as $answers => $v) {
                            if (isset($this->questioncolumn[$answers])) {
                                $sheet->setCellValue($this->questioncolumn[$answers] . $y, $v['score']);
                                $x = $this->questioncolumn[$answers];
                                $x++;
                                $sheet->setCellValue($x . $y, "");
                                $x++;
                                $sheet->setCellValue($x . $y, "");
                                $x++;
                                $sheet->setCellValue($x . $y, "");
                            }
                        }
                    }
                    $y++;
                }
            });

            //$excel->sheet('Export criteria', function ($sheet) use(&$question_rows) {
            //    $sheet->fromArray($question_rows, null, '', true);
            //});

        })->store('xlsx', storage_path('app/public'));
        $file = storage_path('app/public/') . $filename . '.xlsx';
        Storage::put('/public/XLS/' . $filename . '.xlsx', file_get_contents($file));

        return new Response(['filename' => $filename . '.xlsx']);
    }

    private function generateWaves()
    {
        $wave = [];
        $waves = 'G';
        $res = DB::table('show_wave_with_missions')
            ->where('mission_id', $this->data['mission_id'])
            ->where('shop_id', $this->data['shop_id'])
            ->select('wave_name', 'wave_id', 'wave_target_id')
            ->groupby('wave_name', 'wave_id', 'wave_target_id', 'date_start')
            ->orderBy('date_start', 'desc')
            ->take(5)
            ->get();
        foreach ($res as $item) {
            if (!isset($wave[$item['wave_id']])) {
                $wave[$item['wave_id']] = [
                    'id' => $item['wave_id'],
                    'name' => 'Rappel: ' . $item['wave_name'],
                    'name2' => $item['wave_name'],
                    'wave_target' => $item['wave_target_id'],
                    'column' => $waves++
                ];
            }
        }

        return $wave;
    }

    private function generateSurveyWithPossibleAnswers()
    {
        $survey = Survey::find($this->survey_id)->retrieve(false, false, false, false, false, false, true)->toArray();
        if (isset($survey['items'])) {
            $items = $survey['items'];
            unset($survey['items']);
        }

        $this->_prepareItems($items);
    }

    function GetChild($arr, $clean = true)
    {
        $cachekey = 'GetChild' . serialize($arr);
        if ($clean === true) {
            $this->formattedArr = [];
            Cache::forget($cachekey);
        }
        $disable_cache = "";
        if (isset($this->user) && $this->user->disable_cache) {
            $disable_cache = "1";
        }
        $subSequences = Cache::Get($cachekey . $disable_cache, function () use ($cachekey, $arr) {
            if (!empty($arr)) {
                foreach ($arr as $val) {
                    //add first level
                    if ($val['children']) {
                        $this->formattedArr[] = [$val['item_id']];
                        $this->returnArr = $this->GetChild($val['children'], false); // call recursive function
                        if (!empty($this->returnArr)) {
                            $this->formattedArr[] = $this->returnArr;
                        }
                    } else {
                        $this->formattedArr[] = [$val['item_id']];
                    }
                }
            }
            Cache::Put($cachekey, $this->formattedArr, $this->ttl_cache);
            return $this->formattedArr;
        });
        return $subSequences;
    }

    private function _prepareItems($items)
    {
        if ($this->sequences) {
            $allchild = $this->GetChild($this->sequences);
            $allchild = array_flatten($allchild);
            //get parent_id
            $parent_id = SurveyItem::select('id')->where('survey_id', $this->survey_id)->where('type', 'sequence')->wherein('item_id', $allchild)->get()->toarray();
            $parent_id = ArrayHelper::getIds($parent_id);
        }

        foreach ($items as $item) {
            if ((!$this->sequences) || ($this->sequences && (array_search($item['item_id'], $allchild) !== false || array_search($item['parent_id'], $parent_id) !== false))) {
                if (!isset($item['type'])) {
                    $item['type'] = SurveyItem::ITEM_QUESTION;
                }
                if ($item['type'] == SurveyItem::ITEM_SEQUENCE) {
                    $this->sequence_name = $item['sequence']['name'][$this->user->language->code];
                    $this->sequence_id = $item['sequence']['id'];
                    $this->sequence_weight = $item['sequence']['weight'];
                    if ($item['items']) {
                        $nested_items = $item['items'];
                        foreach ($nested_items as $nested_item) //recusivité des sous sequences
                        {
                            $this->e++;
                            $this->_prepareItems([$nested_item]);
                        }
                    }
                } else if ($item['type'] == SurveyItem::ITEM_QUESTION) {
                    $criterion_a = $criterion_b = $jobs = $themes = $answers_array = $sub_answers_array = [];
                    //first is seq
                    if ($this->x !== 'L') {
                        for ($i = 0; $i < 3; $i++) {
                            $this->questioncolumn[$item['item_id']] = $this->x++;
                        }
                    } else {
                        $this->questioncolumn[$item['item_id']] = $this->x++;
                    }

                    $this->xls_header[$this->e]['question_id'] = $item['item_id'];
                    $this->xls_header[$this->e]['question'] = $item['question']->name[$this->user->language->code];
                    $this->xls_header[$this->e]['sequence'] = $this->sequence_id;
                    $this->xls_header[$this->e]['sequence_name'] = $this->sequence_name;
                    $this->xls_header[$this->e]['sequence_weight'] = $this->sequence_weight;
                    $this->xls_header[$this->e]['criterion_weight'] = $item['criteria_weight'];
                    $this->xls_header[$this->e]['question_weight'] = $item['weight'];
                    $this->xls_header[$this->e]['question_type'] = $item['question']->type;
                    $this->xls_header[$this->e]['criterion'] = empty($item['criteria']->name[$this->user->language->code]) ? '' : $item['criteria']->name[$this->user->language->code];
                    $this->xls_header[$this->e]['answer_options'] = null;
                    $this->xls_header[$this->e]['sub-sequence'] = null;
                    $this->xls_header[$this->e]['question_number'] = null;
                    $this->xls_header[$this->e]['theme'] = null;
                    $this->xls_header[$this->e]['job'] = null;
                    $this->xls_header[$this->e]['criterionA'] = null;
                    $this->xls_header[$this->e]['criterionB'] = null;
                    $this->xls_header[$this->e]['allow_comment'] = null;
                    $this->xls_header[$this->e]['scoring'] = $item['scoring'];
                    if (!$item['criterionA']->isEmpty()) {
                        foreach ($item['criterionA'] as $criterion_a_item) {
                            array_push($criterion_a, $criterion_a_item['name'][$this->user->language->code]);
                        }
                        $this->xls_header[$this->e]['criterionA'] = implode('| ', $criterion_a);
                    }
                    if (!$item['criterionB']->isEmpty()) {
                        foreach ($item['criterionB'] as $criterion_b_item) {
                            array_push($criterion_b, $criterion_b_item['name'][$this->user->language->code]);
                        }
                        $this->xls_header[$this->e]['criterionB'] = implode('| ', $criterion_b);
                    }

                    if (!$item['jobs']->isEmpty()) {
                        foreach ($item['jobs'] as $job) {
                            array_push($jobs, $job['name'][$this->user->language->code]);
                        }
                        $this->xls_header[$this->e]['job'] = implode('| ', $jobs);
                    }

                    if (!$item['themes']->isEmpty()) {
                        foreach ($item['themes'] as $theme) {
                            array_push($themes, $theme['name'][$this->user->language->code]);
                        }
                        $this->xls_header[$this->e]['theme'] = implode('| ', $themes);
                    }

                    foreach ($item['question']->answers as $answer) {
                        array_push($answers_array, $answer['name'][$this->user->language->code]);
                        foreach ($answer->comments as $comment) {
                            array_push($sub_answers_array, $comment['name'][$this->user->language->code]);
                        }
                    }
                    $this->xls_header[$this->e]['answer_options'] = implode('| ', $answers_array);
                    $this->xls_header[$this->e]['sub_answer_options'] = implode('| ', $sub_answers_array);
                    $this->x++;
                }
                $this->e++;
            }
        }
    }

    private function generateMissionsData()
    {
        $user_ids = $result_data = [];

        foreach ($this->data as $item) {
            $user_ids[] = $item['user_id'];
        }

        $user_ids = array_unique($user_ids);
        $users = User::withTrashed()->whereIn('id', $user_ids)->get();
        $user_ids = [];

        foreach ($users as $user) {
            $user_ids[$user->id] = $user;
        }

        foreach ($this->data as $item) {
            $result_data[$item['id']]['general_data'] = [
                $item['id'],
                $item['visit_date'],
                $item['program'],
                $item['wave'],
                $item['mission'],
                $item['user_id'],
                !is_null($item['user_id']) ? $user_ids[$item['user_id']]->gender : NULL,
                !is_null($item['user_id']) ? $user_ids[$item['user_id']]->birth_date : NULL,
                $item['shop'],
                $item['status'],
            ];
        }

        $this->missionsData = $result_data;
    }

    private function generateQuestionRowData()
    {
        foreach ($this->data as $item) {
            $answers = Answer::with('question_row', 'comments')
                ->where('user_id', $item['user_id'])
                ->where('wave_target_id', $item['id'])
                ->orderby('question_row_id')
                ->get()
                ->toArray();
            $i = 0;
            $question_id = '';
            foreach ($answers as $a) {
                $question_row_name = $sub_comment_name = $sub_comment_comment_name = '';
                if (isset($a['question_row'])) {
                    $question_row_name = $a['question_row']['name'][$this->user->language->code];
                }
                if (count($a['comments']) > 0) {
                    foreach ($a['comments'] as $sub_comment) {
                        $sub_comment_name .= $sub_comment['questionrowcomment'][0]['name'][$this->user->language->code] . ", ";
                        $sub_comment_comment_name .= $sub_comment['comment'];
                    }
                }
                if ($question_id == $a['question_id']) {
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['answer'] = $previous_answer . ";" . (($question_row_name) ? $question_row_name : $a['value']);
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['comment'] = $previous_comment . ";" . $a['comment'];
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['sub_answer'] = $previous_sub_answer . ";" . ($sub_comment_name) ? $sub_comment_name : "";
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['sub_answer_comment'] = $previous_sub_answer_comment . ";" . ($sub_comment_comment_name) ? $sub_comment_comment_name : "";
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['score'] = $previous_score . ";" . (isset($a['question_row'])) ? $a['question_row']['value'] : "";
                } else {
                    $previous_answer = '';
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['answer'] = ($question_row_name) ? $question_row_name : $a['value'];
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['score'] = ($a['question_row']) ? $a['question_row']['value'] : "";
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['comment'] = $a['comment'];
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['sub_answer'] = ($sub_comment_name) ? $sub_comment_name : "";
                    $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['sub_answer_comment'] = ($sub_comment_comment_name) ? $sub_comment_comment_name : "";
                }
                //add images
                //$userImages = AnswerImage::where('answer_id', $a['id'])->get()->toArray();
                //$this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['images'] = $userImages;

                $previous_comment = $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['comment'];
                $previous_answer = $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['answer'];
                $previous_score = $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['score'];
                $previous_sub_answer = $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['sub_answer'];
                $previous_sub_answer_comment = $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['sub_answer_comment'];
                $question_id = $a['question_id'];
                $i++;
            }
        }
    }

    private function generateQuestionRowDataOneRecord($ids)
    {
        foreach ($ids as $id) {
            $aw = DB::table('show_answer_order')
                ->where('wave_target_id', $id)
                ->get();
            $i = 0;
            foreach ($aw as $a) {
                $question_row_name = '';
                if (isset($a['question_row_name'])) {
                    $question_row_name = json_decode($a['question_row_name'])->{$this->user->language->code};
                }

                $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['answer'] = ($question_row_name) ? $question_row_name : $a['value'];
                $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['score'] = $a['question_row_value'];
                $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['comment'] = $a['comment'];

                //add images
                $userImages = AnswerImage::where('answer_id', $a['id'])->get()->toArray();
                $this->missionsData[$a['wave_target_id']]['answers_data'][$a['question_id']]['images'] = $userImages;
                $i++;
            }
        }
    }
}
