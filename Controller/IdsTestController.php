<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Controller;

use Aldaflux\AldafluxIdsSanteBundle\Service\CheckPasswordService;
use Aldaflux\AldafluxIdsSanteBundle\Service\IDSLog;
use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordIn;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

    
#[Route(path: '/ids/test', methods: 'GET')]
class IdsTestController extends AbstractController
{
    
    private $logger;

    public function __construct(
            private ParameterBagInterface $parameter,
            private EntityManagerInterface $em,
            private UrlGeneratorInterface $router, 
            LoggerInterface $idsLogger, 
            private IDSLog $IDSLog,
            FormFactoryInterface $formBuilder, 
            private HttpClientInterface $client,
            private CheckPasswordService $checkpasswordService,
            )
    {
        $this->prefixe=$parameter->get("aldaflux_ids_sante.prefixe");
        $this->logger=$idsLogger;
    }
 

     #[Route(path: '/checkpassword', name: 'ids_checkpassword_test', methods: ['GET','POST'],  condition: "env('APP_ENV')=='dev'")]
     public function testCheckpasswordAction(Request $request) : Response
     {
         $this->logger->info('ids_checkpassword_test : testCheckpasswordAction');
         
          $form = $this->createFormBuilder()
                 ->add('login', TextType::class, array('required' => false))
                 ->add('password', PasswordType::class)
                 ->add('Test', SubmitType::class)
                 ->getForm();
          
           $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) 
            {

                    $login=$this->prefixe.$form->get('login')->getData();
                    $password=$form->get('password')->getData();

                    $checkPasswordIn = new CheckPasswordIn();
                    
                    $checkPasswordIn->OrganizationalUnit = "test";
                    $checkPasswordIn->Authentifier=$login;
                    $checkPasswordIn->Password=$password;
                    
                    
                    $route=$this->router->generate( "ids_checkpassword_validate", [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $rawResponse = $this->client->request('POST',$route, ['json'=>$checkPasswordIn]);
                    
                    $response=json_decode($rawResponse->getContent());
                    
                    $statusCode = $rawResponse->getStatusCode();
                
                    if ($statusCode<>200) 
                    {
                          $this->logger->critical('erreur REST : ', ['route'=>$route]);
                          $this->AddFlash("danger","Une erreur s'est produite : "); 
                    }
                    else
                    {
                        if ($response->IsValid)
                        {
                            $this->logger->info("Authentification réussie");
                            $this->AddFlash("success","Authentification réussie"); 
                        }
                        else
                        {
                            $this->AddFlash("danger","Authentification echouée : ".$response->CheckPasswordUserInfo); 
                            $this->logger->info("Authentification échouée");
                            return $this->redirectToRoute('ids_checkpassword_test');
                        }
                    }   

                 
                    return($this->renderForm('@AldafluxIdsSante/testlogin.html.twig', ['reponse'=>$response, 'form'=>$form]));
            }

 
        return($this->renderForm('@AldafluxIdsSante/testlogin.html.twig', ['form'=>$form]));
     }
     
       
}