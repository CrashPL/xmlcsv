<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Files;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Data;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as File2;
/**
 * File controller.
 *
 * @Route("files")
 */
class FilesController extends Controller
{
    /**
     * Lists all file entities.
     *
     * @Route("/", name="files_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $files = $em->getRepository('AppBundle:Files')->findAll();

        return $this->render('files/index.html.twig', array(
            'files' => $files,
        ));
    }

    /**
     * Creates a new file entity.
     *
     * @Route("/new", name="files_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $files = new Files();
        $form = $this->createForm('AppBundle\Form\FilesType', $files);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $ext = '';
        $extAccomp = '';
        
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->getData()->getFile();
            
            if($file->getClientOriginalExtension() === 'xml'){

                $xml = simplexml_load_file($file);
                $data = new Data();
                $data->setName($xml->name);
                $data->setSurname($xml->surname);
                
                $em->persist($data);
                $em->flush();
                
                $nameCsv = sha1(uniqid(mt_rand(), true));
                $fp = fopen( 'temp/'. $nameCsv . '.csv','w');
                $objects = array($xml->name,$xml->surname);
                fputcsv($fp, $objects);
                fclose($fp);
                $csvPath = realpath($this->get('kernel')->getRootDir(). '/../web/temp/'. $nameCsv . '.csv');
                
                $file2 = new File2($csvPath);
                
                $fileCsv = new Files();
                $fileCsv->setFile($file2);
                
                $fileCsv->preUpload('csv');
                $fileCsv->upload('csv');
                $em->persist($fileCsv);
                $em->flush();
                $ext = 'xml';
                $extAccomp = 'csv';
                //unlink('uploads/'. $nameCsv . '.csv');
                
            }else if($file->getClientOriginalExtension() === 'csv')
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

                 
                        $resultXml = "<?xml version='1.0' encoding='utf-8'?>";
                        $resultXml .= '<result>';
                        $resultXml .= '<name>';
                        $resultXml .= trim($d[0]);
                        $resultXml .= '</name>';
                        $resultXml .= '<surname>';
                        $resultXml .= trim($d[1]);
                        $resultXml .= '</surname>';
                        $resultXml .= '</result>';
                        
                        $nameXml = sha1(uniqid(mt_rand(), true));
                        file_put_contents('temp/'. $nameXml . '.xml', $resultXml);
                        $xmlPath = realpath($this->get('kernel')->getRootDir(). '/../web/temp/'. $nameXml . '.xml');
                        
                        $file2 = new File2($xmlPath);
                
                        $fileXml = new Files();
                        $fileXml->setFile($file2);
                
                        $fileXml->preUpload('xml');
                        $fileXml->upload('xml');
                        $em->persist($fileXml);
                        $em->flush();
                        $ext = 'csv';
                        $extAccomp = 'xml';
                    }
                }
            }
            $files->preUpload($ext);
            $files->upload($ext);
            $files->setExtAccomp($extAccomp);
            $files->setExt($ext);
            
            $em->persist($files);
            $em->flush($files);

            return $this->redirectToRoute('files_show', array('id' => $files->getId()));
        }

        return $this->render('files/new.html.twig', array(
            'file' => $files,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a file entity.
     *
     * @Route("/{id}", name="files_show")
     * @Method("GET")
     */
    public function showAction(Files $file)
    {
        $deleteForm = $this->createDeleteForm($file);
        $file2 = $this->getDoctrine()->getManager()->getRepository('AppBundle:Files')->findOneById($file->getId() - 1);
        return $this->render('files/show.html.twig', array(
            'file' => $file,
            'file2' => $file2,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing file entity.
     *
     * @Route("/{id}/edit", name="files_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Files $file)
    {
        $deleteForm = $this->createDeleteForm($file);
        $editForm = $this->createForm('AppBundle\Form\FilesType', $file);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('files_edit', array('id' => $file->getId()));
        }

        return $this->render('files/edit.html.twig', array(
            'file' => $file,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a file entity.
     *
     * @Route("/{id}", name="files_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Files $file)
    {
        $form = $this->createDeleteForm($file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($file);
            $em->flush($file);
        }

        return $this->redirectToRoute('files_index');
    }

    /**
     * Creates a form to delete a file entity.
     *
     * @param Files $file The file entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Files $file)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('files_delete', array('id' => $file->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    public function downloadAction($filename)
{
    $request = $this->get('request');
    $path = $this->get('kernel')->getRootDir(). "/../web/uploads/";
    $content = file_get_contents($path.$filename);

    $response = new Response();

    //set headers
    $response->headers->set('Content-Type', 'mime/type');
    $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename);

    $response->setContent($content);
    return $response;
}
}
