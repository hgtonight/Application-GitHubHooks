<?php
/* Copyright 2016 Zachary Doll */

class FileLogger extends BaseLogger {
    
    public function log($level, $message, array $context = array()) {
        $log = $level . "\n";
        $log .= print_r($message, true) . "\n";
        $log .= print_r($context, true) . "\n";
        file_put_contents(PATH_UPLOADS . '/var/log.log', $log, FILE_APPEND | LOCK_EX);
    }
}