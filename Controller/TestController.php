<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 11/07/2017
 * Time: 16:31
 */

namespace Troopers\MangopayBundle\Controller;


use MLC\UserBundle\Entity\LegalUser;
use MLC\UserBundle\Form\Type\LegalUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TestController
 * @Route("/test")
 */
class TestController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/new_user", name="troopers_mangopaybundle_test_new_user")
     */
    public function newMangoUserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = new LegalUser();
        $form = $this->createForm(LegalUserType::class, $user);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'New User Added');
            dump($user);
        }

        return $this->render('TroopersMangopayBundle:Test:new_mango_user.html.twig', array(
            'form' => $form->createView()
        ));
    }
}