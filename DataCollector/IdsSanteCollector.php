<?php

namespace Aldaflux\AldafluxIdsSanteBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Aldaflux\AldafluxIdsSanteBundle\Service\IDSLog;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;



class IdsSanteCollector extends AbstractDataCollector
{
  
    private $service;
    private $ids_active;
    private $ids_user;
    private $ids_appilcation_name;
    
    private $wsdlLog;
    
    private $apiUrl;
   
    private $profiles;

  
//    public function __construct(ParameterBagInterface $parameter)
    public function __construct(IDSLog $service,ParameterBagInterface $parameter)
    {
        $this->service = $service;
        $this->ids_appilcation_name = $parameter->Get("aldaflux_ids_sante.application_name");
        $this->ids_active = $parameter->Get("aldaflux_ids_sante.active");
        $this->apiUrl = $parameter->Get("aldaflux_ids_sante.api_root_url");
        
        if ( isset($_SERVER['HTTP_IDS_USER'])) $this->ids_user = $_SERVER['HTTP_IDS_USER'];
        else $this->ids_user = "";
    }   
    
    
    public function getName() : string
    {
        return 'aldaflux.ids_sante_collector';
    }
    
    
     public function reset(): void
    {
        $this->data = [];
    }

    
    public function collect(Request $request, Response $response, \Throwable $exception = null) : void
    {
        
        if (count($this->service->getLogs())==1)
        {
            $firtLineLog=$this->service->getLogs()[0];
            $title=$firtLineLog["extra"];
            
            //$title="firtLineLog->Extra";
        }
        else
        {
            $title=null;
        }
             $this->data = array(
            'idslogs' => $this->service->getLogs(), 
            'errorlogs' => $this->service->getErrorLogs(), 
            'service' => $this->service,
            'title' => $title,
            'wsdl_log' => $this->wsdlLog,
            'api_url' => $this->apiUrl,
            'ids_active' => $this->ids_active,
            'ids_appilcation_name' => $this->ids_appilcation_name,
            'ids_user' => $this->ids_user,
        );
    }

  
    
    public function getIDSActive()
    {
        return $this->data['ids_active'];
    }
    
    
    public function getWsdlLog()
    {
        return $this->data['wsdl_log'];
    }
    
    public function getApiUrl()
    {
        return $this->data['api_url'];
    }
    
    
    public function getTitle()
    {
        return $this->data['title'];
    }
    
    
    public function getApplicationIDSName()
    {
        return $this->data['ids_appilcation_name']; 
    }
    
    public function getApplicationIDSName2()
    {
        return $this->data['ids_appilcation_name2']; 
    }
    
    
    
     public function getIDSLogs()
    {
        return $this->data['idslogs']; 
    }
    
     public function getLogCount()
    {
        return count($this->data['idslogs']); 
    }
     
     public function getIDSLogsError()
    {
        return $this->data['errorlogs']; 
    }
     public function getErrorLogCount()
    {
        return count($this->data['errorlogs']); 
    }
     
    
    
    public function getIdsUser()
    {
        if ( isset($_SERVER['HTTP_IDS_USER'])) {return($_SERVER['HTTP_IDS_USER']);}
    }
    public function getSessionIds()
    {
        if ( isset($_COOKIE["sessionids"])) {return($_COOKIE["sessionids"]);}
    }
    
    
    
    
    
            
    public static function getTemplate(): ?string
    {
        return '@AldafluxIdsSante/data_collector/ids_sante_collector.html.twig';
    }
    
    
    
 
    
    
    
    
}