<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use App\Document\CsvApi;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(DocumentManager $dm)
    {
        $apis = $dm->getRepository(CsvApi::class)->findAll();
        return $this->render('base.html.twig', [
            'title' => "Mes APIs",
            'apis' => $apis
        ]);
    }

    /**
     * @Route("/new", name="read_csv", methods={"POST","HEAD"})
     */
    public function readCsvAction(Request $request, DocumentManager $dm)
    {
        // Retrieve file path
        $uploads_dir = $this->getParameter('uploads_directory');
        $csv_file = $uploads_dir . "dummy.csv";
        
        // CSV parser
        $reader = new Csv();
        $loader = $reader->load($csv_file);
        $sheet = $loader->getActiveSheet();
        $collection = $sheet->getCellCollection();
        $cell_coordinates = $collection->getCoordinates();

        // Final JSON
        $api_name = (!empty($request->request->get("name"))) ? $request->request->get("name") : "Mon API";
        $json = [
            "name" => $api_name
        ];

        foreach($cell_coordinates as $coordinate) {
            $cell = $collection->get($coordinate);
            $current_column = $cell->getColumn();
            $current_row = $cell->getRow();

            if($current_row > 1) {
                $key_coordinate = $current_column . 1;
                $key_name = $collection->get($key_coordinate)->getValue();
                $json["data"][$current_row -2][$key_name] = $cell->getValue();
            }
        }

        $new_api = new CsvApi();
        $new_api->setName($api_name);
        $new_api->setContent(json_encode($json));
        $dm->persist($new_api);
        $dm->flush();

        return $this->redirectToRoute("index");
    }

    /**
     * @Route("/api/{id}", name="get_api", methods={"GET","HEAD"})
     */
    public function readApiAction($id, DocumentManager $dm)
    {
        $api = $dm->getRepository(CsvApi::class)->findOneBy(["_id" => $id]);
        $api_content = $api->getContent();

        $response = new Response();
        $response->setContent($api_content);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}