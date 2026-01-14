<?php

 namespace Aldaflux\AldafluxIdsSanteBundle\Service;

 
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Aldaflux\AldafluxIdsSanteBundle\Service\ApiAuthentifiedService;


use Psr\Log\LoggerInterface;


class IdsUserSymfonyService
{
 
    protected $prefixe;
    protected $request;
    
    
    public function __construct(
            protected ParameterBagInterface $parameter, 
            protected LoggerInterface $logger,
            protected EntityManagerInterface $em, 
            protected TokenStorageInterface $tokenStorage,
            protected RequestStack $requestStack,
            protected EventDispatcherInterface $eventDispatcher, 
            protected ApiAuthentifiedService $apiAuthentifiedService, 
            )
    {
        $this->prefixe=$parameter->get("aldaflux_ids_sante.prefixe");
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
                    "Trouvé "=>!is_null($user),
                ];

        $this->logger->warning('Get User',$log);
        
        return($user);
    }
    
    
    public function logUserFromIdsSession()
    {
            $userIds=$this->GetUserFromIds();
//            $username = $_SERVER['HTTP_IDS_USER'];
            $username = $userIds->PresentedAuthentifier ?? $userIds->Authentifier;
            
            $user = $this->getUser($username);
            if ($user) 
            {
                $this->logger->info('User :: '. $username."/".$user);
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $this->tokenStorage->setToken($token);
                $session = $this->requestStack->getSession();
                $session ->set('_security_main', serialize($token));
                $event = new InteractiveLoginEvent($this->requestStack->getCurrentRequest(), $token);
                $this->eventDispatcher->dispatch($event, "security.interactive_login");
                return true;
            }        
            else
            {
                $this->logger->warning('User not found : '.$username);
                return false;
            }
            
    }
    
    
    public function GetUserFromIds()
    {
        $sessionIds = $this->requestStack->getCurrentRequest()->cookies->get('sessionids');
        $rawResponse=$this->apiAuthentifiedService->send41Request('authentication/AuthGetUserInfo/',null,$sessionIds);
        $userInfo = json_decode($rawResponse->getContent());
        return($userInfo);
    }
     
  
}