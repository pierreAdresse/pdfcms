<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository\CinescenieRepository;
use AppBundle\Form\ChoiceSkillType;
use AppBundle\Form\ChoiceSpecialtyType;
use AppBundle\Form\ChoiceCinescenieType;
use AppBundle\Form\ChoiceMultiCinescenieType;
use AppBundle\Form\ChoiceMemberForActivityType;
use AppBundle\Form\ChoiceMemberForSpecialtyType;
use AppBundle\Entity\MemberSkill;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Cinescenie;
use AppBundle\Entity\Skill;
use AppBundle\Entity\Specialty;
use AppBundle\Entity\GroupActivities;
use AppBundle\Entity\Activity;
use AppBundle\Entity\Member;
use AppBundle\Entity\MemberSpecialty;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\Cinescenie as CinescenieService;
use AppBundle\Service\Date;
use AppBundle\Service\MemberService;

class ManagementController extends Controller
{
    /**
     * @Route("/gestion/membres/tableau-de-bord", name="memberDashboard")
     */
    public function dashboardAction(MemberService $serviceMember, Date $serviceDate)
    {
        // Membres qui ont moins de 15 séances
        $members = $serviceMember->getAndCountSchedules();

        // Les Cinéscénie avec des rôles sans affectation
        $date        = $serviceDate->getSeasonDate();
        $cinescenies = $this->getDoctrine()
            ->getRepository('AppBundle:Cinescenie')
            ->getCountSchedules($date)
        ;

        $activities = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findBy([
                'allowForDivision' => true,
            ])
        ;

        return $this->render('management/member/dashboard.html.twig', [
            'members'          => $members,
            'cinescenies'      => $cinescenies,
            'numberActivities' => count($activities),
        ]);
    }

    /**
     * @Route("/gestion/membres/planning", name="memberSchedule")
     */
    public function scheduleAction(CinescenieService $serviceCinescenie, Request $request)
    {
        $members = $this->getDoctrine()
          ->getRepository('AppBundle:Member')
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

        return $this->render('management/member/schedule.html.twig', [
            'cinescenies'      => $cinescenies,
            'numberActivities' => count($activities),
        ]);
    }

    /**
     * @Route("/gestion/membres/repartition-roles", name="memberActivityDivision")
     */
    public function activityDivisionAction(CinescenieService $serviceCinescenie, Date $serviceDate, MemberService $serviceMember, Request $request)
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

            $specialties = $this->getDoctrine()
                ->getRepository('AppBundle:Specialty')
                ->findAll()
            ;

            $date = $serviceDate->getSeasonDate();

            $cinescenies = $this->getDoctrine()
                ->getRepository('AppBundle:Cinescenie')
                ->getByDateGreaterThan($cinescenie->getDate())
            ;

