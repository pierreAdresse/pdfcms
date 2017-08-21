<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository\CinescenieRepository;
use AppBundle\Form\UserType;
use AppBundle\Form\ChoiceSkillType;
use AppBundle\Form\ChoiceCinescenieType;
use AppBundle\Entity\UserSkill;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Cinescenie;
use AppBundle\Entity\Skill;
use AppBundle\Entity\GroupActivities;
use AppBundle\Entity\Activity;

class MainController extends Controller
{
    /**
     * @Route("/", name="topicalities")
     */
    public function topicalitiesAction(Request $request)
    {
        return $this->render('main/topicalities.html.twig');
    }

    /**
     * @Route("/informations", name="informations")
     */
    public function informationsAction(Request $request)
    {
        return $this->render('main/informations.html.twig');
    }

    /**
     * @Route("/demande-de-visite", name="requestForVisit")
     */
    public function requestForVisitAction(Request $request)
    {
        return $this->render('main/requestForVisit.html.twig');
    }

    /**
     * @Route("/mon-compte", name="account")
     */
    public function accountAction(Request $request)
    {
        return $this->render('main/account.html.twig');
    }

    /**
     * @Route("/admin/utilisateurs/planning", name="usersSchedule")
     */
    public function usersScheduleAction(Request $request)
    {
        $users = $this->getDoctrine()
          ->getRepository('AppBundle:User')
          ->findAll()
        ;

        $cinescenies = $this->getDoctrine()
          ->getRepository('AppBundle:Cinescenie')
          ->findBy([], ['date' => 'ASC'])
        ;

        $cineToValidate = 0;
        $today = new \Datetime('now');
        foreach ($cinescenies as $cinescenie) {
            if ($cinescenie->getState() != Cinescenie::STATE_VALIDATE and $cinescenie->getDate() < $today) {
                $cineToValidate++;
            }
        }

        return $this->render('admin/user/usersSchedule.html.twig', [
            'users'           => $users,
            'cinescenies'     => $cinescenies,
            'cineToValidate'  => $cineToValidate,
        ]);
    }

