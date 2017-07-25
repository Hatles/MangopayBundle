<?php

namespace Troopers\MangopayBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Troopers\MangopayBundle\Entity\TransactionInterface;
use Troopers\MangopayBundle\Form\CardType;

/**
 * Manage payment.
 *
 * @Route("/payment_direct")
 */
class PaymentDirectController extends Controller
{
    /**
     * Create a payment.
     *
     * @Route("/new/{transaction}", name="troopers_mangopaybundle_direct_payment_new", defaults={"order" = null, "type" = "card"})
     **/
    public function newAction(Request $request, $transaction)
    {
        $transactionRepository = $this->getDoctrine()->getManager()
            ->getRepository($this->container->getParameter('troopers_mangopay.transaction.class'));
        $transaction = $transactionRepository->findOneById($transaction);
        if (!$transaction instanceof TransactionInterface) {
            throw $this->createNotFoundException('Transaction not found');
        }
        if ($transaction->getStatus() != TransactionInterface::STATUS_CREATED) {
            throw $this->createNotFoundException('Transaction already succeeded or failed');
        }

        //create card form
        $form = $this->createForm(CardType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //find or create a mango user
            $mangoUser = $this->container->get('troopers_mangopay.user_helper')
                ->findOrCreateMangoUser($this->getUser());
            //create a cardRegistration
            $callback = $this->container->get('troopers_mangopay.payment_direct_helper')
                ->prepareCardRegistrationCallback($mangoUser, $transaction);
            //return js callback
            return new JsonResponse($callback);
        }

        return $this->render(
            'TroopersMangopayBundle::cardDirectPayment.html.twig',
            [
                'form' => $form->createView(),
                'transaction' => $transaction,
            ]
        );
    }