            foreach($cinescenies as $key => $cinescenie) {
                // Permet de ne pas dépasser la mémoire allouée
                set_time_limit(120);

                $serviceMember->cleanSchedules($cinescenie);

                $pastCinescenies = $serviceCinescenie->getCinesceniesBetween($date, $cinescenie->getDate());

                // Choix d'un spécialiste
                    // Choisir un membre spécialiste qui n'a pas fait ce rôle la dernière fois et qui l'a peu fait
                        // Si pas trouvé alors essayer avec un membre qui l'a déjà fait la dernière fois et qui l'a peu fait
                    // Trouver un rôle que le membre n'a pas fait la dernière fois et dont il a les compétences (on admettra que ces personnes peuvent faire plusieurs autres rôles puisque ce sont des spécialistes ... donc pas de vérification si un rôle est trouvé car il y en aura forcément de disponible ... pour le moment).
                    // Garder en tête que ce rôle et ce membre sont occupés pour la suite de la répartition

                $membersSelected    = [];
                $activitiesComplete = [];
                /*foreach ($specialties as $specialty) {
                    $members        = $serviceMember->getForDivisionSpecialty($pastCinescenies, $specialty);
                    $membersSpe     = $serviceMember->filterMemberPresent($members, $cinescenie, $membersSelected);
                    $membersSpeSort = $serviceMember->filterByDifferentLastSpecialty($members, $cinescenie, $date, $specialty);

                    // Pour choisir le rôle :
                    // Trouver quel était le dernier groupe de rôle effectué par le membre
                    // En choisir un différent dans la liste des rôles possible et qu'il a peu fait
                    $memberIsFind = false;

                    if (!empty($membersSpeSort)) {
                        $member       = $membersSpeSort[0];
                        $memberIsFind = true;
                    } elseif (empty($membersSpeSort) && !empty($membersSpe)) {
                        $member       = $membersSpe[0];
                        $memberIsFind = true;
                    } else {
                        // On ne peut rien faire donc il y aura une alerte sur le planning en question et sur le tableau de bord pour avertir qu'il n'y a pas ce spécialiste.
                    }

                    if ($memberIsFind) {
                        $membersSelected[]   = $member->getId();
                        $lastGroupActivities = $serviceMember->getLastGroupActivities($member, $cinescenie, $date);
                        $activity            = $serviceMember->getActivityBySpecialityAndLastGroupActivities($member, $specialty, $lastGroupActivities);
                        $serviceMember->setActivityAndSpecialtyForMember($member, $activity, $specialty, $cinescenie);
                        $activitiesComplete[] = $activity->getId();
                    }
                }

                // Enlever de la liste des rôles ceux qui sont choisis pour les spécialités
                // A partir d'une nouvelle liste pour ne pas biaiser la liste principale
                $filterActivities = [];
                foreach ($activities as $activity) {
                    if (!in_array($activity->getId(), $activitiesComplete)) {
                        $filterActivities[] = $activity;
                    }
                }*/

                // Choix du rôle
                foreach ($activities as $activity) {
                    // Récupération des compétences nécéssaires pour le rôle
                    $skillActivities  = $activity->getSkillActivities();
                    $skills           = [];
                    foreach ($skillActivities as $skillActivity) {
                        $skills[] = $skillActivity->getSkill();
                    }

                    // T1
                    /*
                        Membres avec la compétence demandée
                        Membres avec comme compétence principale la compétence demandée et le quota non atteint
                        Membres présents et qui ne sont pas déjà sélectionnés
                        Membres dont le dernier rôle fait n'est pas celui demandé / Si pas de membres alors ce critère est ignoré
                        Membres dont le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées est le plus petit
                        Membres dont le nombre de fois fait le groupe de rôle est le plus petit
                        Membres dont le nombre de fois fait le rôle est le plus petit
                    */
                    $members      = $serviceMember
                        ->getForDivisionT1($pastCinescenies, $skills, $activity, $serviceCinescenie->getQuota())
                    ;
                    $memberResult = $serviceMember
                        ->filterBy($members, $membersSelected, $cinescenie, $date, $activity, $pastCinescenies)
                    ;

                    if (!is_null($memberResult)) {
                        $membersSelected[] = $memberResult;
                        $serviceMember->setActivityForMember($memberResult, $activity, $cinescenie);
                    } else {
                        // T2
                        /*
                            Membres avec la compétence demandée
                            Membres présents et qui ne sont pas déjà sélectionnés
                            Membre dont le dernier rôle fait n'est pas celui demandé / Si pas de membres alors ce critère est ignoré
                            Membres dont le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées est le plus petit
                            Membres dont le nombre de fois fait le groupe de rôle est le plus petit
                            Membres dont le nombre de fois fait le rôle est le plus petit
                        */
                        $members      = $serviceMember
                            ->getForDivisionT2($pastCinescenies, $skills, $activity)
                        ;
                        $memberResult = $serviceMember
                            ->filterBy($members, $membersSelected, $cinescenie, $date, $activity, $pastCinescenies)
                        ;

                        if (!is_null($memberResult)) {
                            $membersSelected[] = $memberResult;
                            $serviceMember->setActivityForMember($memberResult, $activity, $cinescenie);
                        }
                    }
                }

                $em->flush();
            }

