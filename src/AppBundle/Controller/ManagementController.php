<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository\CinescenieRepository;
use AppBundle\Form\ChoiceSkillType;
use AppBundle\Form\ChoiceNewType;
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
use AppBundle\Service\Logn;
use AppBundle\Service\MemberService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManagementController extends Controller
{
    /**
     * @Route("/gestion/membres/planning/{cinescenie}/excel", name="memberScheduleExcel")
     */
    public function scheduleExcelAction(Request $request, Cinescenie $cinescenie, Date $serviceDate)
    {
        $readerXlsx  = $this->get('phpoffice.spreadsheet')->createReader('Xlsx');
        $spreadsheet = $readerXlsx->load('schedule/FDR20180518.xlsx');

        // Date
        $date = $serviceDate->transformDatetimeToStringFr($cinescenie->getDate());
        $spreadsheet->getActiveSheet()->setCellValue('J1', $date);

        // Rôles
        $activities = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findBy([
                'allowForDivision' => true,
            ])
        ;

        foreach ($activities as $activity) {
            $cellExcel = $activity->getCellExcel();

            if (!empty($cellExcel)) {
                $schedules = $this->getDoctrine()
                    ->getRepository('AppBundle:Schedule')
                    ->findBy([
                        'cinescenie' => $cinescenie,
                        'activity'   => $activity,
                    ])
                ;

                $members = '';
                foreach ($schedules as $schedule) {
                    $member = $schedule->getMember();

                    if (!empty($member->getNickname())) {
                        $members    .= $member->getNickname().', ';
                    } else {
                        $firstLetter = substr($member->getLastname(), 0, 1);
                        $members    .= $member->getFirstname().' '.$firstLetter.', ';
                    }
                }

                if (!empty($members)) {
                    $members = substr($members, 0, -2);
                    $spreadsheet->getActiveSheet()->setCellValue($cellExcel, $members);
                }
            }
        }

        // Spécialités
        $specialties = $this->getDoctrine()
            ->getRepository('AppBundle:Specialty')
            ->findAll()
        ;

        foreach ($specialties as $specialty) {
            $cellExcel = $specialty->getCellExcel();

            if (!empty($cellExcel)) {
                $schedules = $this->getDoctrine()
                    ->getRepository('AppBundle:Schedule')
                    ->findBy([
                        'cinescenie' => $cinescenie,
                        'specialty'  => $specialty,
                    ])
                ;

                $members = '';
                foreach ($schedules as $schedule) {
                    $member = $schedule->getMember();

                    if (!empty($member->getNickname())) {
                        $members    .= $member->getNickname().', ';
                    } else {
                        $firstLetter = substr($member->getLastname(), 0, 1);
                        $members    .= $member->getFirstname().' '.$firstLetter.', ';
                    }
                }

                if (!empty($members)) {
                    $members = substr($members, 0, -2);
                    $spreadsheet->getActiveSheet()->setCellValue($cellExcel, $members);
                }
            }
        }

        // Sans rôle
        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'cinescenie' => $cinescenie,
                'activity'   => null,
            ])
        ;

        $members = '';
        foreach ($schedules as $schedule) {
            $member = $schedule->getMember();

            if (!empty($member->getNickname())) {
                $members    .= $member->getNickname().', ';
            } else {
                $firstLetter = substr($member->getLastname(), 0, 1);
                $members    .= $member->getFirstname().' '.$firstLetter.', ';
            }
        }

        // Mise à l'échelle pour l'impression
        $spreadsheet->getActiveSheet()->getPageSetup()->setScale(53);

        if (!empty($members)) {
            $members = substr($members, 0, -2);
            $spreadsheet->getActiveSheet()->setCellValue('F36', $members);
        }

        // Ecriture
        $writerXlsx = $this->get('phpoffice.spreadsheet')->createWriter($spreadsheet, 'Xlsx');
        $writerXlsx->save('tmpSchedule.xlsx');

        return new BinaryFileResponse('tmpSchedule.xlsx');
    }

    /**
     * @Route("/gestion/membres/tableau-de-bord/statistiques-excel", name="memberDashboardStats")
     */
    public function dashboardStatsAction(Request $request, CinescenieService $serviceCinescenie, Date $serviceDate)
    {
        // Initialisation Excel
        $readerXlsx  = $this->get('phpoffice.spreadsheet')->createReader('Xlsx');
        $spreadsheet = $readerXlsx->load('schedule/Statistiques.xlsx');

        // Initialisation des données
        $cinescenies = $serviceCinescenie->getCurrentsWithoutTraining();
        $members     = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->findBy(['deleted' => false], ['firstname' => 'ASC'])
        ;
        $cellsX      = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ'
        ];

        // Planning présences
        $spreadsheet->setActiveSheetIndexByName('Planning présences');

        $posCine  = [];
        $minCellX = $cellsX[1];
        $maxCellX = '';
        $sumCellX = '';
        foreach ($cinescenies as $key => $cinescenie) {
            $date = $serviceDate->transformDatetimeToStringFrWithoutYear($cinescenie->getDate());
            $spreadsheet->getActiveSheet()->setCellValue($cellsX[$key+1].'1', $date);
            $posCine[$cinescenie->getId()] = $cellsX[$key+1];
            $maxCellX = $cellsX[$key+1];
            $sumCellX = $cellsX[$key+2];
        }

        $cellY = 2;
        foreach ($members as $member) {
            $name = $member->getFirstname().' '.$member->getLastname();
            $spreadsheet->getActiveSheet()->setCellValue('A'.$cellY, $name);

            $schedules = $member->getSchedules();
            foreach ($schedules as $schedule) {
                $cine = $schedule->getCinescenie();
                
                if (!$cine->getIsTraining()) {
                    $cellX = $posCine[$cine->getId()];
                    $spreadsheet->getActiveSheet()->setCellValue($cellX.$cellY, 1);
                }
            }

            // Somme par ligne
            $formula = '=SUM('.$minCellX.$cellY.':'.$maxCellX.$cellY.')';
            $spreadsheet->getActiveSheet()->setCellValue($sumCellX.$cellY, $formula);

            $cellY++;
        }

        // Somme par colonne
        $maxCellY = $cellY - 1;
        foreach ($cinescenies as $key => $cinescenie) {
            $cellX   = $cellsX[$key+1];
            $formula = '=SUM('.$cellX.'2:'.$cellX.$maxCellY.')';
            $spreadsheet->getActiveSheet()->setCellValue($cellX.$cellY, $formula);
        }

        // Mise en forme conditionnelle
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        $conditional->addCondition('15');
        $conditional->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $conditional->getStyle()->getFont()->setBold(true);

        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle($sumCellX.'2')->getConditionalStyles();
        $conditionalStyles[] = $conditional;

        $spreadsheet->getActiveSheet()->getStyle($sumCellX.'2:'.$sumCellX.$maxCellY)->setConditionalStyles($conditionalStyles);

        // -----------

        // Compétences
        $spreadsheet->setActiveSheetIndexByName('Compétences');

        $skills = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Skill')
            ->findAll()
        ;

        $posSkill = [];
        foreach ($skills as $key => $skill) {
            $spreadsheet->getActiveSheet()->setCellValue($cellsX[$key+1].'1', $skill->getName());
            $posSkill[$skill->getId()] = $cellsX[$key+1];
        }

        $cellY = 2;
        foreach ($members as $member) {
            $name = $member->getFirstname().' '.$member->getLastname();
            $spreadsheet->getActiveSheet()->setCellValue('A'.$cellY, $name);

            $memberSkills = $member->getMemberSkills();
            $skills       = [];
            foreach ($memberSkills as $memberSkill) {
                $skills[] = $memberSkill->getSkill();
            }

            $mainSkill = $member->getMainSkill();

            foreach ($skills as $skill) {
                $flag  = 1;
                $cellX = $posSkill[$skill->getId()];
                
                if (!is_null($mainSkill) && $mainSkill->getId() == $skill->getId()) {
                    $flag = 'P';
                    $spreadsheet->getActiveSheet()->getStyle($cellX.$cellY)->getFont()->setBold(true);
                }
                
                $spreadsheet->getActiveSheet()->setCellValue($cellX.$cellY, $flag);
            }

            $cellY++;
        }

        // -----------

        // Stats rôles
        $spreadsheet->setActiveSheetIndexByName('Stats rôles');

        $activities = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findBy(['allowForDivision' => true], ['orderDisplay' => 'ASC'])
        ;

        $posActivity = [];
        foreach ($activities as $key => $activity) {
            $spreadsheet->getActiveSheet()->setCellValue($cellsX[$key+1].'1', $activity->getName());
            $posActivity[$activity->getId()] = $cellsX[$key+1];
        }

        $cellY = 2;
        foreach ($members as $member) {
            $name = $member->getFirstname().' '.$member->getLastname();
            $spreadsheet->getActiveSheet()->setCellValue('A'.$cellY, $name);

            $stats = $this->getDoctrine()
                ->getRepository('AppBundle:Activity')
                ->getSchedulesForMember($member)
            ;

            foreach ($stats as $stat) {
                $cellX = $posActivity[$stat['id']];
                $spreadsheet->getActiveSheet()->setCellValue($cellX.$cellY, $stat['numberOfTimes']);
            }

            $cellY++;
        }

        // -----------

        // Stats groupes de rôles
        $spreadsheet->setActiveSheetIndexByName('Stats groupes de rôles');

        $groupActivities = $this
            ->getDoctrine()
            ->getRepository('AppBundle:GroupActivities')
            ->findAll()
        ;

        $posGroupActivities = [];
        foreach ($groupActivities as $key => $groupActivity) {
            // Petit byPass en dur pour enlever les groupes suppléant et laissez passer
            if ($groupActivity->getId() != 8 && $groupActivity->getId() != 9) {
                $spreadsheet->getActiveSheet()->setCellValue($cellsX[$key+1].'1', $groupActivity->getName());
                $posGroupActivities[$groupActivity->getId()] = $cellsX[$key+1];
            }
        }

        $cellY = 2;
        foreach ($members as $member) {
            $name = $member->getFirstname().' '.$member->getLastname();
            $spreadsheet->getActiveSheet()->setCellValue('A'.$cellY, $name);

            $gaStats = $this->getDoctrine()
                ->getRepository('AppBundle:GroupActivities')
                ->getSchedulesForMember($member)
            ;

            foreach ($gaStats as $gaStat) {
                $cellX = $posGroupActivities[$gaStat['id']];
                $spreadsheet->getActiveSheet()->setCellValue($cellX.$cellY, $gaStat['numberOfTimes']);
            }

            $cellY++;
        }

        $spreadsheet->setActiveSheetIndexByName('Planning présences');

        // Ecriture
        $writerXlsx = $this->get('phpoffice.spreadsheet')->createWriter($spreadsheet, 'Xlsx');
        $writerXlsx->save('tmpSchedule2.xlsx');

        return new BinaryFileResponse('tmpSchedule2.xlsx');
    }

    /**
     * @Route("/gestion/membres/tableau-de-bord", name="memberDashboard")
     */
    public function dashboardAction(MemberService $serviceMember, Date $serviceDate, CinescenieService $serviceCinescenie)
    {
        // Membres qui ont moins de 15 séances
        $members = $serviceMember->getAndCountSchedules();

        // Les Cinéscénie avec des rôles sans affectation
        $date        = $serviceDate->getSeasonDate();
        $cinescenies = $serviceCinescenie->getCurrents();

        $activities = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findBy([
                'allowForDivision' => true,
            ])
        ;

        $specialties = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Specialty')
            ->findAll()
        ;

        $cineComplete = [];
        $counter      = count($activities) + count($specialties);
        foreach ($cinescenies as $key => $cinescenie) {
            $activitiesId  = [];
            $specialtiesId = [];
            $schedules    = $cinescenie->getSchedules();
            foreach ($schedules as $schedule) {
                $activity  = $schedule->getActivity();
                $specialty = $schedule->getSpecialty();

                if (!is_null($activity) && !in_array($activity->getId(), $activitiesId)) {
                    $activitiesId[]  = $activity->getId();
                }

                if (!is_null($specialty) && !in_array($specialty->getId(), $specialtiesId)) {
                    $specialtiesId[] = $specialty->getId();
                }
            }
            
            $counterActSpe = count($activitiesId) + count($specialtiesId);

            if ($counter != $counterActSpe) {
                $cineComplete[$key]['cinescenie'] = $cinescenie;
                $cineComplete[$key]['manque']     = $counter - $counterActSpe;
            }
        }

        return $this->render('management/member/dashboard.html.twig', [
            'members'      => $members,
            'cineComplete' => $cineComplete,
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

        $activities = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->findBy([
                'allowForDivision' => true,
            ])
        ;

        $specialties = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Specialty')
            ->findAll()
        ;

        $cineComplete = [];
        $counter      = count($activities) + count($specialties);
        foreach ($cinescenies as $cinescenie) {
            $activitiesId  = [];
            $specialtiesId = [];
            $schedules     = $cinescenie->getSchedules();
            foreach ($schedules as $schedule) {
                $activity  = $schedule->getActivity();
                $specialty = $schedule->getSpecialty();

                if (!is_null($activity) && !in_array($activity->getId(), $activitiesId)) {
                    $activitiesId[]  = $activity->getId();
                }

                if (!is_null($specialty) && !in_array($specialty->getId(), $specialtiesId)) {
                    $specialtiesId[] = $specialty->getId();
                }
            }
            
            $counterActSpe = count($activitiesId) + count($specialtiesId);
            $result = false;
            if ($counterActSpe == $counter) {
                $result = true;
            }
            $cineComplete[$cinescenie->getId()] = $result;
        }

        return $this->render('management/member/schedule.html.twig', [
            'cinescenies'  => $cinescenies,
            'cineComplete' => $cineComplete,
        ]);
    }

    /**
     * @Route("/gestion/membres/repartition-roles", name="memberActivityDivision")
     */
    public function activityDivisionAction(CinescenieService $serviceCinescenie, Date $serviceDate, MemberService $serviceMember, Request $request, Logn $log)
    {
        $cinescenies = $serviceCinescenie->getFutures();

        $form = $this->createForm(ChoiceCinescenieType::class, $cinescenies);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em         = $this->getDoctrine()->getManager();
            $data       = $form->getData();
            $cinescenie = $data['cinescenie'];

            $messageLog = 'Répartition à partir de '.$cinescenie->getDate()->format('d/m/Y').' ('.$cinescenie->getId().')';
            $log->log($this->getUser(), $messageLog, 'Répartition');

            $activities = $this->getDoctrine()
                ->getRepository('AppBundle:Activity')
                ->findBy([
                    'allowForDivision' => true,
                ], ['ranking' => 'ASC'])
            ;

            $specialties = $this->getDoctrine()
                ->getRepository('AppBundle:Specialty')
                ->findBy([], ['ranking' => 'ASC'])
            ;

            $date = $serviceDate->getSeasonDate();

            $cinesceniesWithoutTraining = $this->getDoctrine()
                ->getRepository('AppBundle:Cinescenie')
                ->getByDateGreaterThanWithoutTraining($cinescenie->getDate())
            ;
                
            $serviceMember->cleanSchedules($cinesceniesWithoutTraining);

            // Algo V3
            $groupActivities = $this
                ->getDoctrine()
                ->getRepository('AppBundle:GroupActivities')
                ->findAll()
            ;

            // Nombre de séances max
            $cines = $this
                ->getDoctrine()
                ->getRepository('AppBundle:Cinescenie')
                ->findBy(['isTraining' => 0])
            ;
            $numberMaxCinescenies = count($cines);

            foreach($cinesceniesWithoutTraining as $key => $cinescenie) {
                // Permet de ne pas dépasser la mémoire allouée
                set_time_limit(120);

                //$pastCinescenies = $serviceCinescenie->getCinesceniesBetween($date, $cinescenie->getDate());
//var_dump('Cinéscénie du '.$cinescenie->getDate()->format('d/m/Y H:i'));
                // ---------------------------------

                /* Algo V3
                    On récupère la liste des groupes de rôles
                    On les ordonne en mettant celui ou il y a le moins de personnes présentes à la date pour les compétences demandées en priorité
                    On récupère le nombre de personnes nécéssaire pour tous les rôles du groupe (+ 1 ou 2) dont le ratio de présence est le plus bas
                    On calcule pour chaque personne et chaque rôle du groupe le ratio de quand les personnes devraient faire le rôle (répartition homogène)
                     et le plus petit de tous prend le rôle, puis on passe au rôle suivant, etc ...

                    Récupération des skills
                    Ordonner les skills du plus au moins immortant (nom de personnes présentes avec le skill le plus petit)
                    Etape X : Pour chaque skill récupération des activities
                    Pour le nombre d'activities + 1 ou 2 récupération des membres dont le ratio de participation est le plus bas
                    Etape Y : Pour chaque membre et pour chaque activity calcul du ratio de répartition homogène
                    Affectation de l'activity au membre qui a le ratio de répartition homogène le plus bas
                    Nettoyage des listes en enlevant le membre et l'activity
                    Recommencer à l'étape Y, quand plus de membre ou d'activity recommencer à l'étape X, quand plus de skill terminer

                */

                $serviceMember->filterByAlgoV3($cinescenie, $date, $groupActivities, $numberMaxCinescenies);

                // ---------------------------------

                // Choix des spécialistes
                /*
                    Membres avec la spécialité demandée
                    Membres présents et qui ne sont pas déjà sélectionnés
                    Membres dont la derniere spécialité faite n'est pas celle demandée / Si pas de membres alors ce critère est ignoré
                    Membres dont le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées est le plus petit
                    Choisir un rôle via le groupe de rôle possible qui n'a été fait la dernière fois et le moins fait puis le rôle possible le moins fait 
                */
/*
                $membersSelected    = [];
                $activitiesComplete = [];
                foreach ($specialties as $specialty) {
                    $members      = $serviceMember->getForDivisionSpecialty($specialty);
                    $memberResult = $serviceMember
                        ->filterSpecialtyBy($members, $membersSelected, $cinescenie, $date, $specialty)
                    ;

                    if (!is_null($memberResult)) {
                        $membersSelected[]   = $memberResult;
                        $lastGroupActivities = $serviceMember->getLastGroupActivities($memberResult, $cinescenie, $date);
                        $activity            = $serviceMember->getActivityBySpecialityAndLastGroupActivities($memberResult, $specialty, $lastGroupActivities, $activitiesComplete);
                        $serviceMember->setActivityAndSpecialtyForMember($memberResult, $activity, $specialty, $cinescenie);

                        if (!is_null($activity)) {
                            $activitiesComplete[] = $activity->getId();
                        }
                    } else {
                        // Aucun membre trouvé donc il y aura une alerte sur le planning en question et sur le tableau de bord pour avertir qu'il n'y a pas ce spécialiste.
                    }
                }

                // Enlever de la liste des rôles ceux qui sont choisis pour les spécialités
                // A partir d'une nouvelle liste pour ne pas biaiser la liste principale
                $filterActivities = [];
                foreach ($activities as $activity) {
                    if (!in_array($activity->getId(), $activitiesComplete)) {
                        $filterActivities[] = $activity;
                    }
                }

                // Choix des rôles
                foreach ($filterActivities as $activity) {
                    // Récupération des compétences nécéssaires pour le rôle
                    $skillActivities  = $activity->getSkillActivities();
                    $skills           = [];
                    foreach ($skillActivities as $skillActivity) {
                        $skills[] = $skillActivity->getSkill();
                    }
*/
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
/*                        
                    $members      = $serviceMember
                        ->getForDivisionT1($pastCinescenies, $skills, $activity, $serviceCinescenie->getQuota())
                    ;

                    $memberResult = $serviceMember
                        ->filterBy($members, $membersSelected, $cinescenie, $date, $activity, $pastCinescenies, true)
                    ;
*/
                    /*$memberResult = $serviceMember
                        ->filterByWeight($members, $membersSelected, $cinescenie, $date, $activity, $pastCinescenies, true)
                    ;*/
/*
                    if (!is_null($memberResult)) {
                        $membersSelected[] = $memberResult;
                        $serviceMember->setActivityForMember($memberResult, $activity, $cinescenie);
                    } else {
*/
                        // T2
                        /*
                            Membres avec la compétence demandée
                            Membres présents et qui ne sont pas déjà sélectionnés
                            Membre dont le dernier rôle fait n'est pas celui demandé / Si pas de membres alors ce critère est ignoré
                            Membres dont le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées est le plus petit
                            Membres dont le nombre de fois fait le groupe de rôle est le plus petit
                            Membres dont le nombre de fois fait le rôle est le plus petit
                        */
/*
                        $members      = $serviceMember
                            ->getForDivisionT2($skills)
                        ;
                        $memberResult = $serviceMember
                            ->filterBy($members, $membersSelected, $cinescenie, $date, $activity, $pastCinescenies)
                        ;
*/
                        /*$memberResult = $serviceMember
                            ->filterByWeight($members, $membersSelected, $cinescenie, $date, $activity, $pastCinescenies)
                        ;*/
/*
                        if (!is_null($memberResult)) {
                            $membersSelected[] = $memberResult;
                            $serviceMember->setActivityForMember($memberResult, $activity, $cinescenie);
                        }

                    }
                }
*/
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
            ], ['orderDisplay' => 'ASC'])
        ;

        $specialties = $this->getDoctrine()
            ->getRepository('AppBundle:Specialty')
            ->findBy([], ['ranking' => 'ASC'])
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
     * @Route("/gestion/membres/planning/{cinescenie}/editer-role/{activity}", name="memberScheduleEditActivity")
     */
    public function scheduleEditActivity(Request $request, Cinescenie $cinescenie, Activity $activity)
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

        $hasLaissezPasser = false;
        foreach ($secondaryMembers as $secondaryMember) {
            if ($secondaryMember->getId() == Member::LAISSEZ_PASSER) {
                $hasLaissezPasser = true;
                break;
            }
        }

        $memberLaissezPasser = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->find(Member::LAISSEZ_PASSER)
        ;

        $scheduleLp = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findOneBy([
                'cinescenie' => $cinescenie,
                'member'     => $memberLaissezPasser,
                'activity'   => $activity,
            ])
        ;

        if (!$hasLaissezPasser && is_null($scheduleLp)) {
            $secondaryMembers[] = $memberLaissezPasser;
        }

        $membersSelected = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->getSelected($cinescenie, $activity)
        ;

        $form = $this->createForm(ChoiceMemberForActivityType::class, $members, [
            'secondaryMembers' => $secondaryMembers,
            'activityName'     => $activity->getName(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data   = $form->getData();
            $member = $data['members'];
            $em     = $this->getDoctrine()->getManager();

            if (!is_null($member)) {
                if ($member->getId() != Member::LAISSEZ_PASSER) {
                    $schedule = $this->getDoctrine()
                        ->getRepository('AppBundle:Schedule')
                        ->findOneBy([
                            'cinescenie' => $cinescenie,
                            'member'     => $member,
                        ])
                    ;
                } else {
                    $schedule = new Schedule();
                    $schedule->setCinescenie($cinescenie);
                }

                if ($cinescenie->getIsTraining()) {
                    $schedule->setIsTraining(true);
                }

                $schedule->setMember($member);
                $schedule->setActivity($activity);
                $em->persist($schedule);
                $em->flush();

                $this->addFlash(
                    'notice',
                    $member->getFirstname().' '.$member->getLastname().' est ajouté(e) au rôle !'
                );
            }

            return $this->redirectToRoute('memberScheduleEditActivity', [
                'cinescenie' => $cinescenie->getId(),
                'activity'   => $activity->getId(),
            ]);
        }

        return $this->render('management/member/scheduleEditActivity.html.twig', [
            'activity'        => $activity,
            'cinescenie'      => $cinescenie,
            'form'            => $form->createView(),
            'membersSelected' => $membersSelected,
        ]);
    }

    /**
     * @Route("/gestion/membres/planning/{cinescenie}/editer-role/{activity}/supprimer-membre/{member}", name="memberScheduleEditActivityDeleteMember")
     */
    public function scheduleEditActivityDeleteMember(Request $request, Cinescenie $cinescenie, Activity $activity, Member $member)
    {
        $em = $this->getDoctrine()->getManager();

        if ($member->getId() == Member::LAISSEZ_PASSER) {
            $schedule = $this->getDoctrine()
                ->getRepository('AppBundle:Schedule')
                ->findOneBy([
                    'cinescenie' => $cinescenie,
                    'member'     => $member,
                    'activity'   => $activity,
                ])
            ;
            $em->remove($schedule);
        } else {
            $schedule = $this->getDoctrine()
                ->getRepository('AppBundle:Schedule')
                ->findOneBy([
                    'cinescenie' => $cinescenie,
                    'member'     => $member,
                ])
            ;
            $schedule->setActivity(null);
            $em->persist($schedule);
        }

        $em->flush();

        $this->addFlash(
            'notice',
            $member->getFirstname().' '.$member->getLastname().' est supprimé(e) du rôle !'
        );

        return $this->redirectToRoute('memberScheduleEditActivity', [
            'cinescenie' => $cinescenie->getId(),
            'activity'   => $activity->getId(),
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

        $hasLaissezPasser = false;
        foreach ($secondaryMembers as $secondaryMember) {
            if ($secondaryMember->getId() == Member::LAISSEZ_PASSER) {
                $hasLaissezPasser = true;
                break;
            }
        }

        $memberLaissezPasser = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->find(Member::LAISSEZ_PASSER)
        ;

        $scheduleLp = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findOneBy([
                'cinescenie' => $cinescenie,
                'member'     => $memberLaissezPasser,
                'specialty'  => $specialty,
            ])
        ;

        if (!$hasLaissezPasser && is_null($scheduleLp)) {
            $secondaryMembers[] = $memberLaissezPasser;
        }

        $membersSelected = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->getSelectedSpecialty($cinescenie, $specialty)
        ;

        $form = $this->createForm(ChoiceMemberForSpecialtyType::class, $members, [
            'secondaryMembers' => $secondaryMembers,
            'specialtyName'    => $specialty->getName(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data   = $form->getData();
            $member = $data['members'];
            $em     = $this->getDoctrine()->getManager();

            if (!is_null($member)) {
                if ($member->getId() != Member::LAISSEZ_PASSER) {
                    $schedule = $this->getDoctrine()
                        ->getRepository('AppBundle:Schedule')
                        ->findOneBy([
                            'cinescenie' => $cinescenie,
                            'member'     => $member,
                        ])
                    ;
                } else {
                    $schedule = new Schedule();
                    $schedule->setCinescenie($cinescenie);
                }

                if ($cinescenie->getIsTraining()) {
                    $schedule->setIsTraining(true);
                }

                $schedule->setMember($member);
                $schedule->setSpecialty($specialty);
                $em->persist($schedule);
                $em->flush();

                $this->addFlash(
                    'notice',
                    $member->getFirstname().' '.$member->getLastname().' est ajouté(e) à la spécialité !'
                );
            }

            return $this->redirectToRoute('memberScheduleEditSpecialty', [
                'cinescenie' => $cinescenie->getId(),
                'specialty'  => $specialty->getId(),
            ]);
        }

        return $this->render('management/member/scheduleEditSpecialty.html.twig', [
            'specialty'       => $specialty,
            'cinescenie'      => $cinescenie,
            'form'            => $form->createView(),
            'membersSelected' => $membersSelected,
        ]);
    }

    /**
     * @Route("/gestion/membres/planning/{cinescenie}/editer-specialite/{specialty}/supprimer-membre/{member}", name="memberScheduleEditSpecialtyDeleteMember")
     */
    public function scheduleEditSpecialtyDeleteMember(Request $request, Cinescenie $cinescenie, Specialty $specialty, Member $member)
    {
        $em = $this->getDoctrine()->getManager();

        if ($member->getId() == Member::LAISSEZ_PASSER) {
            $schedule = $this->getDoctrine()
                ->getRepository('AppBundle:Schedule')
                ->findOneBy([
                    'cinescenie' => $cinescenie,
                    'member'     => $member,
                    'specialty'  => $specialty,
                ])
            ;
            $em->remove($schedule);
        } else {
            $schedule = $this->getDoctrine()
                ->getRepository('AppBundle:Schedule')
                ->findOneBy([
                    'cinescenie' => $cinescenie,
                    'member'     => $member,
                ])
            ;
            $schedule->setSpecialty(null);
            $em->persist($schedule);
        }

        $em->flush();

        $this->addFlash(
            'notice',
            $member->getFirstname().' '.$member->getLastname().' est supprimé(e) de la spécialité !'
        );

        return $this->redirectToRoute('memberScheduleEditSpecialty', [
            'cinescenie' => $cinescenie->getId(),
            'specialty'  => $specialty->getId(),
        ]);
    }

    /**
     * @Route("/gestion/membres/{member}/editer-specialites", name="memberEditSpecialties")
     */
    public function editSpecialtiesAction(Request $request, Member $member, Logn $log)
    {
        $specialties = $this->getDoctrine()
          ->getRepository('AppBundle:Specialty')
          ->findBy([], ['ranking' => 'ASC'])
        ;

        $defaultSpecialties = $this->getDoctrine()
          ->getRepository('AppBundle:Specialty')
          ->getByMember($member)
        ;

        $form = $this->createForm(ChoiceSpecialtyType::class, $specialties, ['defaultSpecialties' => $defaultSpecialties]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $messageLog = 'Mise à jour des spécialités de '.$member->getFirstname().' '.$member->getLastname().' ('.$member->getId().'). ### Spécialités avant : ';

            // Supprimer les spécialités
            $memberSpecialties = $member->getMemberSpecialties();
            foreach ($memberSpecialties as $memberSpecialty) {
                $messageLog .= $memberSpecialty->getSpecialty()->getName().' ('.$memberSpecialty->getSpecialty()->getId().'), ';
                $em->remove($memberSpecialty);
            }

            // Ajouter les spécialités
            $data        = $form->getData();
            $specialties = $data['specialties'];
            $messageLog .= '### spécialités après : ';
            foreach ($specialties as $specialty) {
                $messageLog .= $specialty->getName().' ('.$specialty->getId().'), ';
                $memberSpecialty = new MemberSpecialty();
                $memberSpecialty->setMember($member);
                $memberSpecialty->setSpecialty($specialty);
                $em->persist($memberSpecialty);
            }

            $em->flush();

            $log->log($this->getUser(), $messageLog, 'Membre spécialités');

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
    public function editSkillsAction(Request $request, Member $member, Logn $log)
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

            $messageLog = 'Mise à jour des compétences de '.$member->getFirstname().' '.$member->getLastname().' ('.$member->getId().'). Compétences avant : ';

            // Supprimer les compétences
            $memberSkills = $member->getMemberSkills();
            foreach ($memberSkills as $memberSkill) {
                $messageLog .= $memberSkill->getSkill()->getName().' ('.$memberSkill->getSkill()->getId().'), ';
                $em->remove($memberSkill);
            }

            // Ajouter les compétences
            $data        = $form->getData();
            $skills      = $data['skills'];
            $messageLog .= '### compétences après : ';
            foreach ($skills as $skill) {
                $messageLog .= $skill->getName().' ('.$skill->getId().'), ';
                $memberSkill = new MemberSkill();
                $memberSkill->setMember($member);
                $memberSkill->setSkill($skill);
                $em->persist($memberSkill);
            }

            // Ajouter la compétence principale
            $mainSkill = $data['mainSkill'];
            $msBefore  = '';
            $msAfter   = '';
            $mainSk    = $member->getMainSkill();
            if (!is_null($mainSk)) {
                $msBefore = $mainSk->getName().' ('.$mainSk->getId().')';
            }

            if (!is_null($mainSkill)) {
                $msAfter = $mainSkill->getName().' ('.$mainSkill->getId().')';
            }

            $messageLog .= '### compétence principale avant et après : '.$msBefore.' / '.$msAfter;
                $member->setMainSkill($mainSkill);
                $em->persist($member);

            $em->flush();

            $log->log($this->getUser(), $messageLog, 'Membre compétences');

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
     * @Route("/gestion/membres/{member}/editer-nouveau", name="memberEditNew")
     */
    public function editNewAction(Request $request, Member $member, Logn $log)
    {
        $form = $this->createForm(ChoiceNewType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em     = $this->getDoctrine()->getManager();
            $member = $form->getData();

            $em->persist($member);
            $em->flush();

            $isNewString = 'Non';
            if ($member->getIsNew()) {
                $isNewString = 'Oui';
            }
            $messageLog = 'Mise à jour du boolean isNew à "'.$isNewString.'" de '.$member->getFirstname().' '.$member->getLastname().' ('.$member->getId().')';
            $log->log($this->getUser(), $messageLog, 'Membre nouveau');

            $this->addFlash(
                'notice',
                'Les données sont bien enregistrées !'
            );

            return $this->redirectToRoute('memberGeneral', ['member' => $member->getId()]);
        }

        return $this->render('management/member/editNew.html.twig', [
            'member' => $member,
            'form'   => $form->createView(),
        ]);
    }


    /**
     * @Route("/gestion/membres/{member}/editer-planning", name="memberEditSchedule")
     */
    public function editScheduleAction(CinescenieService $serviceCinescenie, Date $serviceDate, Request $request, Member $member, Logn $log)
    {
        $cinescenies        = $serviceCinescenie->getCurrents();
        $defaultCinescenies = $serviceCinescenie->getCurrentsByMember($member);
        $year               = $serviceDate->getSeasonYear();

        $form = $this->createForm(ChoiceMultiCinescenieType::class, $cinescenies, [
            'defaultCinescenies' => $defaultCinescenies,
        ]);
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
            $messageLog          = 'Mise à jour du planning de '.$member->getFirstname().' '.$member->getLastname().' ('.$member->getId().') / Suppression des dates : ';
            $cinesceniesToDelete = array_diff_key($cinesceniesFromSchedules, $cinesceniesFromForm);
            foreach ($cinesceniesToDelete as $cineToDelete) {
                $messageLog .= $cineToDelete->getDate()->format('d/m/Y').' ('.$cineToDelete->getId().') ';
                $schedule = $this->getDoctrine()
                  ->getRepository('AppBundle:Schedule')
                  ->findOneBy([
                        'cinescenie' => $cineToDelete,
                        'member'       => $member,
                    ])
                ;

                $em->remove($schedule);
            }

            // Planning à ajouter
            $messageLog .= '/ Ajout des dates : ';
            $cinesceniesToAdd = array_diff_key($cinesceniesFromForm, $cinesceniesFromSchedules);
            foreach ($cinesceniesToAdd as $cineToAdd) {
                $messageLog .= $cineToAdd->getDate()->format('d/m/Y').' ('.$cineToAdd->getId().') ';
                $schedule = new Schedule();
                $schedule->setMember($member);
                $schedule->setCinescenie($cineToAdd);

                if ($cineToAdd->getIsTraining()) {
                    $schedule->setIsTraining(true);
                } else {
                    $schedule->setIsTraining(false);
                }

                $em->persist($schedule);
            }

            $em->flush();

            $log->log($this->getUser(), $messageLog, 'Planning');

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

        $cinesceniesWithoutTraining = $serviceCinescenie->getCurrentsWithoutTraining();

        $skills = $this->getDoctrine()
            ->getRepository('AppBundle:Skill')
            ->findAll()
        ;

        $specialties = $this->getDoctrine()
            ->getRepository('AppBundle:Specialty')
            ->findBy([], ['ranking' => 'ASC'])
        ;

        $stats = $this->getDoctrine()
            ->getRepository('AppBundle:Activity')
            ->getSchedulesForMember($member)
        ;

        $gaStats = $this->getDoctrine()
            ->getRepository('AppBundle:GroupActivities')
            ->getSchedulesForMember($member)
        ;

        $speStats = $this->getDoctrine()
            ->getRepository('AppBundle:Specialty')
            ->getSchedulesForMember($member)
        ;

        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'member'     => $member,
                'activity'   => null,
                'cinescenie' => $cinesceniesWithoutTraining,
            ])
        ;

        $numberPresenceWithoutActivity = count($schedules);

        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'member'     => $member,
                'cinescenie' => $cinesceniesWithoutTraining,
            ])
        ;

        $numberPresence             = count($schedules);
        $numberPresenceWithActivity = $numberPresence - $numberPresenceWithoutActivity;

        $schedules = $this->getDoctrine()
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'member'     => $member,
                'specialty'  => null,
                'cinescenie' => $cinesceniesWithoutTraining,
            ])
        ;

        $numberPresenceWithoutSpecialty = count($schedules);

        $stats[] = [
            'name'          => 'Sans rôle',
            'numberOfTimes' => $numberPresenceWithoutActivity,
        ];

        $gaStats[] = [
            'name'          => 'Sans rôle',
            'numberOfTimes' => $numberPresenceWithoutActivity,
        ];

        $speStats[] = [
            'name'          => 'Sans spécialité',
            'numberOfTimes' => $numberPresenceWithoutSpecialty,
        ];

        return $this->render('management/member/general.html.twig', [
            'skills'                     => $skills,
            'specialties'                => $specialties,
            'member'                     => $member,
            'cinescenies'                => $cinescenies,
            'year'                       => $year,
            'stats'                      => $stats,
            'gaStats'                    => $gaStats,
            'speStats'                   => $speStats,
            'numberPresenceWithActivity' => $numberPresenceWithActivity,
            'numberPresence'             => $numberPresence,
        ]);
    }

    /**
     * @Route("/", name="home")
     * @Route("/gestion/membres", name="memberList")
     */
    public function listAction(MemberService $serviceMember, Request $request)
    {
        $members = $serviceMember->getCountCinePlayAndCinePresent();

        return $this->render('management/member/list.html.twig', [
            'members' => $members,
        ]);
    }
}
