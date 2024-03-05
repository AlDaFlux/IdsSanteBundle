<?php

 namespace Aldaflux\AldafluxIdsSanteBundle\Service;


 
use Aldaflux\AldafluxIdsSanteBundle\Utils\logLine;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

use Psr\Log\LoggerInterface;


class IDSLog
{
    private $application;
    private $user;
    private $active;
    
    private $options;
    private $wsdl;
    protected static  $idslogs = array();
    protected static  $errorlogs = array();
    
    protected $profiles = array();
    
     /**
     * @var integer
     */
    protected $counter = 0;
    
    
    
    /**
     * @var Stopwatch $stopwatch Symfony profiler Stopwatch service
     */
    protected $stopwatch;
    
    
    public function __construct(ParameterBagInterface $parameter, TokenStorageInterface $token, RequestStack $requestStack , LoggerInterface $logger)
    {
        $this->application = $parameter->Get("aldaflux_ids_sante.application_name");
        $this->options =array('compression'=>true,'exceptions'=>true,'trace'=>true);
        $this->wsdl = $parameter->Get("aldaflux_ids_sante.soap.wsdl.log");

        $this->user="USER NON DEFINI";
        if ($token->getToken())
        {
            if ($token->getToken()->getUser()!="anon.")
            {
                $this->user = $token->getToken()->getUser()->GetUsername();
                //$this->user = $parameter->Get("aldaflux_ids_sante.prefixe").$token->getToken()->getUser()->GetUsername();
            }
        }
        
        $this->active=$parameter->Get("aldaflux_ids_sante.active");
        $this->url=$requestStack->getCurrentRequest()?->getRequestUri();
        $this->logger=$logger;
        
        
    }
    
    public function getLogs()
    {
        return self::$idslogs;
    }
    public function getErrorLogs()
    {
        return self::$errorlogs;
    }

    public function getName()
    {
        return 'ids_log';
    }

    public function log($patient,$extra,$accessType=0)
    {

        $logline=new logLine();
        $logline->Patient=$patient->GetId();
        $logline->Requester=$this->user;
        $logline->Unit="na";
        $logline->Application=$this->application;
        $logline->PageName=$this->url;
        $logline->OrganizationUnit = "Utilisateur";
        $logline->Unit="na";
        $logline->AccessType = $accessType; // consultation
        $logline->Extra = $extra;
        $logline->Unit="na";


        

        if (isset($_COOKIE["sessionids"]))
        {
            $cookie=$_COOKIE["sessionids"];
            $logline->AuthCookie=$cookie;
            
            try 
            {
                $service = new \SoapClient($this->wsdl, $this->options);
            }
            catch (\Exception $exeption) 
            {
        
                
                $erroLog=array();
                $erroLog["faultcode"]=$exeption->faultcode;
                $erroLog["faultstring"]=utf8_decode($exeption->faultstring); 
                $this->addErrorLog($erroLog, "Erreur SOAP");
                return(1);
            }
            
            
            $this->logger->info("idslog", (array) $logline);
            
            $reponse = $service->LogAccess($logline);

            if (is_soap_fault($reponse)) 
            {
                $erroLog=array();
                $erroLog["faultcode"]=$reponse->faultcode;
                $erroLog["faultstring"]=utf8_decode($reponse->faultstring); 
                $this->addErrorLog($erroLog, "Erreur SOAP");
            }
        }
        else
        {
            $this->logger->info("idslog", (array) $logline);
            $logline->AuthCookie="Pas de cookie d'authentification";
            $erroLog=array();
            $erroLog["faultcode"]=321;
            $erroLog["faultstring"]="Pas de cookie d'authentification"; 
            $this->addErrorLog($erroLog, "Pas de cookie d'authentification");
        }
         
        self::$idslogs[]=$logline;
 
 
    }
    

    public function addErrorLog($erroLog,$title="Error")
    {
        self::$errorlogs[]=$erroLog;  
        $this->logger->error($title, $erroLog);
    }

     
    public function consult($patient,$extra)
    { 
        $this->log($patient,$extra,0);
    }
    
    public function create($patient,$extra)
    { 
        $this->log($patient,$extra,1);
    }
    
    public function modify($patient,$extra)
    { 
        $this->log($patient,$extra,2);
    }
    
   
    public function delete($patient,$extra)
    { 
        $this->log($patient,$extra,3);
    }
    
   
    
}