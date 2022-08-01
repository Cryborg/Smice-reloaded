<?php

namespace App\Classes\SmiceClasses;

class SmiceLog
{
    private $log_file               = false;

    private $log                    = "";

    private $nb_queries             = 0;

    private $time_start_logging     = null;

    private $date_start_logging     = null;

    static private $instance        = null;

    private function                __construct()
    {
        $this->time_start_logging   = microtime(true);
        $this->date_start_logging   = date('Y-m-d H:i:s', time());

        try {
            $this->log_file = fopen(storage_path('logs/query.log'), 'a');
        }
        catch (\Exception $e) {}
    }

    static public function          getInstance($log_name = null)
    {
        if (self::$instance === null)
            self::$instance = (new self())->_initLog($log_name);

        return self::$instance;
    }

    private function                _initLog($log_name)
    {
        $this->log                  .= "-------------------------------------\n";
        $this->log                  .= "| Date ". $this->date_start_logging ."\n";
        $this->log                  .= "| ". $log_name . "\n|\n";

        return $this;
    }

    public function                 addQuery($query, $bindings, $time, $name)
    {
        $this->log                  .= "| Query: ". strval($query) ."\n";
        $this->log                  .= "| Bindings: ". json_encode($bindings) ."\n";
        $this->log                  .= "| Execution time: ". strval($time) ." ms\n";
        $this->log                  .= "| Connection: ". strval($name) ."\n";
        $this->log                  .= "|\n";
        $this->nb_queries++;

        return true;
    }

    public function                 dump()
    {
        $execution_time             = number_format(microtime(true) - $this->time_start_logging, 3);
        $this->log                  .= "---- TOTAL ". $this->nb_queries ." queries in $execution_time s ----\n";

        if ($this->log_file === false)
        {
            // Destroy the logger instance
            self::$instance = null;

            return false;
        }
        // One day we will log in a BigData table
        fwrite($this->log_file, $this->log);
        fclose($this->log_file);
        // Destroy the logger instance
        self::$instance = null;

        return true;
    }
}