<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Controller;

use Aldaflux\AldafluxIdsSanteBundle\Service\CheckPasswordService;
use Aldaflux\AldafluxIdsSanteBundle\Service\IDSLog;
use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordIn;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

    
#[Route(path: '/ids/rest', methods: 'GET')]
class IdsRestController extends AbstractController
{
    public function __construct(
            private IDSLog $IDSLog,
            private CheckPasswordService $checkpasswordService,
            )
    {
    }

 
    
    #[Route('/auth/login', methods: ['POST'], name:'ids_checkpassword_validate')]
    public function handle(Request $request): JsonResponse 
    {
        $data = json_decode($request->getContent());
        $inputRequest = new CheckPasswordIn();
        $inputRequest->OrganizationalUnit = $data->OrganizationalUnit;
        $inputRequest->CertificationAuthority = $data->CertificationAuthority;
        $inputRequest->Authentifier = $data->Authentifier;
        $inputRequest->Password = $data->Password;
        
        $result=$this->checkpasswordService->CheckPassword($inputRequest);
        
        $response = new JsonResponse($result);
        return $response;
    }
    

    
    #[Route('/auth/send-otp/default', methods: ['POST'])]
    public function sendOtp(Request $request): JsonResponse 
    {

        $content = json_decode($request->getContent());
        
        
        error_log('send the OTP : ' . $content->otp . ' to ' . $content->authentifier);

        return new JsonResponse(true);
    }
    
     
     
  
}