    /**
     * @Route("/admin/utilisateurs/repartition-roles", name="usersActivityDivision")
     */
    public function usersActivityDivisionAction(Request $request)
    {
        $cinescenieRepository = $this->getDoctrine()
            ->getRepository('AppBundle:Cinescenie')
        ;

        $query = $cinescenieRepository->createQueryBuilder('cine')
            ->where('cine.date > :today')
            ->setParameter('today', new \Datetime('now'))
            ->orderBy('cine.date', 'ASC')
            ->getQuery()
        ;

        $cinescenies = $query->getResult();

        $cinescenieKey = $request->request->get('select-cinescenie');
        if (!empty($cinescenieKey)) {
            $em = $this->getDoctrine()->getManager();

            $cinescenieElements = explode('-', $cinescenieKey);
            $cinescenieId = $cinescenieElements[1];

            $cinescenie = $this->getDoctrine()
              ->getRepository('AppBundle:Cinescenie')
              ->find($cinescenieId)
            ;

            $activities = [
                'TELEPILOTE' => ['SKILL' => Skill::TELEPILOTE, 'GROUP_ACTIVITIES' => GroupActivities::TELEPILOTE, 'ACTIVITY' => Activity::TELEPILOTE],
                'ECLAIRAGISTE' => ['SKILL' => Skill::ECLAIRAGISTE, 'GROUP_ACTIVITIES' => GroupActivities::ECLAIRAGISTE, 'ACTIVITY' => Activity::ECLAIRAGISTE],
                'REGISSEUR' => ['SKILL' => Skill::REGISSEUR, 'GROUP_ACTIVITIES' => GroupActivities::REGISSEUR, 'ACTIVITY' => Activity::REGISSEUR],
                'GCS_1' => ['SKILL' => Skill::GCS, 'GROUP_ACTIVITIES' => GroupActivities::GCS, 'ACTIVITY' => Activity::GCS_1],
                'GCS_2' => ['SKILL' => Skill::GCS, 'GROUP_ACTIVITIES' => GroupActivities::GCS, 'ACTIVITY' => Activity::GCS_2],
                'NEOPTER_1' => ['SKILL' => Skill::RESPONSABLE_NEOPTER, 'GROUP_ACTIVITIES' => GroupActivities::NEOPTER, 'ACTIVITY' => Activity::NEOPTER_1],
                'NEOPTER_2' => ['SKILL' => Skill::OPERATEUR_NEOPTER, 'GROUP_ACTIVITIES' => GroupActivities::NEOPTER, 'ACTIVITY' => Activity::NEOPTER_2],
                'NEOPTER_3' => ['SKILL' => Skill::OPERATEUR_NEOPTER, 'GROUP_ACTIVITIES' => GroupActivities::NEOPTER, 'ACTIVITY' => Activity::NEOPTER_3],
                'NEOPTER_4' => ['SKILL' => Skill::OPERATEUR_NEOPTER, 'GROUP_ACTIVITIES' => GroupActivities::NEOPTER, 'ACTIVITY' => Activity::NEOPTER_4],
                'NEOPTER_5' => ['SKILL' => Skill::OPERATEUR_NEOPTER, 'GROUP_ACTIVITIES' => GroupActivities::NEOPTER, 'ACTIVITY' => Activity::NEOPTER_5],
                'SECURITE_1' => ['SKILL' => Skill::RESPONSABLE_SECURITE, 'GROUP_ACTIVITIES' => GroupActivities::SECURITE, 'ACTIVITY' => Activity::SECURITE_1],
                'SECURITE_2' => ['SKILL' => Skill::OPERATEUR_SECURITE, 'GROUP_ACTIVITIES' => GroupActivities::SECURITE, 'ACTIVITY' => Activity::SECURITE_2],
                'SECURITE_3' => ['SKILL' => Skill::OPERATEUR_SECURITE, 'GROUP_ACTIVITIES' => GroupActivities::SECURITE, 'ACTIVITY' => Activity::SECURITE_3],
                'SECURITE_4' => ['SKILL' => Skill::OPERATEUR_SECURITE, 'GROUP_ACTIVITIES' => GroupActivities::SECURITE, 'ACTIVITY' => Activity::SECURITE_4],
                'SECURITE_5' => ['SKILL' => Skill::VISUEL_REGIE, 'GROUP_ACTIVITIES' => GroupActivities::ECLAIRAGISTE, 'ACTIVITY' => Activity::SECURITE_5],
                'VISUEL_1' => ['SKILL' => Skill::VISUEL, 'GROUP_ACTIVITIES' => GroupActivities::VISUEL, 'ACTIVITY' => Activity::VISUEL_1],
                'VISUEL_2' => ['SKILL' => Skill::VISUEL, 'GROUP_ACTIVITIES' => GroupActivities::VISUEL, 'ACTIVITY' => Activity::VISUEL_2],
                'VISUEL_3' => ['SKILL' => Skill::VISUEL, 'GROUP_ACTIVITIES' => GroupActivities::VISUEL, 'ACTIVITY' => Activity::VISUEL_3],
                'VISUEL_4' => ['SKILL' => Skill::VISUEL, 'GROUP_ACTIVITIES' => GroupActivities::VISUEL, 'ACTIVITY' => Activity::VISUEL_4],
                'VISUEL_5' => ['SKILL' => Skill::VISUEL, 'GROUP_ACTIVITIES' => GroupActivities::VISUEL, 'ACTIVITY' => Activity::VISUEL_5],
            ];


            // On commence par effacer tous les rôles
            $schedules = $this->getDoctrine()
              ->getRepository('AppBundle:Schedule')
              ->findBy([
                  'cinescenie' => $cinescenie,
              ])
            ;

            foreach ($schedules as $schedule) {
                $schedule->setActivity(null);
                $em->persist($schedule);
            }

            $em->flush();

            // Choix d'un rôle
                // Première tentative
                    /*
                    Utilisateurs présent le jour demandé avec une compétence du rôle recherché.
                    Trier par nombre de fois fait ce rôle dans la saison du plus petit au plus grand.
                    Puis trier par le nombre de séance dans la saison
                    Qui n'a pas fait la cétégorie télépilote la dernière fois
                    */
                // Deuxième tentative
                    /*
                    Utilisateurs présent le jour demandé avec une compétence du rôle recherché.
                    Trier par nombre de fois fait ce rôle dans la saison du plus petit au plus grand.
                    Puis trier par le nombre de séance dans la saison
                    */
                // TODO: Cas d'un rôle qui n'est pas trouvé
                    /*
                    Essayer d'en trouver un dans les utilisateurs déjà positionné en faisant un échange
                    Sinon ne rien mettre et le signaler
                    */
            $usersSelected = [];
            foreach ($activities as $act) {
                $usersForActivity = [];

                $users = $this->getDoctrine()
                  ->getRepository('AppBundle:User')
                  ->getForDivision($cinescenie, $act['SKILL'])
                ;

                foreach ($users as $key => $user) {
                    if (!in_array($user[0]->getId(), $usersSelected)) {
                        $usersForActivity[] = $user[0];
                    }
                }

                $usersForActivitySort = [];
                foreach ($usersForActivity as $user) {
                    $schedules = $this->getDoctrine()
                      ->getRepository('AppBundle:Schedule')
                      ->getLastActivity($user)
                    ;

                    $lastActivity = null;
                    if (!empty($schedules)) {
                        $lastActivity = $schedules[0]->getActivity();
                        $lastGroup = $lastActivity->getGroupActivities();

                        if ($lastGroup->getId() != $act['GROUP_ACTIVITIES']) {
                            $usersForActivitySort[] = $user;
                        }
                    }
                }

                if (!empty($usersForActivitySort)) {
                    $schedule = $this->getDoctrine()
                      ->getRepository('AppBundle:Schedule')
                      ->findOneBy([
                          'user'       => $usersForActivitySort[0],
                          'cinescenie' => $cinescenie,
                      ])
                    ;

                    $usersSelected[] = $usersForActivitySort[0]->getId();

                    $activity = $this->getDoctrine()
                        ->getRepository('AppBundle:Activity')
                        ->find($act['ACTIVITY'])
                    ;

                    $schedule->setActivity($activity);
                    $em->persist($schedule);
                    $em->flush();
                } elseif (empty($usersForActivitySort) && !empty($usersForActivity)) {
                    $schedule = $this->getDoctrine()
                      ->getRepository('AppBundle:Schedule')
                      ->findOneBy([
                          'user'       => $usersForActivity[0],
                          'cinescenie' => $cinescenie,
                      ])
                    ;

                    $usersSelected[] = $usersForActivity[0]->getId();

                    $activity = $this->getDoctrine()
                        ->getRepository('AppBundle:Activity')
                        ->find($act['ACTIVITY'])
                    ;

                    $schedule->setActivity($activity);
                    $em->persist($schedule);
                }
            }

            $em->flush();

            // Reprendre les plannings de la Cinéscénie et compléter les utilisateurs sans rôle par suppléant
            $schedules = $this->getDoctrine()
              ->getRepository('AppBundle:Schedule')
              ->getWithoutActivity($cinescenie)
            ;

            foreach ($schedules as $schedule) {
                $activity = $this->getDoctrine()
                    ->getRepository('AppBundle:Activity')
                    ->find(Activity::SUPPLEANT)
                ;
                $schedule->setActivity($activity);
                $em->persist($schedule);
            }

            $em->flush();

            $this->addFlash(
                'notice',
                'La répartition est terminée !'
            );

            return $this->redirectToRoute('usersScheduleEditActivities', ['cinescenie' => $cinescenie->getId()]);
        }

        return $this->render('admin/user/usersActivityDivision.html.twig', [
            'cinescenies' => $cinescenies,
        ]);
    }

