<?php

namespace App\Classes\Filemanager;

use App\Classes\Filemanager\includes\Ftp;

class FileManager extends Ftp
{
    public function downloadTemp($path)
    {
        $localPath = tempnam(sys_get_temp_dir(), 'fmanager_');
        $path = $this->_path . $path;
        if ($this->download($path, $localPath)) {
            return $localPath;
        }
    }

    public function getContent($path)
    {
        $path = $this->_path . $path;
        $localPath = $this->downloadTemp($path);
        if ($localPath) {
            return @file_get_contents($localPath);
        }
    }
}