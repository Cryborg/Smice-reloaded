<?php

namespace App\Classes\Reports;

use App\Exceptions\SmiceException;

interface ReportPDFInterface
{
    /**
     * @param string $uuid
     * @return string
     * @throws SmiceException
     */
    public function generateReport(string $uuid, $forceupdate = false, $alert = null);
}