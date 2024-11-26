<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Controller;

use Aldaflux\AldafluxIdsSanteBundle\Service\IDSLog;
use Aldaflux\AldafluxIdsSanteBundle\Utils\getLogLineRequest;
use Aldaflux\AldafluxIdsSanteBundle\Utils\logLine;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

    
#[Route(path: '/ids/logs', methods: 'GET')]
class IdsLogsController extends AbstractController
{
    
    public function __construct(
            private ParameterBagInterface $parameter,
            private IDSLog $IDSLog,
            )
    {
        $this->prefixe=$parameter->get("aldaflux_ids_sante.prefixe");
    }

  
     
    #[Route(path: '/logs', name: 'ids_show_logs')]
    public function logAction()
    {
        $dateFin=new DateTime();
        $dateDebut =clone $dateFin;
        $dateDebut->modify("-2 days");

        $logs=array();
        

        if (isset($_COOKIE["sessionids"]))
        {
            $cookie = $_COOKIE["sessionids"];


            $classmap = array('logLine' => logLine::class);
            $options = array('compression' => true, 'exceptions' => true, 'trace' => true, 'classmap' => $classmap);

            $request = new getLogLineRequest();
            $request->Application = $this->parameter->get("aldaflux_ids_sante.application_name");
            $request->AuthCookie = $cookie;
            $request->Requester = $_SERVER['HTTP_IDS_USER'];
            $request->OrganizationUnitFilter = "*";
            $request->UnitFilter = "*";
            $request->PatientFilter = "*";
            $request->ReqFilter = "*";
            $request->ExtraFilter = "%%";

            $request->MinTimeFilter =  $dateDebut->format(DATE_ATOM);
            $request->MaxTimeFilter =  $dateFin->format(DATE_ATOM);

 
        }
        
        return $this->render('@AldafluxIdsSante/logs.html.twig' , ['logs'=>$logs]);
    }    
     
     
     
     
}