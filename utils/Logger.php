<?php

// utils/Logger.php
// utils/Logger.php
class Logger
{
    private $log_file;
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';

    public function __construct($log_file = 'logs/app.log')
    {
        $this->log_file = $log_file;
        // Créer le dossier logs s'il n'existe pas
        if (!is_dir(dirname($this->log_file))) {
            mkdir(dirname($this->log_file), 0777, true);
        }
    }

    public function error($message)
    {
        $this->log(self::ERROR, $message);
    }

    public function warning($message)
    {
        $this->log(self::WARNING, $message);
    }

    public function info($message)
    {
        $this->log(self::INFO, $message);
    }
}