            $this->addFlash(
                'notice',
                'La répartition est terminée !'
            );

            return $this->redirectToRoute('memberSchedule');
        }

        return $this->render('management/member/activityDivision.html.twig', [
            'cinescenies' => $cinescenies,
            'form'        => $form->createView(),
        ]);
    }

    /**
     * @Route("/gestion/membres/planning/{cinescenie}/editer-roles", name="memberScheduleEditActivities")
     */
    public function scheduleEditActivitesAction(Request $request, Cinescenie $cinescenie)
    {
        $activities = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findBy([
                'allowForDivision' => true,
            ], ['ranking' => 'ASC'])
        ;

        $specialties = $this->getDoctrine()
            ->getRepository('AppBundle:Specialty')
            ->findAll()
        ;

        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'cinescenie' => $cinescenie,
            ])
        ;

        return $this->render('management/member/scheduleEditActivities.html.twig', [
            'schedules'   => $schedules,
            'activities'  => $activities,
            'cinescenie'  => $cinescenie,
            'specialties' => $specialties,
        ]);
    }

    /**
     * @Route("/gestion/membres/planning/{cinescenie}/editer-roles/{activity}", name="memberScheduleEditActivity")
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

        $members = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->getForActivityWithSkill($cinescenie, $skill)
        ;

        $secondaryMembers = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->getForActivityWithoutSkill($cinescenie, $members)
        ;

        $memberLaissezPasser = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->find(Member::LAISSEZ_PASSER)
        ;
        $secondaryMembers[] = $memberLaissezPasser;

        $schedule = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findOneBy([
                'cinescenie' => $cinescenie,
                'activity'   => $activity,
            ])
        ;

        $memberSelected = null;
        if (!is_null($schedule)) {
            $memberSelected = $schedule->getMember();
            $members[] = $memberSelected;
        } else {
            $schedule = new Schedule();
            $schedule->setCinescenie($cinescenie);
            $schedule->setActivity($activity);
        }

        $form = $this->createForm(ChoiceMemberForActivityType::class, $members, [
            'memberSelected'   => $memberSelected,
            'secondaryMembers' => $secondaryMembers,
            'activityName'     => $activity->getName(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data   = $form->getData();
            $member = $data['members'];
            $em     = $this->getDoctrine()->getManager();

            $schedule->setActivity(null);
            $em->persist($schedule);

            if (!is_null($member)) {
                if ($member->getId() != Member::LAISSEZ_PASSER) {
                    $schedule = $this->getDoctrine()
                        ->getRepository('AppBundle:Schedule')
                        ->findOneBy([
                            'cinescenie' => $cinescenie,
                            'member'     => $member,
                        ])
                    ;
                }

                $schedule->setMember($member);
                $schedule->setActivity($activity);
            }

            $em->persist($schedule);
            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('memberScheduleEditActivities', ['cinescenie' => $cinescenie->getId()]);
        }

        return $this->render('management/member/scheduleEditActivity.html.twig', [
            'activity'   => $activity,
            'cinescenie' => $cinescenie,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @Route("/gestion/membres/planning/{cinescenie}/editer-specialite/{specialty}", name="memberScheduleEditSpecialty")
     */
    public function scheduleEditSpecialty(Request $request, Cinescenie $cinescenie, Specialty $specialty)
    {
        $specialtyActivity = $this->getDoctrine()
            ->getRepository('AppBundle:SpecialtyActivity')
            ->findOneBy([
                'specialty' => $specialty,
            ])
        ;
        $specialty = $specialtyActivity->getSpecialty();

        $members = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->getWithSpecialty($cinescenie, $specialty)
        ;

        $secondaryMembers = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->getWithoutSpecialty($cinescenie, $members)
        ;

        $memberLaissezPasser = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->find(Member::LAISSEZ_PASSER)
        ;
        $secondaryMembers[] = $memberLaissezPasser;

        $schedule = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findOneBy([
                'cinescenie' => $cinescenie,
                'specialty'  => $specialty,
            ])
        ;

        $memberSelected = null;
        if (!is_null($schedule)) {
            $memberSelected = $schedule->getMember();
            $members[] = $memberSelected;
        } else {
            $schedule = new Schedule();
            $schedule->setCinescenie($cinescenie);
            $schedule->setSpecialty($specialty);
        }

        $form = $this->createForm(ChoiceMemberForSpecialtyType::class, $members, [
            'memberSelected'   => $memberSelected,
            'secondaryMembers' => $secondaryMembers,
            'specialtyName'    => $specialty->getName(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data   = $form->getData();
            $member = $data['members'];
            $em     = $this->getDoctrine()->getManager();

            $schedule->setSpecialty(null);
            $em->persist($schedule);

            if (!is_null($member)) {
                if ($member->getId() != Member::LAISSEZ_PASSER) {
                    $schedule = $this->getDoctrine()
                        ->getRepository('AppBundle:Schedule')
                        ->findOneBy([
                            'cinescenie' => $cinescenie,
                            'member'     => $member,
                        ])
                    ;
                }

                $schedule->setMember($member);
                $schedule->setSpecialty($specialty);
            }

            $em->persist($schedule);
            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('memberScheduleEditActivities', ['cinescenie' => $cinescenie->getId()]);
        }

        return $this->render('management/member/scheduleEditSpecialty.html.twig', [
            'specialty'  => $specialty,
            'cinescenie' => $cinescenie,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @Route("/gestion/membres/{member}/editer-specialites", name="memberEditSpecialties")
     */
    public function editSpecialtiesAction(Request $request, Member $member)
    {
        $specialties = $this->getDoctrine()
          ->getRepository('AppBundle:Specialty')
          ->findAll()
        ;

        $defaultSpecialties = $this->getDoctrine()
          ->getRepository('AppBundle:Specialty')
          ->getByMember($member)
        ;

        $form = $this->createForm(ChoiceSpecialtyType::class, $specialties, ['defaultSpecialties' => $defaultSpecialties]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Supprimer les spécialités
            $memberSpecialties = $member->getMemberSpecialties();
            foreach ($memberSpecialties as $memberSpecialty) {
                $em->remove($memberSpecialty);
            }

            // Ajouter les spécialités
            $data = $form->getData();
            $specialties = $data['specialties'];
            foreach ($specialties as $specialty) {
                $memberSpecialty = new MemberSpecialty();
                $memberSpecialty->setMember($member);
                $memberSpecialty->setSpecialty($specialty);
                $em->persist($memberSpecialty);
            }

            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('memberGeneral', ['member' => $member->getId()]);
        }

        return $this->render('management/member/editSpecialties.html.twig', [
            'specialties' => $specialties,
            'member' => $member,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/gestion/membres/{member}/editer-competences", name="memberEditSkills")
     */
    public function editSkillsAction(Request $request, Member $member)
    {
        $skills = $this->getDoctrine()
          ->getRepository('AppBundle:Skill')
          ->findAll()
        ;

        $defaultSkills = $this->getDoctrine()
          ->getRepository('AppBundle:Skill')
          ->getByMember($member)
        ;

        $form = $this->createForm(ChoiceSkillType::class, $skills, ['defaultSkills' => $defaultSkills, 'mainSkill' => $member->getMainSkill()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Supprimer les compétences
            $memberSkills = $member->getMemberSkills();
            foreach ($memberSkills as $memberSkill) {
                $em->remove($memberSkill);
            }

            // Ajouter les compétences
            $data = $form->getData();
            $skills = $data['skills'];
            foreach ($skills as $skill) {
                $memberSkill = new MemberSkill();
                $memberSkill->setMember($member);
                $memberSkill->setSkill($skill);
                $em->persist($memberSkill);
            }

            // Ajouter la compétence principale
            // Le test est en commentaire car on peut très bien vouloir enlever la compétence principale
            $mainSkill = $data['mainSkill'];
            //if (!is_null($mainSkill)) {
                $member->setMainSkill($mainSkill);
                $em->persist($member);
            //}

            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('memberGeneral', ['member' => $member->getId()]);
        }

        return $this->render('management/member/editSkills.html.twig', [
            'skills' => $skills,
            'member' => $member,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/gestion/membres/{member}/editer-planning", name="memberEditSchedule")
     */
    public function editScheduleAction(CinescenieService $serviceCinescenie, Date $serviceDate, Request $request, Member $member)
    {
        $cinescenies        = $serviceCinescenie->getCurrents();
        $defaultCinescenies = $serviceCinescenie->getCurrentsByMember($member);
        $year               = $serviceDate->getSeasonYear();

        $form = $this->createForm(ChoiceMultiCinescenieType::class, $cinescenies, ['defaultCinescenies' => $defaultCinescenies]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Comparer le planning avant et après
            $schedules = $this->getDoctrine()
              ->getRepository('AppBundle:Schedule')
              ->findByMember($member)
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
                        'member'       => $member,
                    ])
                ;

                // TODO: protection pour empêcher de supprimer un planning passé ?
                $em->remove($schedule);
            }

            // Planning à ajouter
            $cinesceniesToAdd = array_diff_key($cinesceniesFromForm, $cinesceniesFromSchedules);
            foreach ($cinesceniesToAdd as $cineToAdd) {
                $schedule = new Schedule();
                $schedule->setMember($member);
                $schedule->setCinescenie($cineToAdd);
                $em->persist($schedule);
            }

            $em->flush();

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('memberGeneral', ['member' => $member->getId()]);
        }

        return $this->render('management/member/editSchedule.html.twig', [
            'member'      => $member,
            'cinescenies' => $cinescenies,
            'form'        => $form->createView(),
            'year'        => $year,
        ]);
    }

    /**
     * @Route("/gestion/membres/{member}", name="memberGeneral")
     */
    public function generalAction(CinescenieService $serviceCinescenie, Date $serviceDate, Request $request, Member $member)
    {
        $cinescenies = $serviceCinescenie->getCurrents();
        $year        = $serviceDate->getSeasonYear();
        $date        = $serviceDate->getSeasonDate();

        $skills = $this->getDoctrine()
            ->getRepository('AppBundle:Skill')
            ->findAll()
        ;

        $specialties = $this->getDoctrine()
            ->getRepository('AppBundle:Specialty')
            ->findAll()
        ;

        $stats = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->getSchedulesForMember($member)
        ;

        $gaStats = $this->getDoctrine()
            ->getRepository('AppBundle:GroupActivities')
            ->getSchedulesForMember($member)
        ;

        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'member'     => $member,
                'activity'   => null,
                'cinescenie' => $cinescenies,
            ])
        ;

        $numberPresenceWithoutActivity = count($schedules);

        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'member'     => $member,
                'cinescenie' => $cinescenies,
            ])
        ;

        $numberPresenceWithActivity = count($schedules) - $numberPresenceWithoutActivity;

        $countCinescenies = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->getAndCountSchedulesForMember($date, $member)
        ;

        $stats[] = [
            'name'          => 'Suppléant',
            'numberOfTimes' => $numberPresenceWithoutActivity,
        ];

        $gaStats[] = [
            'name'          => 'Sans activité',
            'numberOfTimes' => $numberPresenceWithoutActivity,
        ];

        return $this->render('management/member/general.html.twig', [
            'skills'                     => $skills,
            'specialties'                => $specialties,
            'member'                     => $member,
            'cinescenies'                => $cinescenies,
            'year'                       => $year,
            'stats'                      => $stats,
            'count'                      => $countCinescenies[0],
            'gaStats'                    => $gaStats,
            'numberPresenceWithActivity' => $numberPresenceWithActivity,
        ]);
    }

    /**
     * @Route("/", name="home")
     * @Route("/gestion/membres", name="memberList")
     */
    public function listAction(MemberService $serviceMember, Request $request)
    {
        $members = $serviceMember->getAndCountSchedules();

        return $this->render('management/member/list.html.twig', [
            'members' => $members,
        ]);
    }
}
