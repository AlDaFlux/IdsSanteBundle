<?php

 namespace Aldaflux\AldafluxIdsSanteBundle\Service;

use Psr\Log\LoggerInterface;


class IDSLog
{
    protected static  $idslogs = array();
    protected static  $errorlogs = array();

    protected $counter = 0;
    protected $stopwatch;
 
    
    public function __construct(protected LoggerInterface $logger)
    {
        
    }
    
    public function getName()
    {
        return 'ids_log';
    }
    
    public function getLogs()
    {
        return self::$idslogs;
    }
    public function getErrorLogs()
    {
        return self::$errorlogs;
    }


    public function addErrorLog($erroLog,$title="Error")
    {
        self::$errorlogs[]=$erroLog;  
        $this->logger->error($title, $erroLog);
    }
     
    public function addLog($log,$title="Info")
    {
        self::$idslogs[]=$log;  
        $this->logger->info($title, $log);
    }
     
     
}