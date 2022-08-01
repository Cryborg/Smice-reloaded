<?php

namespace App\Classes\Services;

use App\Classes\Helpers\ColorsHelper;
use App\Classes\Helpers\FusionHelper;
use App\Classes\Helpers\GlobalScoreHelper;
use App\Classes\SmiceTCPDF;
use App\Exceptions\SmiceException;
use App\Models\Alias;
use App\Models\Color;
use App\Models\CriteriaA;
use App\Models\CriteriaB;
use App\Models\Mission;
use App\Models\SelectedColor;
use App\Models\Shop;
use App\Models\Society;
use App\Models\Survey;
use App\Models\Theme;
use App\Models\User;
use App\Models\WaveTarget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use \Koerel\PdfUnite\PdfUnite;
use Illuminate\Support\Facades\Storage;


class ReportPDFService extends SmiceService
{
    
}