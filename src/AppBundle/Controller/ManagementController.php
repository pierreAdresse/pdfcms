<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository\CinescenieRepository;
use AppBundle\Form\UserType;
use AppBundle\Form\UserPasswordType;
use AppBundle\Form\ChoiceSkillType;
use AppBundle\Form\ChoiceCinescenieType;
use AppBundle\Form\ChoiceMultiCinescenieType;
use AppBundle\Form\ChoiceUserForActivityType;
use AppBundle\Entity\UserSkill;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Cinescenie;
use AppBundle\Entity\Skill;
use AppBundle\Entity\GroupActivities;
use AppBundle\Entity\Activity;
use AppBundle\Entity\User;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\Cinescenie as CinescenieService;
use AppBundle\Service\Date;
use AppBundle\Service\User as UserService;

class ManagementController extends Controller
{
    /**
     * @Route("/gestion/membres/tableau-de-bord", name="userDashboard")
     */
    public function dashboardAction(CinescenieService $serviceCinescenie, Request $request)
    {
        return $this->render('management/user/dashboard.html.twig');
    }

    /**
     * @Route("/gestion/membres/planning", name="userSchedule")
     */
    public function scheduleAction(CinescenieService $serviceCinescenie, Request $request)
    {
        $users = $this->getDoctrine()
          ->getRepository('AppBundle:User')
          ->findAll()
        ;

        $cinescenies = $serviceCinescenie->getCurrents();
        $today       = new \Datetime('now');

        $activities = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findBy([
                'allowForDivision' => true,
            ])
        ;

        return $this->render('management/user/schedule.html.twig', [
            'users'            => $users,
            'cinescenies'      => $cinescenies,
            'numberActivities' => count($activities),
        ]);
    }

