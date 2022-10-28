<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Service;

use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordIn;
use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordOut;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;



use \SoapFault;

use Aldaflux\AldafluxIdsSanteBundle\Service\IdsUserSymfonyService;


class CheckPasswordService 
{

        
    private $passwordHasher;
    private $em;
    private $parameter;
    private $logger;
    private $idsUserSymfony;
        
    public function __construct(ParameterBagInterface $parameter,UserPasswordHasherInterface $passwordHasher,EntityManagerInterface $em, LoggerInterface $logger ,IdsUserSymfonyService $idsUserSymfony )
    {
        $this->passwordHasher=$passwordHasher;
        $this->em=$em;
        $this->parameter=$parameter;
        $this->logger=$logger;
        $this->prefixe=$parameter->get("aldaflux_ids_sante.prefixe");
        $this->idsUserSymfony=$idsUserSymfony;

    }
    
    
    
    public function CheckPassword(CheckPasswordIn $CheckPasswordRequest) 
    {
 
        if (empty($CheckPasswordRequest->Authentifier) || empty($CheckPasswordRequest->OrganizationalUnit) || empty($CheckPasswordRequest->Password)) {
            $clientreponse = "Requete d'identification invalide";
            $this->logger->warning($clientreponse);
            throw new SoapFault("Erreur ", utf8_encode($clientreponse));
            return;
        }

        /*
        $method=$this->parameter->get("aldaflux_ids_sante.user.find_by");
        $username= substr($CheckPasswordRequest->Authentifier, strlen($this->prefixe));
        $user=$this->em->getRepository($this->parameter->get("aldaflux_ids_sante.user.class"))->{$method}($username);
         * 
         */
        
        $user==$this->idsUserSymfony->getUser($CheckPasswordRequest->Authentifier);
        
        
        $CheckPasswordOut = new CheckPasswordOut();
        
        
        if ($user) 
        {

//            $this->logger->info("User {username} trouvé dans la base", ['username'=>$username]);

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
                $this->logger->warning("Mot de passe non concordant", ['username'=>$CheckPasswordRequest->Authentifier]);
            }
        }
        else 
        {
//            $this->logger->warning("User {username} non trouvé dans la base", ['username'=>$username]);
            $CheckPasswordOut->IsValid = false;
            $CheckPasswordOut->CheckPasswordUserInfo = "DenialReason=User unknow;";
        }

        return $CheckPasswordOut;
    }
 
}
 