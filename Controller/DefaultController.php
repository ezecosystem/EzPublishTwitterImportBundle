<?php

namespace xrow\EzPublishTwitterImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('xrowEzPublishTwitterImportBundle:Default:index.html.twig', array('name' => $name));
    }
}
