<?php

namespace TransporteurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TransporteurBundle\Service\UtilsService;

class DefaultController extends Controller
{
    public function indexAction()
    {

        return $this->render('@Transporteur/Email/email_template.html.twig');
    }








}
