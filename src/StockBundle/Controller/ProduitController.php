<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Produit;
use StockBundle\Entity\Categories;
use EntrepotBundle\Entity\Entrepot;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Produit controller.
 *
 * @Route("produit")
 */
class ProduitController extends Controller
{
    /**
     * Lists all produit entities.
     *
     * @Route("/", name="produit_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $produits = $em->getRepository('StockBundle:Produit')->findBy(array('idUser'=>$user));
        $categorys = $em->getRepository('StockBundle:Categories')->findBy(array('idUser'=>$user));
        $entrepots = $em->getRepository('GererEntrepotBundle:Entrepot')->findBy(array ('id'=>$user));

        foreach ($produits as $produit)
        {
            if ($produit->isPromotion()==0){
            $repo=$this->getDoctrine()->getManager()->getRepository('StockBundle:Produit');
            $update=$repo->updatePrix($produit);

            }
        }
        $i=-1; $tab= array();

        foreach ($entrepots as $entrepot){
            $i++;
            $tab[$i] = array('entrepot'=>$entrepot,'qtt'=>$em->createQuery('
        SELECT SUM(p.quantite) somme FROM StockBundle:Produit p WHERE p.fkEntrepot =:item ')->setParameter(
                'item',$entrepot
            )->getResult());
        }


            return $this->render('@Stock/produit/index.html.twig', array(
            'produits' => $produits,
                'categorys'=>$categorys,
                'entrepots'=>$entrepots,'tab' => $tab,
          ));
    }

    /**
     * Creates a new produit entity.
     *
     * @Route("/new", name="produit_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $produit = new Produit();
        $form = $this->createForm('StockBundle\Form\ProduitType', $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $id = $this->getUser();
            $produit->setIdUser($id);
            $categories=$produit->getFkCategorie();
            $ent=$categories->getFkEntrepot();
            $produit->setFkEntrepot($ent);
            $produit->setPromotion(0);
           $fk=$produit->getFkEntrepot();
            $qteM=$fk->getQuantiteMax();
            //echo $qteM;
            $qteA=$produit->getQuantite();
            $qteE = $em->createQuery('SELECT SUM(p.quantite) somme FROM StockBundle:Produit p WHERE p.fkEntrepot =:item ')
                ->setParameter('item',$fk)
                ->getResult();


            foreach ($qteE as $qteE){
                $qteEE= $qteE['somme'] ;

            }
         //   echo $qteEE;
            if($qteEE + $qteA <= $qteM){
                $em->persist($produit);
                $em->flush();
       } else {
                return $this->render('@Stock/produit/msg.html.twig');
            }
            return $this->redirectToRoute('produit_show', array('idProduit' => $produit->getIdproduit()));
        }

        return $this->render('@Stock/produit/new.html.twig', array(
            'produit' => $produit,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a produit entity.
     *
     * @Route("/{idProduit}", name="produit_show")
     * @Method("GET")
     */
    public function showAction(Produit $produit)
    {

        return $this->render('@Stock/produit/show.html.twig', array(
            'produit' => $produit,
        ));
    }

    /**
     * Displays a form to edit an existing produit entity.
     *
     * @Route("/{idProduit}/edit", name="produit_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Produit $produit)
    {
        $editForm = $this->createForm('StockBundle\Form\ProduitType', $produit);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid())
        {
            $this->getDoctrine()->getManager()->flush();


            return $this->redirectToRoute('produit_edit', array('idProduit' => $produit->getIdproduit()));
        }

        return $this->render('@Stock/produit/edit.html.twig', array(
            'produit' => $produit,
            'edit_form' => $editForm->createView(),
        ));
    }


    /**
 * Deletes a produit entity.
 *
 * @Route("/{idProduit}/delete", name="produit_delete")
 * @Method("DELETE")
 */
    public function deleteAction( $idProduit)
    {
        $form = $this->getDoctrine()->getRepository(Produit::class)->find($idProduit);
        $em=$this->getDoctrine()->getManager();

        $em->
        remove($form);

        $em->flush();

        return $this->redirectToRoute('produit_index');
    }

    public function qteAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $entrepots = $em->getRepository('GererEntrepotBundle:Entrepot')->findBy(array ('id'=>$user));
        $i=-1; $tab= array();

        foreach ($entrepots as $entrepot){
            $i++;
            $tab[$i] = array('entrepot'=>$entrepot,'qtt'=>$em->createQuery('
        SELECT SUM(p.quantite) somme FROM StockBundle:Produit p WHERE p.fkEntrepot =:item ')->setParameter(
                'item',$entrepot
            )->getResult());
        }
        return $this->render('@Stock/default/index.html.twig',
            array('tab' => $tab,
                'entrepots'=>$entrepots,
                ));

        }
    public function prixAction()
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $produits = $em->getRepository('StockBundle:Produit')->findBy(array('idUser'=>$user));
        $i=0;
        foreach($produits as $produit)
        {

            if ($produits[$i]->getQuantite()==10)
            {
                $produits[$i]->setPrix($produits[$i]->getPrix()*0.9);
                $i++;
            }
            return $this->render('@Stock/default/index.html.twig',
                array('produits'=>$produits
                ));
        }


    }

    public function returnAction()
    {
        return $this->render('@Stock/default/index.html.twig');

    }
    public function affecterNoteAction($id,$note){
        $em = $this->getDoctrine()->getManager();
        $produit = $em->getRepository('StockBundle:Produit')->find($id);
        $produit->setNote($note);
        $em->persist($produit);
        $em->flush();
            return new Response("ok");
    }

public function recherchePAction(){
    $em = $this->getDoctrine()->getManager();

    if (isset($_GET['produit'])){
        $produit=(String) trim($_GET['produit']);
var_dump($produit);
$prod=$em->Query('SELECT (p) FROM StockBundle:Produit p WHERE p.libelle LIKE =:item ')
            ->setParameter('item',"%$produit%")
            ->getResult();
        $prod=$prod->fetchALL();
        foreach($prod as $p){

            ?>
    {{p.libelle}}
<?php
        }
    }

}

}
