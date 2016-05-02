<?php
/* Copyright 2016 Zachary Doll */

class FileLogger extends BaseLogger {
    
    private $logfile = PATH_UPLOADS . '/var/log.log';

    public function log($level, $message, array $context = array()) {
        $log = $level . "\n";
        $log .= formatString($message, $context) . "\n";
        $log .= print_r($context, true) . "\n";
        file_put_contents($this->logfile, $log, FILE_APPEND | LOCK_EX);
    }
}