    /**
     * @param Request $request The request
     * @param Reservation $reservation The reservation
     * @param int $cardId The cardId
     *
     * This method is called by paymentAction callback, with the authorized cardId as argument.
     * It creates a PreAuthorisation with reservation price, and store its id in the Reservation.
     * When the owner will accept the reservation, we will be able to fetch the PreAuthorisation and create the PayIn
     *
     * @Route("/finalize/{transaction}/{cardId}", name="troopers_mangopaybundle_direct_payment_finalize")
     *
     * @return JsonResponse return json
     */
    public function paymentFinalizeAction(Request $request, $transaction, $cardId)
    {
        $em = $this->getDoctrine()->getManager();
        $transactionRepository = $em->getRepository($this->container->getParameter('troopers_mangopay.transaction.class'));
        /**
         * @var TransactionInterface $transaction
         */
        $transaction = $transactionRepository->findOneById($transaction);
        if (!$transaction instanceof TransactionInterface) {
            throw $this->createNotFoundException('Transaction not found');
        }
        if ($transaction->getStatus() != TransactionInterface::STATUS_CREATED) {
            throw $this->createNotFoundException('Transaction already succeeded or failed');
        }

        $data = $request->get('data');
        $errorCode = $request->get('errorCode');

        $directPaymentHelper = $this->container->get('troopers_mangopay.payment_direct_helper');
        $updatedCardRegister = $directPaymentHelper->updateCardRegistration($cardId, $data, $errorCode);

        // Handle error
        if ((property_exists($updatedCardRegister, 'ResultCode')
                && $updatedCardRegister->ResultCode !== '000000')
            || $updatedCardRegister->Status == 'ERROR'
        ) {
            $errorMessage = $this->get('translator')->trans('mangopay.error.' . $updatedCardRegister->ResultCode);

            return new JsonResponse([
                'success' => false,
                'message' => $errorMessage,
            ]);
        }

        $secureModeReturnURL = $this->generateUrl(
            'troopers_mangopaybundle_direct_payment_finalize_secure',
            [
                'transaction' => $transaction->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Create a PayIn
        $payIn = $directPaymentHelper->executeDirectTransaction($transaction, $updatedCardRegister, $secureModeReturnURL);

        // Handle error
        if ((property_exists($payIn, 'Code') && $payIn->Code !== 200) || $payIn->Status == 'FAILED') {
            $errorMessage = $this->get('translator')->trans('mangopay.error.' . $payIn->ResultCode);

            return new JsonResponse([
                'success' => false,
                'message' => $errorMessage,
            ]);
        }

        // Handle secure mode
        if (property_exists($payIn->ExecutionDetails, 'SecureModeNeeded') && $payIn->ExecutionDetails->SecureModeNeeded == 1) {
            return new JsonResponse([
                'success' => true,
                'redirect' => $payIn->ExecutionDetails->SecureModeRedirectURL,
            ]);
        }

//         store payin transaction
//        $event = new PayInEvent($transaction, $payIn);
//        $this->get('event_dispatcher')->dispatch(TroopersMangopayEvents::NEW_PAY_IN, $event);
//
//        $event = new OrderEvent($transaction);
//        $this->get('event_dispatcher')->dispatch(OrderEvents::ORDER_CREATED, $event);

        $transaction->setMangoTransactionId($payIn->Id);

        //Persist pending order
        $em->persist($transaction);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('troopers_mangopay.alert.transaction.success')
        );

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * @param Request $request The request
     * @param Reservation $reservation The reservation
     *
     * This method is called by paymentFinalizeActionif 3dsecure is required. 3DSecure is needed when 250€ are reached
     *
     * @Route("/finalize-secure/{transaction}", name="troopers_mangopaybundle_direct_payment_finalize_secure")
     *
     * @return RedirectResponse
     */
    public function paymentFinalizeSecureAction(Request $request, $transaction)
    {
        $trId = $transaction;
        $em = $this->getDoctrine()->getManager();
        $transactionRepository = $em->getRepository($this->container->getParameter('troopers_mangopay.transaction.class'));
        $transaction = $transactionRepository->findOneById($transaction);
        if (!$transaction instanceof TransactionInterface) {
            throw $this->createNotFoundException('Transaction not found');
        }
        if ($transaction->getStatus() != TransactionInterface::STATUS_CREATED) {
            throw $this->createNotFoundException('Transaction already succeeded or failed');
        }

        $mangopayApi = $this->container->get('troopers_mangopay.mango_api');

        $payInId = $request->get('transactionId');

        $payIn = $mangopayApi->PayIns->Get($payInId);

        if ((property_exists($payIn, 'Code') && $payIn->Code !== 200) || $payIn->Status != 'SUCCEEDED') {
            if (property_exists($payIn, 'Code')) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    $this->get('translator')->trans('mangopay.error.' . $payIn->Code)
                );
            } else {
                $this->get('session')->getFlashBag()->add('error', $payIn->ResultMessage);
            }

            if (!$request->headers->get('referer')) {
                return $this->redirect('/');
            }

            return $this->redirect($request->headers->get('referer'));
        }

//        $event = new PreAuthorisationEvent($order, $preAuth);
//        $this->get('event_dispatcher')->dispatch(TroopersMangopayEvents::UPDATE_CARD_PREAUTHORISATION, $event);
//
//        $event = new OrderEvent($order);
//        $this->get('event_dispatcher')->dispatch(OrderEvents::ORDER_CREATED, $event);

        $transaction->setStatus(TransactionInterface::STATUS_SUCCEEDED);

        $em->persist($transaction);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('troopers_mangopay.alert.pre_authorisation.success')
        );

        return $this->redirect($this->get('troopers_mangopay.payment_helper')->generateSuccessUrl($trId));
    }

    /**
     * @param Request $request The request
     * @param int $transactionId
     *
     * This method shows the congratulations
     *
     * @Route("/success/{transactionId}", name="troopers_mangopaybundle_direct_payment_success")
     *
     * @return Response
     */
    public function successAction(Request $request, $transactionId)
    {
        return $this->render(
            'TroopersMangopayBundle::success.html.twig',
            ['transactionId' => $transactionId]
        );
    }
}