    /**
     * @Route("/gestion/membres/repartition-roles", name="userActivityDivision")
     */
    public function activityDivisionAction(CinescenieService $serviceCinescenie, Date $serviceDate, UserService $serviceUser, Request $request)
    {
        $cinescenies = $serviceCinescenie->getFutures();

        $form = $this->createForm(ChoiceCinescenieType::class, $cinescenies);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em         = $this->getDoctrine()->getManager();
            $data       = $form->getData();
            $cinescenie = $data['cinescenie'];

            $activities = $this->getDoctrine()
                ->getRepository('AppBundle:Activity')
                ->findBy([
                    'allowForDivision' => true,
                ], ['ranking' => 'ASC'])
            ;

            $date = $serviceDate->getSeasonDate();

            $cinescenies = $this->getDoctrine()
                ->getRepository('AppBundle:Cinescenie')
                ->getByDateGreaterThan($cinescenie->getDate())
            ;

            foreach($cinescenies as $cinescenie) {
                $serviceUser->cleanSchedules($cinescenie);

                // Choix d'un rôle
                    // T1 - Tentative 1
                        /*
                        Utilisateur avec la compétence demandée
                        Utilisateur avec comme compétence principale la compétence demandée et le quota non atteint
                        Utilisateur dont le dernier rôle fait n'est pas celui demandé
                        */
                    // T2 - Tentative 2
                        /*
                        Utilisateur avec la compétence demandée
                        Utilisateur avec comme compétence principale la compétence demandée et le quota non atteint
                        */
                    // T3 - Tentative 3
                        /*
                        Utilisateur avec la compétence demandée
                        Utilisateur dont le dernier rôle fait n'est pas celui demandé
                        */
                    // T4 - Tentative 4
                        /*
                        Utilisateur avec la compétence demandée
                        */
                    // Priotité dans le choix
                        /*
                        Nombre de fois que le rôle à été fait (le plus petit en priorité)
                        /!\ Nombre de présence dans l'année (le plus petit en priorité) /!\ Pas effectué car techniquement difficile, la personne devrait être là plus souvent
                        */
                $usersSelected = [];
                foreach ($activities as $activity) {
                    // Récupération des compétences nécéssaires pour le rôle
                    $skillActivities  = $activity->getSkillActivities();
                    $skills           = [];
                    foreach ($skillActivities as $skillActivity) {
                        $skills[] = $skillActivity->getSkill();
                    }

                    // T1 - Tentative 1
                        /*
                        Utilisateur avec la compétence demandée
                        Utilisateur avec comme compétence principale la compétence demandée et le quota non atteint
                        Utilisateur dont le dernier rôle fait n'est pas celui demandé
                        */
                    $pastCinescenies = $serviceCinescenie->getCinesceniesBetween($date, $cinescenie->getDate());
                    $users           = $serviceUser->getForDivisionT1($pastCinescenies, $skills, $activity, $serviceCinescenie->getQuota());
                    $usersT1         = $serviceUser->filterUserPresent($users, $cinescenie, $usersSelected);
                    $usersT1Sort     = $serviceUser->filterByDifferentLastActivity($usersT1, $cinescenie, $date, $activity);

                    // S'il y a des résultats on sélectionne le premier utilisateur trouvé pour ce rôle
                    if (!empty($usersT1Sort)) {
                        $usersSelected[] = $usersT1Sort[0]->getId();
                        $serviceUser->setActivityForUser($usersT1Sort[0], $activity, $cinescenie);
                    } elseif (empty($usersT1Sort) && !empty($usersT1)) {
                        // T2 - Tentative 2
                            /*
                            Utilisateur avec la compétence demandée
                            Utilisateur avec comme compétence principale la compétence demandée et le quota non atteint
                            */
                        $usersSelected[] = $usersT1[0]->getId();
                        $serviceUser->setActivityForUser($usersT1[0], $activity, $cinescenie);
                    } elseif (empty($usersT1Sort) && empty($usersT1)) {
                        // T3 - Tentative 3
                            /*
                            Utilisateur avec la compétence demandée
                            Utilisateur dont le dernier rôle fait n'est pas celui demandé
                            */
                        $users       = $serviceUser->getForDivisionT3($pastCinescenies, $skills, $activity);
                        $usersT3     = $serviceUser->filterUserPresent($users, $cinescenie, $usersSelected);
                        $usersT3Sort = $serviceUser->filterByDifferentLastActivity($usersT3, $cinescenie, $date, $activity);

                        // S'il y a des résultats on sélectionne le premier utilisateur trouvé pour ce rôle
                        if (!empty($usersT3Sort)) {
                            $usersSelected[] = $usersT3Sort[0]->getId();
                            $serviceUser->setActivityForUser($usersT3Sort[0], $activity, $cinescenie);
                        } elseif (empty($usersT3Sort) && !empty($usersT3)) {
                            // T4 - Tentative 4
                                /*
                                Utilisateur avec la compétence demandée
                                */
                            $usersSelected[] = $usersT3[0]->getId();
                            $serviceUser->setActivityForUser($usersT3[0], $activity, $cinescenie);
                        }
                    }
                }

                $em->flush();
            }

            $this->addFlash(
                'notice',
                'La répartition est terminée !'
            );

            return $this->redirectToRoute('userSchedule');
        }