    /**
     * @Route("/admin/utilisateurs/planning/{cinescenie}/editer-roles", name="usersScheduleEditActivities")
     */
    public function usersScheduleEditActivitesAction(Request $request, Cinescenie $cinescenie)
    {
        $schedules = $cinescenie->getSchedules();

        $activities = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findAll()
        ;

        $schedulesKeys = $request->request->keys();
        if (!empty($schedulesKeys)) {
            $em = $this->getDoctrine()->getManager();

            // Supprimer les rôles des plannings
            foreach ($schedules as $schedule) {
                $schedule->setActivity(null);
                $em->persist($schedule);
            }

            $em->flush();

            // Ajouter les rôles sur les plannings
            foreach ($schedulesKeys as $schedulesKey) {
                $scheduleElements = explode('-', $schedulesKey);
                $scheduleId = $scheduleElements[1];

                $schedule = $this->getDoctrine()
                  ->getRepository('AppBundle:Schedule')
                  ->find($scheduleId)
                ;

                $activityKey = $request->request->get($schedulesKey);

                if (!empty($activityKey)) {
                    $activityElements = explode('-', $activityKey);
                    $activityId = $activityElements[1];

                    $activity = $this->getDoctrine()
                      ->getRepository('AppBundle:Activity')
                      ->find($activityId)
                    ;

                    $schedule->setActivity($activity);
                } else {
                    $schedule->setActivity(null);
                }

                $em->persist($schedule);
            }

            // Valider la Cinéscénie si elle est passée
            $today = new \Datetime('now');
            if ($cinescenie->getDate() < $today) {
                $cinescenie->setState(Cinescenie::STATE_VALIDATE);
                $em->persist($cinescenie);
            }

            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('usersSchedule');
        }

        return $this->render('admin/user/usersScheduleEditActivities.html.twig', [
            'schedules'  => $schedules,
            'activities' => $activities,
            'cinescenie' => $cinescenie,
        ]);
    }

