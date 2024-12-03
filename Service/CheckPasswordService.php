<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Service;

use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordIn;
use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordOut;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
         
use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;


class CheckPasswordService 
{
        
    public function __construct( 
            private ParameterBagInterface $parameter,
            private UserPasswordHasherInterface $passwordHasher,
            private EntityManagerInterface $em, 
            private LoggerInterface $logger, 
            private UrlGeneratorInterface $router  )
    {
        $this->prefixe=$parameter->get("aldaflux_ids_sante.prefixe");

    }
    
    
    
    public function CheckPassword(CheckPasswordIn $CheckPasswordRequest) :  ? CheckPasswordOut
    {

        $this->logger->info("CheckPassword");
        
        $CheckPasswordOut = new CheckPasswordOut();
    
        if (empty($CheckPasswordRequest->Authentifier) || empty($CheckPasswordRequest->OrganizationalUnit) || empty($CheckPasswordRequest->Password)) {
            $clientreponse = "Requete d'identification invalide. Les identifiants sont vides";
            $this->logger->warning($clientreponse);
//          throw new \Exception($clientreponse);            
            $CheckPasswordOut->IsValid = false;
            $CheckPasswordOut->CheckPasswordUserInfo = "DenialReason=".$clientreponse.";";
            return $CheckPasswordOut;
        }
        
        $method=$this->parameter->get("aldaflux_ids_sante.user.find_by");
        $username= substr($CheckPasswordRequest->Authentifier, strlen($this->prefixe));
        
        $user=$this->em->getRepository($this->parameter->get("aldaflux_ids_sante.user.class"))->{$method}($username);

        
        if ($user) 
        {
            $this->logger->info("User {username} trouvé dans la base", ['username'=>$username]);

            if ($this->passwordHasher->isPasswordValid($user, $CheckPasswordRequest->Password))
            {
                $CheckPasswordOut->IsValid = true;
                
                $CheckPasswordOut->CheckPasswordUserInfo = "Authentifier=".$user->GetUsername().";OrganizationalUnit=Utilisateur;";
                

                if (isset($this->parameter->get("aldaflux_ids_sante.counter_call.route_name")) &&  $this->parameter->get("aldaflux_ids_sante.counter_call.route_name"))
                {
                    $route="http://".$this->parameter->get("aldaflux_ids_sante.proxy.ip");
                    $route.=$this->router->generate("app_sent_otp"); 
                    $route= urlencode($route);
                    $CheckPasswordOut->CheckPasswordUserInfo.="CounterCall=client:".$route.";";
                }
                else
                {
                    $CheckPasswordOut->CheckPasswordUserInfo.="CounterCall=mail:".$user->GetEmail().";";
                }

                $this->logger->info("CheckPasswordUserInfo : ".$CheckPasswordOut->CheckPasswordUserInfo);

                
                
                $this->logger->info("Mot de passe OK");
            }
            else
            {
                $CheckPasswordOut->IsValid = false;
                $CheckPasswordOut->CheckPasswordUserInfo = $this->parameter->get("application_name")." - DenialReason=Bad Password;";
                $this->logger->warning("Mot de passe non concordant", ['username'=>$username]);
            }
        }
        else 
        {
            $this->logger->warning("User {username} non trouvé dans la base", ['username'=>$username]);
            $CheckPasswordOut->IsValid = false;
            $CheckPasswordOut->CheckPasswordUserInfo = $this->parameter->get("application_name")." - DenialReason=User '".$username."' unknow;";
        }

        return $CheckPasswordOut;
    }
 
}
 