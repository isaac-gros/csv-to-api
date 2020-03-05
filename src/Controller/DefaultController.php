<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use App\Document\Upload;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        return $this->render('base.html.twig', [
            'title' => "Hello world !"
        ]);
    }

    /**
     * @Route("/new", name="read_csv", methods={"POST","HEAD"})
     */
    public function readCsvAction(DocumentManager $dm)
    {
        $upload = new Upload();
        $upload->setName("Hello world");
        
        $dm->persist($upload);
        $dm->flush();

        return $this->redirectToRoute("index", [
            'title' => "Sent !"
        ]);
    }
}