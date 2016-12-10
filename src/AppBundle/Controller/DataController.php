<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Data;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Datum controller.
 *
 * @Route("data")
 */
class DataController extends Controller
{
    /**
     * Lists all datum entities.
     *
     * @Route("/", name="data_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $datas = $em->getRepository('AppBundle:Data')->findAll();

        return $this->render('data/index.html.twig', array(
            'datas' => $datas,
        ));
    }

    /**
     * Creates a new datum entity.
     *
     * @Route("/new", name="data_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $datum = new Data();
        $form = $this->createForm('AppBundle\Form\DataType', $datum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($datum);
            $em->flush($datum);

            return $this->redirectToRoute('data_show', array('id' => $datum->getId()));
        }

        return $this->render('data/new.html.twig', array(
            'datum' => $datum,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a datum entity.
     *
     * @Route("/{id}", name="data_show")
     * @Method("GET")
     */
    public function showAction(Data $datum)
    {
        $deleteForm = $this->createDeleteForm($datum);

        return $this->render('data/show.html.twig', array(
            'datum' => $datum,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing datum entity.
     *
     * @Route("/{id}/edit", name="data_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Data $datum)
    {
        $deleteForm = $this->createDeleteForm($datum);
        $editForm = $this->createForm('AppBundle\Form\DataType', $datum);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('data_edit', array('id' => $datum->getId()));
        }

        return $this->render('data/edit.html.twig', array(
            'datum' => $datum,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a datum entity.
     *
     * @Route("/{id}", name="data_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Data $datum)
    {
        $form = $this->createDeleteForm($datum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($datum);
            $em->flush($datum);
        }

        return $this->redirectToRoute('data_index');
    }

    /**
     * Creates a form to delete a datum entity.
     *
     * @param Data $datum The datum entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Data $datum)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('data_delete', array('id' => $datum->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
