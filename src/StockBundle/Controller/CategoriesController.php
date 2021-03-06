<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Category controller.
 *
 * @Route("categories")
 */
class CategoriesController extends Controller
{
    /**
     * Lists all category entities.
     *
     * @Route("/", name="categories_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $categories = $em->getRepository('StockBundle:Categories')->findBy(array('idUser'=>$user));
        return $this->render('@Stock/categories/index.html.twig', array(
            'categories' => $categories
        ));


    }


    /**
     * Creates a new category entity.
     *
     * @Route("/new", name="categories_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $categories = new Categories();
        $form = $this->createForm('StockBundle\Form\CategoriesType', $categories);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $id = $this->getUser();
            $categories->setIdUser($id);
            $em->persist($categories);
            $em->flush();
            return $this->redirectToRoute('categories_show', array('idCategorie' => $categories->getIdcategorie()));
        }

        return $this->render('@Stock/categories/new.html.twig', array(
            'categories' => $categories,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a category entity.
     *
     * @Route("/{idCategorie}", name="categories_show")
     * @Method("GET")
     */
    public function showAction(Categories $categories)
    {

        return $this->render('@Stock/categories/show.html.twig', array(
            'category' => $categories,
        ));
    }

    /**
     * Displays a form to edit an existing category entity.
     *
     * @Route("/{idCategorie}/edit", name="categories_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Categories $categories)
    {
        $editForm = $this->createForm('StockBundle\Form\CategoriesType', $categories);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('categories_edit', array('idCategorie' => $categories->getIdcategorie()));
        }

        return $this->render('@Stock/categories/edit.html.twig', array(
            'category' => $categories,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a category entity.
     *
     * @Route("/{idCategorie}/delete", name="categories_delete")
     * @Method("DELETE")
     */
    public function deleteAction($idCategorie)
    {
        $form = $this->getDoctrine()->getRepository(Categories::class)->find($idCategorie);
        $em=$this->getDoctrine()->getManager();

        $em->remove($form);
        $em->flush();


        return $this->redirectToRoute('categories_index');
    }


}
