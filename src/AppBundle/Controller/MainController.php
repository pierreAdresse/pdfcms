<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\UserPasswordType;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\Cinescenie;
use AppBundle\Service\Date;

class MainController extends Controller
{
    /**
     * @Route("/", name="topicalities")
     */
    public function topicalitiesAction(Request $request)
    {
        return $this->redirectToRoute('userList');
        //return $this->render('main/topicalities.html.twig');
    }

    /**
     * @Route("/informations", name="informations")
     */
    /*public function informationsAction(Request $request)
    {
        return $this->render('main/informations.html.twig');
    }*/

    /**
     * @Route("/demande-de-visite", name="requestForVisit")
     */
    /*public function requestForVisitAction(Request $request)
    {
        return $this->render('main/requestForVisit.html.twig');
    }*/

    /**
     * @Route("/mon-compte", name="myAccount")
     */
    /*public function accountAction(Request $request)
    {
        return $this->render('main/myAccount.html.twig');
    }*/

    /**
     * @Route("/editer-mon-mot-de-passe", name="editMyPassword")
     */
    /*public function editMyPasswordAction(Request $request)
    {
        $user = $this->getUser();

        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(UserPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('editMyPassword');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            $this->addFlash(
                'notice',
                'Votre mot de passe vient d\'être modifié !'
            );

            return $response;
        }

        return $this->render('main/editMyPassword.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }*/

    /**
     * @Route("/mon-planning", name="mySchedule")
     */
    /*public function myScheduleAction(Cinescenie $serviceCinescenie, Date $serviceDate, Request $request)
    {
        $cinescenies = $serviceCinescenie->getCurrents();
        $year        = $serviceDate->getCurrentYear();
        $user        = $this->getUser();

        return $this->render('main/mySchedule.html.twig', [
            'user'        => $user,
            'cinescenies' => $cinescenies,
            'year'        => $year,
        ]);
    }*/
}