        return $this->render('management/user/activityDivision.html.twig', [
            'cinescenies' => $cinescenies,
            'form'        => $form->createView(),
        ]);
    }

    /**
     * @Route("/gestion/membres/planning/{cinescenie}/editer-roles", name="userScheduleEditActivities")
     */
    public function scheduleEditActivitesAction(Request $request, Cinescenie $cinescenie)
    {
        $activities = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findBy([
                'allowForDivision' => true,
            ], ['ranking' => 'ASC'])
        ;

        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'cinescenie' => $cinescenie,
            ])
        ;

        return $this->render('management/user/scheduleEditActivities.html.twig', [
            'schedules'  => $schedules,
            'activities' => $activities,
            'cinescenie' => $cinescenie,
        ]);
    }

    /**
     * @Route("/gestion/membres/planning/{cinescenie}/editer-roles/{activity}", name="userScheduleEditActivity")
     */
    public function scheduleEditActivityRole(Request $request, Cinescenie $cinescenie, Activity $activity)
    {
        $skillActivity = $this->getDoctrine()
            ->getRepository('AppBundle:SkillActivity')
            ->findOneBy([
                'activity' => $activity,
            ])
        ;
        $skill = $skillActivity->getSkill();

        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->getForActivityWithSkill($cinescenie, $skill)
        ;

        $secondaryUsers = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->getForActivityWithoutSkill($cinescenie, $users)
        ;

        $userLaissezPasser = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find(User::LAISSEZ_PASSER)
        ;
        $secondaryUsers[] = $userLaissezPasser;

        $schedule = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findOneBy([
                'cinescenie' => $cinescenie,
                'activity'   => $activity,
            ])
        ;

        $userSelected = null;
        if (!is_null($schedule)) {
            $userSelected = $schedule->getUser();
            $users[] = $userSelected;
        } else {
            $schedule = new Schedule();
            $schedule->setCinescenie($cinescenie);
            $schedule->setActivity($activity);
        }

        $form = $this->createForm(ChoiceUserForActivityType::class, $users, [
            'userSelected'   => $userSelected,
            'secondaryUsers' => $secondaryUsers,
            'activityName'   => $activity->getName(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $data['users'];
            $em   = $this->getDoctrine()->getManager();

            if (is_null($user)) {
                $schedule->setActivity(null);
            } else {
                $schedule->setUser($user);
            }

            $em->persist($schedule);
            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('userScheduleEditActivities', ['cinescenie' => $cinescenie->getId()]);
        }

        return $this->render('management/user/scheduleEditActivity.html.twig', [
            'activity'   => $activity,
            'cinescenie' => $cinescenie,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @Route("/gestion/membres/{userSlug}/editer-competences", name="userEditSkills")
     */
    public function editSkillsAction(Request $request, $userSlug)
    {
        $user = $this->getDoctrine()
          ->getRepository('AppBundle:User')
          ->findOneByUsername($userSlug)
        ;

        $skills = $this->getDoctrine()
          ->getRepository('AppBundle:Skill')
          ->findAll()
        ;

        $defaultSkills = $this->getDoctrine()
          ->getRepository('AppBundle:Skill')
          ->getByUser($user)
        ;

        $form = $this->createForm(ChoiceSkillType::class, $skills, ['defaultSkills' => $defaultSkills, 'mainSkill' => $user->getMainSkill()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Supprimer les compétences
            $userSkills = $user->getUserSkills();
            foreach ($userSkills as $userSkill) {
                $em->remove($userSkill);
            }

            // Ajouter les compétences
            $data = $form->getData();
            $skills = $data['skills'];
            foreach ($skills as $skill) {
                $userSkill = new UserSkill();
                $userSkill->setUser($user);
                $userSkill->setSkill($skill);
                $em->persist($userSkill);
            }

            // Ajouter la compétence principale
            $mainSkill = $data['mainSkill'];
            if (!is_null($mainSkill)) {
                $user->setMainSkill($mainSkill);
                $em->persist($user);
            }

            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('userGeneral', ['userSlug' => $user->getUsername()]);
        }

        return $this->render('management/user/editSkills.html.twig', [
            'skills' => $skills,
            'user'   => $user,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/gestion/membres/{userSlug}/editer-planning", name="userEditSchedule")
     */
    public function editScheduleAction(CinescenieService $serviceCinescenie, Date $serviceDate, Request $request, $userSlug)
    {
        $user = $this->getDoctrine()
          ->getRepository('AppBundle:User')
          ->findOneByUsername($userSlug)
        ;

        $cinescenies        = $serviceCinescenie->getCurrents();
        $defaultCinescenies = $serviceCinescenie->getCurrentsByUser($user);
        $year               = $serviceDate->getSeasonYear();

        $form = $this->createForm(ChoiceMultiCinescenieType::class, $cinescenies, ['defaultCinescenies' => $defaultCinescenies]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Comparer le planning avant et après
            $schedules = $this->getDoctrine()
              ->getRepository('AppBundle:Schedule')
              ->findByUser($user)
            ;

            $cinesceniesFromSchedules = [];
            foreach ($schedules as $schedule) {
                $cinesceniesFromSchedules[$schedule->getCinescenie()->getId()] = $schedule->getCinescenie();
            }

            $keys = $request->request->get('choice_multi_cinescenie')['cinescenies'];
            $cinescenies = $form->getData();
            $cinesceniesFromForm = [];
            foreach ($keys as $key) {
                $cinescenie = $cinescenies[$key];
                $cinesceniesFromForm[$cinescenie->getId()] = $cinescenie;
            }

            // Planning à supprimer
            $cinesceniesToDelete = array_diff_key($cinesceniesFromSchedules, $cinesceniesFromForm);
            foreach ($cinesceniesToDelete as $cineToDelete) {
                $schedule = $this->getDoctrine()
                  ->getRepository('AppBundle:Schedule')
                  ->findOneBy([
                        'cinescenie' => $cineToDelete,
                        'user'       => $user,
                    ])
                ;

                // TODO: protection pour empêcher de supprimer un planning passé ?
                $em->remove($schedule);
            }

            // Planning à ajouter
            $cinesceniesToAdd = array_diff_key($cinesceniesFromForm, $cinesceniesFromSchedules);
            foreach ($cinesceniesToAdd as $cineToAdd) {
                // TODO: protection pour empêcher d'ajouter un planning quand la cinéscénie est validée ?

                $schedule = new Schedule();
                $schedule->setUser($user);
                $schedule->setCinescenie($cineToAdd);
                $em->persist($schedule);
            }

            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('userGeneral', ['userSlug' => $user->getUsername()]);
        }

        return $this->render('management/user/editSchedule.html.twig', [
            'user'        => $user,
            'cinescenies' => $cinescenies,
            'form'        => $form->createView(),
            'year'        => $year,
        ]);
    }

    /**
     * @Route("/gestion/membres/{userSlug}/editer-mot-de-passe", name="userEditPassword")
     */
    /*public function editPasswordAction(Request $request, $userSlug)
    {
        $user = $this->getDoctrine()
          ->getRepository('AppBundle:User')
          ->findOneByUsername($userSlug)
        ;

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
                $url = $this->generateUrl('userGeneral', ['userSlug' => $user->getUsername()]);
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $response;
        }

        return $this->render('management/user/editPassword.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }*/

    /**
     * @Route("/gestion/membres/{userSlug}", name="userGeneral")
     */
    public function generalAction(CinescenieService $serviceCinescenie, Date $serviceDate, Request $request, $userSlug)
    {
        $cinescenies = $serviceCinescenie->getCurrents();
        $year        = $serviceDate->getSeasonYear();

        $skills = $this->getDoctrine()
            ->getRepository('AppBundle:Skill')
            ->findAll()
        ;

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneByUsername($userSlug)
        ;

        $stats = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->getSchedulesForUser($user)
        ;

        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'user'     => $user,
                'activity' => null,
            ])
        ;
        $numberPresenceWithoutActivity = count($schedules);

        $stats[] = [
            'name'          => 'Suppléant',
            'numberOfTimes' => $numberPresenceWithoutActivity,
        ];

        if ($user->getId() == User::LAISSEZ_PASSER) {
            throw $this->createAccessDeniedException('You cannot access this page!');
        }

        return $this->render('management/user/general.html.twig', [
            'skills'      => $skills,
            'user'        => $user,
            'cinescenies' => $cinescenies,
            'year'        => $year,
            'stats'       => $stats,
        ]);
    }

    /**
     * @Route("/gestion/membres", name="userList")
     */
    public function listAction(UserService $serviceUser, Request $request)
    {
        $users = $serviceUser->getAndCountSchedules();

        return $this->render('management/user/list.html.twig', [
            'users' => $users,
        ]);
    }
}