    /**
     * @Route("/admin/utilisateur/{userSlug}", name="userGeneral")
     */
    public function userGeneralAction(Request $request, $userSlug)
    {
        $cinescenies = $this->getDoctrine()
          ->getRepository('AppBundle:Cinescenie')
          ->findBy([], ['date' => 'ASC'])
        ;

        $skills = $this->getDoctrine()
          ->getRepository('AppBundle:Skill')
          ->findAll()
        ;

        $user = $this->getDoctrine()
          ->getRepository('AppBundle:User')
          ->findOneByUsername($userSlug)
        ;

        return $this->render('admin/user/userGeneral.html.twig', [
            'skills'      => $skills,
            'user'        => $user,
            'cinescenies' => $cinescenies,
        ]);
    }

    /**
     * @Route("/admin/utilisateurs", name="usersList")
     */
    public function usersListAction(Request $request)
    {
        /*
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findBy([], [
                'firstname' => 'ASC',
            ])
        ;
        */
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->getAndCountSchedules()
        ;

        return $this->render('admin/user/usersList.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/admin/utilisateur/{userSlug}/editer-profil", name="userEditAccount")
     */
    public function userEditAccountAction(Request $request, $userSlug)
    {
        $user = $this->getDoctrine()
          ->getRepository('AppBundle:User')
          ->findOneByUsername($userSlug)
        ;

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('userGeneral', ['userSlug' => $user->getUsername()]);
        }

        return $this->render('admin/user/userEditAccount.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/utilisateur/{userSlug}/editer-competences", name="userEditSkills")
     */
    public function userEditSkillsAction(Request $request, $userSlug)
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

        $form = $this->createForm(ChoiceSkillType::class, $skills, ['defaultSkills' => $defaultSkills]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Supprimer les compétences
            $userSkills = $user->getUserSkills();
            foreach ($userSkills as $userSkill) {
                $em->remove($userSkill);
            }

            // Ajouter les compétences
            $keys = $request->request->get('choice_skill')['skills'];
            $skills = $form->getData();
            foreach ($keys as $key) {
                $skill = $skills[$key];
                $userSkill = new UserSkill();
                $userSkill->setUser($user);
                $userSkill->setSkill($skill);
                $em->persist($userSkill);
            }

            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('userGeneral', ['userSlug' => $user->getUsername()]);
        }

        return $this->render('admin/user/userEditSkills.html.twig', [
            'skills' => $skills,
            'user'   => $user,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/utilisateur/{userSlug}/editer-planning", name="userEditSchedule")
     */
    public function userEditScheduleAction(Request $request, $userSlug)
    {
        $user = $this->getDoctrine()
          ->getRepository('AppBundle:User')
          ->findOneByUsername($userSlug)
        ;

        $cinescenies = $this->getDoctrine()
          ->getRepository('AppBundle:Cinescenie')
          ->findBy([], ['date' => 'ASC'])
        ;

        $defaultCinescenies = $this->getDoctrine()
          ->getRepository('AppBundle:Cinescenie')
          ->getByUser($user)
        ;

        $form = $this->createForm(ChoiceCinescenieType::class, $cinescenies, ['defaultCinescenies' => $defaultCinescenies]);
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

            $keys = $request->request->get('choice_cinescenie')['cinescenies'];
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

                // Empêcher de supprimer un planning quand la cinéscénie est validée
                if ($cineToDelete->getState() != Cinescenie::STATE_VALIDATE) {
                    $em->remove($schedule);
                }
            }

            // Planning à ajouter
            $cinesceniesToAdd = array_diff_key($cinesceniesFromForm, $cinesceniesFromSchedules);
            foreach ($cinesceniesToAdd as $cineToAdd) {
                // Empêcher d'ajouter un planning quand la cinéscénie est validée
                if ($cineToAdd->getState() == Cinescenie::STATE_VALIDATE) {
                    continue;
                }

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

        return $this->render('admin/user/userEditSchedule.html.twig', [
            'user'        => $user,
            'cinescenies' => $cinescenies,
            'form'        => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/parametrage", name="setting")
     */
    public function settingAction(Request $request)
    {
        return $this->render('admin/setting.html.twig');
    }
}
