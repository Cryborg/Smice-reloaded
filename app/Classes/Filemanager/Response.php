<?php

namespace App\Classes\Filemanager;

class Response
{
    protected $data;

    public function __construct($data = null)
    {
        $this->setData($data);
    }

    public function flushJson()
    {
        $this->data = json_encode(['result' => $this->data]);
        return $this->flush();
    }

    public function flush()
    {
        echo $this->data;
        exit;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setHeaders($params)
    {
        if (! headers_sent()) {
            if (is_scalar($params)) {
                header($params);
            } else {
                foreach($params as $key => $value) {
                    header(sprintf('%s: %s', $key, $value));
                }
            }
        }
        return $this;
    }
}