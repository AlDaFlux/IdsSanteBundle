<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Service;

use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordIn;
use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordOut;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;



use \SoapFault;

class CheckPasswordService 
{

        
    private $passwordHasher;
    private $em;
    private $parameter;
    private $logger;
        
    public function __construct(ParameterBagInterface $parameter,UserPasswordHasherInterface $passwordHasher,EntityManagerInterface $em, LoggerInterface $logger  )
    {
        $this->passwordHasher=$passwordHasher;
        $this->em=$em;
        $this->parameter=$parameter;
        $this->logger=$logger;
        $this->prefixe=$parameter->get("aldaflux_ids_sante.prefixe");

    }
    
    
    
    public function CheckPassword(CheckPasswordIn $CheckPasswordRequest) 
    {
 
        if (empty($CheckPasswordRequest->Authentifier) || empty($CheckPasswordRequest->OrganizationalUnit) || empty($CheckPasswordRequest->Password)) {
            $clientreponse = "Requete d'identification invalide";
            $this->logger->warning($clientreponse);
            throw new SoapFault("Erreur ", utf8_encode($clientreponse));
            return;
        }

        $method=$this->parameter->get("aldaflux_ids_sante.user.find_by");
        $username= substr($CheckPasswordRequest->Authentifier, strlen($this->prefixe));
        $user=$this->em->getRepository($this->parameter->get("aldaflux_ids_sante.user.class"))->{$method}($username);
        
        $CheckPasswordOut = new CheckPasswordOut();
        
        
        if ($user) 
        {

            $this->logger->info("User {username} trouvé dans la base", ['username'=>$username]);

            if ($this->passwordHasher->isPasswordValid($user, $CheckPasswordRequest->Password))
            {
                $CheckPasswordOut->IsValid = true;
                $CheckPasswordOut->CheckPasswordUserInfo = "Authentifier=".$user->GetUsername().";OrganizationalUnit=Utilisateur;CounterCall=mail:".$user->GetEmail().";";
                $this->logger->info("Mot de passe OK");
            }
            else
            {
                $CheckPasswordOut->IsValid = false;
                $CheckPasswordOut->CheckPasswordUserInfo = "DenialReason=Bas Pasword;";
                $this->logger->warning("Mot de passe non concordant", ['username'=>$username]);
            }
        }
        else 
        {
            $this->logger->warning("User {username} non trouvé dans la base", ['username'=>$username]);
            $CheckPasswordOut->IsValid = false;
            $CheckPasswordOut->CheckPasswordUserInfo = "DenialReason=User unknow;";
        }

        return $CheckPasswordOut;
    }
 
}
 