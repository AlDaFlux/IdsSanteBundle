<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Controller;
 
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordIn;

use Aldaflux\AldafluxIdsSanteBundle\Utils\CheckPasswordOut;

use Aldaflux\AldafluxIdsSanteBundle\Service\CheckPasswordService;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


use Symfony\Component\Form\Extension\Core\Type\PasswordType;


ini_set("soap.wsdl_cache_enabled", "0");

use \SoapServer;

        
    
/**
 * @Route("/ids")
 */
class IdsController extends AbstractController
{
    
    
    private $parameter;
    private $wdsl;
    private $passwordHasher;
    private $router;
    private $logger;

    public function __construct(ParameterBagInterface $parameter,UserPasswordHasherInterface $passwordHasher, UrlGeneratorInterface $router, LoggerInterface $idsLogger)
    {
        $this->router=$router;
        $this->wdsl=$this->router->generate("ids_checkpassword_wsdl",[], urlGeneratorInterface::ABSOLUTE_URL);
        $this->passwordHasher=$passwordHasher;
        $this->parameter=$parameter;
        $this->prefixe=$parameter->get("aldaflux_ids_sante.prefixe");
        
        
        
        $this->logger=$idsLogger;
    }

    
    /**
     * @Route("/checkpassword.wsdl", defaults={"_format"="xml"}, name="ids_checkpassword_wsdl")
     */
     public function checkpasswordWdslAction()
     {
         
        $this->logger->info('route ids_checkpassword_wsdl appelé');
         
        
        
        if ($this->parameter->get("aldaflux_ids_sante.proxy_enabled"))
        {
             $this->logger->info('proxy active', ["aldaflux_ids_sante.proxy_enabled"=>$this->parameter->get("aldaflux_ids_sante.proxy_enabled")]);
             $checkpasswordservice="http://".$this->parameter->get("aldaflux_ids_sante.ip").$this->router->generate("ids_checkpasswordservice");
        }
        else
        {
            $this->logger->info('proxy désactivé active', ["aldaflux_ids_sante.proxy_enabled"=>$this->parameter->get("aldaflux_ids_sante.proxy_enabled")]);
            $checkpasswordservice=$this->router->generate("ids_checkpasswordservice",[], urlGeneratorInterface::ABSOLUTE_URL);
        }
        $this->logger->info('$checkpasswordservice : {checkpasswordservice}', ['checkpasswordservice'=>$checkpasswordservice]);

        $this->logger->info('Check pass');
         
        $response=$this->render('@AldafluxIdsSante/checkpassword.wsdl.twig', ['checkpasswordservice'=>$checkpasswordservice]);
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
     }

 
     

    /**
     * @Route("/checkpasswordservice", name="ids_checkpasswordservice")
     */
     public function CheckpasswordServiceAction()
     {
        $this->logger->info('ids_checkpasswordservice : CheckpasswordServiceAction');
        
        ini_set("soap.wsdl_cache_enabled", "0");
        $classmap = array('CheckPasswordIn' => CheckPasswordIn::class, 'CheckPasswordOut' => CheckPasswordOut::class);
        $server = new SoapServer($this->wdsl, array('classmap' => $classmap));
        $server->setClass(CheckPasswordService::class,  $this->parameter, $this->passwordHasher, $this->getDoctrine()->getManager(), $this->logger);
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $response = new Response();
                $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');
                ob_start();
                $server->handle();
                $response->setContent(ob_get_clean());
                return $response;
        } 
        else 
        { 

            $this->logger->warning('Ce serveur SOAP peut gérer les fonctions suivantes : ');
            //dans le cas contraire, la page affichera les méthodes proposées par le WebService
            $reponse_txt= '<h4>Ce serveur SOAP peut gérer les fonctions suivantes : </h4><ul>';
            $functions = $server->getFunctions();
            foreach ($functions as $func) {
                $reponse_txt.='<li>' . $func . '</li>';
                $this->logger->warning($func);
            }
            $reponse_txt.='</ul>';
            $response = new Response();
            $response->setContent($reponse_txt);
            return $response;
        }
         
     }
     

    /**
     * @Route("/checkpasswordservice/test", name="ids_checkpassword_test")
     * @Method({"GET", "POST"})
     */
     public function testCheckpasswordAction(Request $request)
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

                    
                    /*  'proxy_host' => 'URL PROXY',
                        'proxy_port' => 'PORT PROXY'*/
                    
                    $options = [
                        'cache_wsdl'     => WSDL_CACHE_NONE,
                        'trace'          => 1,
                        'stream_context' => stream_context_create(
                            [
                                'ssl' => [
                                    'verify_peer'       => false,
                                    'verify_peer_name'  => false,
                                    'allow_self_signed' => true
                                ]
                            ]
                        )
                    ];                    
                    
                    $soapClient =new \SoapClient($this->wdsl, $options);
                    $checkPasswordIn = new CheckPasswordIn();
                    $checkPasswordIn->Authentifier=$login;
                    $checkPasswordIn->OrganizationalUnit="test";
                    $checkPasswordIn->Password=$password;




                    $reponse = $soapClient->CheckPassword($checkPasswordIn);

                   
                    if (is_soap_fault($reponse)) 
                    {
                          $this->logger->critical('erreur Soap  : '.$this->wdsl, ['soapreponse'=>$soapClient->__getLastResponse()]);
                          $this->AddFlash("danger","Une erreur s'est produite : "); 
                          $this->AddFlash("danger", utf8_decode($soapClient->__getLastResponse())); 
                    }
                    else
                    {
            //            dd($reponse);

                        if ($reponse->IsValid)
                        {
                            $this->logger->info("Authentification réussie");
                            $this->AddFlash("success","Authentification réussie"); 
                        }
                        else
                        {
                            $this->AddFlash("danger","Authentification echouée : ".$reponse->CheckPasswordUserInfo); 
                            $this->logger->info("Authentification échouée");
                            return $this->redirectToRoute('ids_checkpassword_test');
                        }
                    } 

                    return($this->renderForm('@AldafluxIdsSante/testlogin.html.twig', ['reponse'=>$reponse, 'form'=>$form]));
            }

 
        return($this->renderForm('@AldafluxIdsSante/testlogin.html.twig', ['form'=>$form]));
     }
     
      
          
     
     
     
     
     
}