<?php

 namespace Aldaflux\AldafluxIdsSanteBundle\Service;

 
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


use Psr\Log\LoggerInterface;


class IdsUserSymfonyService
{
       
    protected $parameter;
    protected $logger;
    protected $em;
    protected $prefixe;
    
    
    public function __construct(ParameterBagInterface $parameter, LoggerInterface $logger,EntityManagerInterface $em)
    {
        $this->prefixe=$parameter->get("aldaflux_ids_sante.prefixe");
        $this->logger=$logger;
        $this->parameter=$parameter;
        $this->em=$em;
    }
    
    
    public function getUser($usernameWPrefix)
    {
        $method=$this->parameter->get("aldaflux_ids_sante.user.find_by");
        
        if (substr($usernameWPrefix,0,strlen($this->prefixe))==$this->prefixe)
        {
            $username= substr($usernameWPrefix, strlen($this->prefixe));
        }
        else
        {
            $username= $usernameWPrefix;
        }
        
        
        
        $user=$this->em->getRepository($this->parameter->get("aldaflux_ids_sante.user.class"))->{$method}($username);
        
        
        $log=[ 
                    "user Prefixe "=>$this->parameter->get("aldaflux_ids_sante.prefixe"),
                    "user avec  Prefixe "=>$usernameWPrefix,
                    "user sans Prefixe "=>$username,
                    "user Classe"=>$this->parameter->get("aldaflux_ids_sante.user.class"),
                    "Methode "=>$method,
                    "Methode "=>$method,
                    "Trouvé "=>!is_null($user),
                ];
        if ($user)
        {
            $this->logger->info('Get User',$log);
        }
        else
        {
            $this->logger->warning('Get User',$log);
        }
        
        return($user);
    }
    
    
    public function logUserFromIdsSession()
    {
        $username = $_SERVER['HTTP_IDS_USER'];
        
            $user = $this->getUser($username);
            if ($user) {
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
//                $event = new InteractiveLoginEvent($request, $token);
//                $eventDispatcher->dispatch($event, "security.interactive_login");
                return true;
            }        
            return false;
    }
    
    
    
    
  
}