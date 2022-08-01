<?php

namespace App\Classes\Factory;

use App\Classes\Reports\ReportImagePDF;
use App\Classes\Reports\ReportNotCompliantPDF;
use App\Classes\Reports\ReportStandardPDF;
use App\Classes\Reports\ReportSynteticPDF;
use App\Classes\Reports\ReportThemePDF;
use App\Classes\Reports\ReportAlertPDF;
use App\Models\User;

class ReportPdfFactory
{
    private static $user;

    const TYPE_STANDARD = 'Standard';
    const TYPE_IMAGE = 'Image';
    const TYPE_NOT_COMPLIANT = 'Not compliant';
    const TYPE_SYNTETIC = 'Syntetic';
    const TYPE_THEME = 'Theme';
    const TYPE_ALERT = 'Alert';


    public function __construct(User $user = null)
    {
        self::$user = $user;
    }

    public static function create(string $type)
    {
        if ($type === self::TYPE_STANDARD) {
            return new ReportStandardPDF(self::$user);
        } elseif ($type === self::TYPE_NOT_COMPLIANT) {
            return new ReportNotCompliantPDF(self::$user);
        } elseif ($type === self::TYPE_IMAGE) {
            return new ReportImagePDF(self::$user);
        } elseif ($type === self::TYPE_SYNTETIC) {
            return new ReportSynteticPDF(self::$user);
        } elseif ($type === self::TYPE_THEME) {
            return new ReportThemePDF(self::$user);
        } elseif ($type === self::TYPE_ALERT) {
            return new ReportAlertPDF(self::$user);
        }

        throw new \InvalidArgumentException('Unknown type given');
    }
}