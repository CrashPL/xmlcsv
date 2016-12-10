<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Entity\Data;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    ///**
    // * @Route("/", name="homepage")
     //*/
//    public function indexAction(Request $request)
//    {
//        // replace this example code with whatever you need
//        return $this->render('default/index.html.twig', [
//            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
//        ]);
//    }
    
    /**
     * @Route("/", name="convert")
     */
    public function convertAction(Request $request, Data $data = null)
    {
        $data = array();
        $form = $this->createFormBuilder($data)
                ->add('file',FileType::class )
                ->getForm();
        
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        
       
        
        if($form->isSubmitted() && $form->isValid())
        {
            $dataForm = $form->getData();
            $file = $dataForm['file'];
            if($file->getClientOriginalExtension() === 'xml')
            {
                $xml = simplexml_load_file($file);
                $data = new Data();
                $data->setName($xml->name);
                $data->setSurname($xml->surname);
                
                $em->persist($data);
                $em->flush();
                
                $fp = fopen('file.csv','w');
                $objects = array($xml->name,$xml->surname);
                fputcsv($fp, $objects);
                fclose($fp);
                
                return $this->render('default/show-csv.html.twig');
            }
            else if($file->getClientOriginalExtension() === 'csv')
            {
                if (($handle = fopen($file, "r")) !== FALSE) 
                {
                    while (($d = fgetcsv($handle, 1000, ",")) !== FALSE) 
                    {
                        $data = new Data();
                        $data->setName($d[0]);
                        $data->setSurname($d[1]);
                        $em->persist($data);
                        $em->flush();
                        //$rootNode = new \SimpleXMLElement( "<?xml version='1.0' encoding='UTF-8' standalone='yes'? >"<result></result>" );
//                        $xml = new \SimpleXMLElement('<xml/>');
//                        $resultNode = $xml->addChild('result');
//                        $resultNode->addChild('name',$d[0]);
//                        $resultNode->addChild('surname',$d[1]);
                 
                        $resultXml = "<?xml version='1.0' encoding='utf-8'?>";
                        $resultXml .= '<result>';
                        $resultXml .= '<name>';
                        $resultXml .= trim($d[0]);
                        $resultXml .= '</name>';
                        $resultXml .= '<surname>';
                        $resultXml .= trim($d[1]);
                        $resultXml .= '</surname>';
                        $resultXml .= '</result>';
                   
                        file_put_contents('file.xml', $resultXml);
                    }
                }
                fclose($handle);

                //return new Response($xml->asXML());
                return $this->render('default/show-xml.html.twig');
            }        
        }
    
        return $this->render('default/convert-form.html.twig', array('form' => $form->createView()));
    }
    
    /**
     * @Route("/convert/{data}", name="convert_database")
     */
    public function convertXmlCsvAction(Data $data)
    {
        $fp = fopen('file.csv','w');
        $objects = array($data->getName(),$data->getSurname());
        fputcsv($fp, $objects);
        fclose($fp);
        
        $resultXml = "<?xml version='1.0' encoding='utf-8'?>";
        $resultXml .= '<result>';
        $resultXml .= '<name>';
        $resultXml .= trim($data->getName());
        $resultXml .= '</name>';
        $resultXml .= '<surname>';
        $resultXml .= trim($data->getSurname());
        $resultXml .= '</surname>';
        $resultXml .= '</result>';
                   
        file_put_contents('file.xml', $resultXml);
        
        return $this->render('default/show-csv-xml.html.twig');
    }
}
