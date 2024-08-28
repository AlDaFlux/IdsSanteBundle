<?php

 namespace Aldaflux\AldafluxIdsSanteBundle\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\HttpClient\Exception\ClientException;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class IdsMainService
{
    private $application;
    private $user;
    private $active;
    
    private $options;
    private $wsdl;
    

    protected $profiles = array();
    
 
    
    public function __construct(
            ParameterBagInterface $parameter, 
            TokenStorageInterface $token, 
            RequestStack $requestStack , 
            LoggerInterface $logger,
            protected IDSLog $IDSLog,
            protected  ApiAuthentifiedService $apiAuthentifiedService, 
            )
    {
        $this->application = $parameter->Get("aldaflux_ids_sante.application_name");
        $this->options =array('compression'=>true,'exceptions'=>true,'trace'=>true);
        $this->wsdl = $parameter->Get("aldaflux_ids_sante.soap.wsdl.log");

        $this->user="USER NON DEFINI";
        if ($token->getToken())
        {
            if ($token->getToken()->getUser()!="anon.")
            {
                $this->user = $token->getToken()->getUser();
            }
        }
        
        $this->active=$parameter->Get("aldaflux_ids_sante.active");
        $this->url=$requestStack->getCurrentRequest()?->getRequestUri();
        $this->logger=$logger;
    }
    
    
    
    
    public function getUser(): User
    {
        return $this->user;
    }
      
    public function log($patient,$extra,$accessType=0)
    {
        $sessionIds="";
        $body = new \stdClass();
        $body->accessType = $accessType;
        $body->pageName = $this->url;
        $body->patient = $patient->GetId()." : ".$patient->__toString();
        $body->extra = $extra;

        if (isset($_COOKIE["sessionids"]))
        {
            $sessionIds=$_COOKIE["sessionids"];
        }
        else
        {
            if ($this->active)
            {
                $erroLog=array();
                $erroLog["faultcode"]=321;
                $erroLog["faultstring"]="Pas de cookie d'authentification"; 
                $this->IDSLog->addErrorLog($erroLog, "Pas de cookie d'authentification");
                return 1;
            }
        }
        
        
        if ($this->active)
        {
            try 
                    {
                        $rawResponse=$this->apiAuthentifiedService->send41Request('traces', $body, $sessionIds);
                        $response = json_decode($rawResponse->getContent());
                        $documentId=$response[0]->documentId;

                        $rawResponse = $this->apiAuthentifiedService->send41Request('traces/read/'.$documentId, null, $sessionIds);
                        $response = json_decode($rawResponse->getContent());
                        $this->IDSLog->addLog((array) $response);
                    }
                    catch (\Exception $exeption) 
                    {
                        $erroLog=array();
                        if ($exeption instanceof ClientException )
                        {
                            $erroLog["faultcode"]=$exeption->getCode();
                            $erroLog["faultstring"]=$exeption->getMessage();
                        }
                        elseif ($exeption instanceof  \ErrorException)
                        {
                            $erroLog["faultcode"]=$exeption->getCode();
                            $erroLog["faultstring"]=utf8_decode($exeption->getMessage()); 
                        }
                        else
                        {
                            $erroLog["faultcode"]=$exeption->faultcode;
                            $erroLog["faultstring"]=utf8_decode($exeption->faultstring); 
                        }
                        $this->IDSLog->addErrorLog($erroLog, "Erreur REST");
                        return(1);
                  }
        }
        else
        {
            $body->documentId=9999;
            $body->application=$this->application;
            $body->session=$sessionIds;
            $body->organizationalUnit="organizationalUnit???";
            $body->logDate=(new \DateTime())->format("Y-md H:i");
            $body->user= $this->getUser();
            $this->IDSLog->addLog((array) $body);
        }
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