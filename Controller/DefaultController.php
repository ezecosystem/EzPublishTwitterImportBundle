<?php

namespace Xrow\TwitterImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('XrowTwitterImportBundle:Default:index.html.twig', array('name' => $name));
    }
}
