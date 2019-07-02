<?php
namespace AppBundle\Service;

use AppBundle\Service\Date;
use AppBundle\Service\Cinescenie as ServiceCinescenie;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Cinescenie;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Member;
use AppBundle\Entity\Activity;

class MemberService
{
	private $em;
	private $serviceDate;

	public function __construct(EntityManagerInterface $em, Date $serviceDate)
	{
		$this->em                = $em;
		$this->serviceDate       = $serviceDate;
	}

	public function getAndCountSchedules()
	{
        $date = $this->serviceDate->getSeasonDate();

        $members = $this->em
            ->getRepository('AppBundle:Member')
            ->getAndCountSchedules($date)
        ;

        return $members;
	}

    public function getAndCountCinePlay()
    {
        $date = $this->serviceDate->getSeasonDate();

        $members = $this->em
            ->getRepository('AppBundle:Member')
            ->getTotalCinePlay($date)
        ;

        return $members;
    }

    public function getCountCinePlayAndCinePresent()
    {
        $cinePresentMembers = $this->getAndCountSchedules();
        $cinePlayMembers    = $this->getAndCountCinePlay();

        $members = [];
        foreach ($cinePresentMembers as $key => $cinePresentMember) {
            $members[$key]['member']           = $cinePresentMember[0];
            $members[$key]['countCinescenies'] = $cinePresentMember['countCinescenies'];

            $isFind = false;
            foreach ($cinePlayMembers as $cinePlayMember) {
                if ($cinePresentMember[0]->getId() == $cinePlayMember['id']) {
                    $isFind  = true;
                    $percent = round($cinePlayMember['totalCinePlay'] / $cinePresentMember['countCinescenies'] * 100, 0);
                    $members[$key]['counter'] = $cinePlayMember['totalCinePlay'] .'/'. $cinePresentMember['countCinescenies'];
                    $members[$key]['ratio']   = $percent.'%';
                    break;
                }
            }

            if (!$isFind) {
                $members[$key]['counter'] = '0/'. $cinePresentMember['countCinescenies'];
                $members[$key]['ratio']   = '0%';
            }
        }

        return $members;
    }

    // Cette fonction permet d'effacer les rôles d'une cinéscénie et d'enlever les éventuels Laissez passer
    public function cleanSchedules($cinescenies)
    {
        // On commence par effacer tous les rôles
        $schedules = $this->em
            ->getRepository('AppBundle:Schedule')
            ->findBy([
              'cinescenie' => $cinescenies,
            ])
        ;

        foreach ($schedules as $schedule) {
            $member = $schedule->getMember();
            if (!is_null($member) && $member->getId() == Member::LAISSEZ_PASSER) {
                $this->em->remove($schedule);
            } else {
                $schedule->setActivity(null);
                $schedule->setSpecialty(null);
                $this->em->persist($schedule);
            }
        }

        $this->em->flush();
    }

    // T1
    /*
        Membres avec la compétence demandée
        Membre avec comme compétence principale la compétence demandée et le quota non atteint
    */
	public function getForDivisionT1($pastCinescenies, $skills, $activity, $quota)
	{
        $groupActivities = $activity->getGroupActivities();
        $activities = $groupActivities->getActivities();
        $members = $this->em
            ->getRepository('AppBundle:Member')
            ->getForDivisionT1($pastCinescenies, $skills, $activities, $quota)
        ;

        $resultMembers = [];
        foreach ($members as $member) {
            $resultMembers[] = $member['id'];
        }

        return $resultMembers;
	}

    /*
        Membres présents et qui ne sont pas déjà sélectionnés
        Membres dont le dernier rôle fait n'est pas celui demandé
        Membres dont le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées est le plus petit
        Membres dont le nombre de fois fait le groupe de rôle est le plus petit
        Membres dont le nombre de fois fait le rôle est le plus petit
    */
    public function filterBy($members, $membersSelected, $cinescenie, $date, $activity, $pastCinescenies, $byPass = false)
    {
        // Membres présents et qui ne sont pas déjà sélectionnés
        $presenceMembers = $this->filterByPresence($members, $cinescenie, $membersSelected);

        if (empty($presenceMembers)) {
            // Pas de membres disponible le rôle sera vide
            return null;
        } elseif (count($presenceMembers) == 1) {
            // Un seul membre disponible le rôle est pour lui
            return $presenceMembers[0];
        }

        // Membres dont le dernier rôle fait n'est pas celui demandé
        $diffLastActMembers = $this->filterByDifferentLastActivity($presenceMembers, $cinescenie, $date, $activity);

        // Au lieu de récupérer les membres dont le dernier rôle fait n'est pas celui demandé il faudrait récupérer les membres
        // dont c'est le tour via à vis d'une répartition harmonieuse dans la saison.

        if (empty($diffLastActMembers)) {
            // Aucun membre n'a de rôle différent depuis la dernière fois
            // Ce critère est donc mis de côté et la précédente liste est utilisée
            $diffLastActMembers = $presenceMembers;
        } elseif (count($diffLastActMembers) == 1) {
            // Un seul membre disponible le rôle est pour lui
            return $diffLastActMembers[0];
        }

        if (!$byPass) {
            // Membres dont le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées est le plus petit
            $ratioMembers = $this->orderByRatio($diffLastActMembers, $cinescenie, $date); 

            if (count($ratioMembers) == 1) {
                // Un seul membre disponible le rôle est pour lui
                return $ratioMembers[0];
            }
        } else {
            $ratioMembers = $diffLastActMembers;
        }

        // Membres dont le nombre de fois fait le rôle est le plus petit
        $activityMembers = $this->orderByActivity($ratioMembers, $activity, $pastCinescenies);
        
        if (count($activityMembers) == 1) {
            // Un seul membre disponible le rôle est pour lui
            return $activityMembers[0];
        }

        // Membres dont le nombre de fois fait le groupe de rôle est le plus petit
        $groupActMembers = $this->orderByGroupActivities($activityMembers, $activity, $pastCinescenies);

        // Un membre au hasard est sélectionné
        $randKeys = array_rand($groupActMembers);
        return $groupActMembers[$randKeys];
    }

    public function filterByWeight($members, $membersSelected, $cinescenie, $date, $activity, $pastCinescenies, $byPass = false)
    {
        // Membres présents et qui ne sont pas déjà sélectionnés
        $presenceMembers = $this->filterByPresence($members, $cinescenie, $membersSelected);

        if (empty($presenceMembers)) {
            // Pas de membres disponible le rôle sera vide
            return null;
        } elseif (count($presenceMembers) == 1) {
            // Un seul membre disponible le rôle est pour lui
            return $presenceMembers[0];
        }

        $membersByWeight = $this->filterByWeightProcess($presenceMembers, $cinescenie, $date, $activity, $byPass);

        $randKeys = array_rand($membersByWeight);
        return $membersByWeight[$randKeys];
    }

    /* Algo V3
        Récupération des skills
        Ordonner les skills du plus au moins immortant (nom de personnes présentes avec le skill le plus petit)
        Etape X : Pour chaque skill récupération des activities
        Pour le nombre d'activities + 1 ou 2 récupération des membres dont le ratio de participation est le plus bas
        Etape Y : Pour chaque membre et pour chaque activity calcul du ratio de répartition homogène
        Affectation de l'activity au membre qui a le ratio de répartition homogène le plus bas
        Nettoyage des listes en enlevant le membre et l'activity
        Recommencer à l'étape Y, quand plus de membre ou d'activity recommencer à l'étape X, quand plus de skill terminer
    */
    public function filterByAlgoV3($cinescenie, $date, $groupActivities, $numberMaxCinescenies)
    {
        // ### Récupération des skills
        // ### Ordonner les skills du plus au moins immortant (nom de personnes présentes avec le skill le plus petit)
        $skillsSorted = $this->sortSkills($cinescenie);
/*$listSkills = '';
foreach ($skillsSorted as $skill) {
    $skill = $skill['id'];
    $listSkills .= $skill->getName().', ';
}
var_dump('Ordre des compétences : '.$listSkills);*/

        // ### Etape X : Pour chaque skill récupération des activities
        $membersSelected = [];
        foreach ($skillsSorted as $skill) {
            $skill = $skill['id'];
            $skillActivities = $skill->getSkillActivities();

            // Nombre de personnes pour la compétence du rôle
            $numberMembersForSkill = count($skill->getMemberSkills());

            $activities = [];
            foreach ($skillActivities as $skillActivity) {
                $activities[] = $skillActivity->getActivity();
            }

            $nbActivities = count($activities);
            $nbActivitiesPlus = $nbActivities + 2; // équivalent à + 2 car les array commencent à 0

            // Nombre de rôles disponibles dans la saison
            $numberActivitiesSaison = $numberMaxCinescenies * count($activities);

            // Nombre de rôles max par membre dans la saison
            $numberActivitiesByMember = $numberActivitiesSaison / $numberMembersForSkill;

            // Nombre max de fois qu'un rôle peut être attribué
            $maxActivity = $numberActivitiesByMember / count($activities);

//var_dump('Pour la compétence : '.$skill->getName().', numberMaxCinescenies: '.$numberMaxCinescenies.', numberMembersForSkill: '.$numberMembersForSkill.', count($activities): '.count($activities).', numberActivitiesSaison: '.$numberActivitiesSaison.', numberActivitiesByMember: '.$numberActivitiesByMember.', maxActivity: '.$maxActivity);

            // ### Pour le nombre d'activities + 1 ou 2 récupération des membres dont le ratio de participation est le plus bas
            $members = $this->getForDivisionT2($skill);
            $membersPresents = $this->filterByPresence($members, $cinescenie, $membersSelected);

/*$listMembers = '';
foreach ($membersPresents as $membersPresent) {
    $listMembers .= $membersPresent.', ';
}
var_dump('Membres présents avec la compétence : '.$listMembers);*/

            $totalCineMembers = $this
                ->em
                ->getRepository('AppBundle:Member')
                ->getTotalCineByMember($membersPresents, $date, $cinescenie->getDate())
            ;

            $totalCinePlayMembers = $this
                ->em
                ->getRepository('AppBundle:Member')
                ->getTotalCinePlayByMember($membersPresents, $date, $cinescenie->getDate())
            ;

            $membersRatio = [];
            $membersSameActivities = [];
            $membersSameActivitiesGreaterThanLimit = [];
            $membersGreaterThanLimit = [];
            foreach ($totalCineMembers as $key => $totalCineMember) {
                // Nombre de fois à laquelle le membre à déjà fait les rôles
                $schedulesActivity = $this
                    ->em
                    ->getRepository('AppBundle:Schedule')
                    ->getForMemberAndActivities($totalCineMember['id'], $activities)
                ;
                $numberMemberDoActivity = count($schedulesActivity);

                // ---

                $isFind = false;
                foreach ($totalCinePlayMembers as $totalCinePlayMember) {
                    if ($totalCinePlayMember['id'] == $totalCineMember['id']) {
                        $totalCinePlay = $totalCinePlayMember['totalCinePlay'];
                        $isFind = true;
                        break;
                    }
                }

                if ($isFind) {
                    $ratio = round($totalCinePlay / $totalCineMember['totalCine'] * 100, 0);
                } else {
                    $ratio = 0;
                }

                // Dernier groupe de rôle
                $currentGroupActivities = $activities[0]->getGroupActivities();
                $isSameGroupActivities = false;

                $schedules = $this
                    ->em
                    ->getRepository('AppBundle:Schedule')
                    ->getLastPresence($totalCineMember['id'], $cinescenie->getDate())
                ;

                if ($schedules != null) {
                    $lastActivity = $schedules[0]->getActivity();
                    $lastGroupActivities = null;

                    if ($lastActivity != null) {
                        $lastGroupActivities = $lastActivity->getGroupActivities();

                        if ($lastGroupActivities->getId() == $currentGroupActivities->getId()) {
                            $isSameGroupActivities = true;
                        }
                    }
                }

                if ($isSameGroupActivities) {
                    if ($numberMemberDoActivity <= $numberActivitiesByMember) {
                        $membersSameActivities[$key]['id']      = $totalCineMember['id'];
                        $membersSameActivities[$key]['counter'] = $ratio;
                    } else {
                        $membersSameActivitiesGreaterThanLimit[$key]['id']      = $totalCineMember['id'];
                        $membersSameActivitiesGreaterThanLimit[$key]['counter'] = $ratio; 
                    }
                } else {
                    if ($numberMemberDoActivity <= $numberActivitiesByMember) {
                        $membersRatio[$key]['id']      = $totalCineMember['id'];
                        $membersRatio[$key]['counter'] = $ratio;
                    } else {
                        $membersGreaterThanLimit[$key]['id']      = $totalCineMember['id'];
                        $membersGreaterThanLimit[$key]['counter'] = $ratio;
                    }
                }
            }

            // Tri par ordre croissant du ratio
            $counter = [];
            foreach ($membersRatio as $key => $row) {
                $id[$key]      = $row['id'];
                $counter[$key] = $row['counter'];
            }
            array_multisort($counter, SORT_ASC, $membersRatio);

            $counter = [];
            foreach ($membersSameActivities as $key => $row) {
                $id[$key]      = $row['id'];
                $counter[$key] = $row['counter'];
            }
            array_multisort($counter, SORT_ASC, $membersSameActivities);

            $counter = [];
            foreach ($membersSameActivitiesGreaterThanLimit as $key => $row) {
                $id[$key]      = $row['id'];
                $counter[$key] = $row['counter'];
            }
            array_multisort($counter, SORT_ASC, $membersSameActivitiesGreaterThanLimit);

            $counter = [];
            foreach ($membersGreaterThanLimit as $key => $row) {
                $id[$key]      = $row['id'];
                $counter[$key] = $row['counter'];
            }
            array_multisort($counter, SORT_ASC, $membersGreaterThanLimit);

            $combinedMembers = array_merge($membersRatio, $membersGreaterThanLimit, $membersSameActivities, $membersSameActivitiesGreaterThanLimit);

/*$listMembersCombined = '';
foreach ($combinedMembers as $combinedMember) {
    $listMembersCombined .= $combinedMember['id'].', ';
}
var_dump('Ordre des membres : '.$listMembersCombined);*/


            // Réduction du nombre de membres
            $membersPresents = array_slice($combinedMembers, 0, $nbActivitiesPlus);
            $members = [];
            foreach ($membersPresents as $memberPresent) {
                $members[] = $memberPresent['id'];
            }

            // ### Etape Y : Pour chaque membre et pour chaque activity calcul du ratio de répartition homogène
            while ($members != null && $activities != null && count($members) > 0 && count($activities) > 0) {
                $result = $this->getMemberAndActivityWithRatioRepartitionHomogeneMin($members, $activities, $numberMembersForSkill, $numberMaxCinescenies, $numberActivitiesSaison, $numberActivitiesByMember, $maxActivity);

                if ($result['memberSelected'] != null && $result['activitySelected'] != null) {
                    // ### Affectation de l'activity au membre qui a le ratio de répartition homogène le plus bas
                    $membersSelected[] = $result['memberSelected'];
                    $this->setActivityForMember($result['memberSelected'], $result['activitySelected'], $cinescenie);

                    // ### Nettoyage des listes en enlevant le membre et l'activity
                    $keyMember = array_search($result['memberSelected'], $members);
                    unset($members[$keyMember]);

                    $keyActivity = array_search($result['activitySelected'], $activities);
                    unset($activities[$keyActivity]);
//var_dump('Membre sélectionné : '.$result['memberSelected'].', activité : '.$result['activitySelected']->getName());
                } else {
                    if (count($activities) > 0 && $nbActivitiesPlus <= count($combinedMembers) - 1) {
                        $membersPresents = array_slice($combinedMembers, $nbActivitiesPlus, 1);
                        $nbActivitiesPlus++;
                        $members = $membersPresents[0]['id'];
                    } else {
                        $members = null;
                    }
                }
            }
            /*
            if (count($activities) > 0) {
                // On recommence si toutes les activités ne sont pas remplies
                $nbActivities = count($activities);
                $nbActivitiesPlus = $nbActivities + 2; // équivalent à + 2 car les array commencent à 0

                // ### Pour le nombre d'activities + 1 ou 2 récupération des membres dont le ratio de participation est le plus bas
                $members = $this->getForDivisionT2($skill);
                $membersPresents = $this->filterByPresence($members, $cinescenie, $membersSelected);

                $totalCineMembers = $this
                    ->em
                    ->getRepository('AppBundle:Member')
                    ->getTotalCineByMember($membersPresents, $date, $cinescenie->getDate())
                ;

                $totalCinePlayMembers = $this
                    ->em
                    ->getRepository('AppBundle:Member')
                    ->getTotalCinePlayByMember($membersPresents, $date, $cinescenie->getDate())
                ;

                $membersRatio = [];
                foreach ($totalCineMembers as $key => $totalCineMember) {
                    // Nombre de personnes pour la compétence du rôle
                    $numberMembersForSkill = count($skill->getMemberSkills());

                    $isFind = false;
                    foreach ($totalCinePlayMembers as $totalCinePlayMember) {
                        if ($totalCinePlayMember['id'] == $totalCineMember['id']) {
                            $totalCinePlay = $totalCinePlayMember['totalCinePlay'];
                            $isFind = true;
                            break;
                        }
                    }

                    if ($isFind) {
                        $ratio = round($totalCinePlay / $totalCineMember['totalCine'] * 100, 0);
                    } else {
                        $ratio = 0;
                    }

                    $membersRatio[$key]['id']      = $totalCineMember['id'];
                    $membersRatio[$key]['counter'] = $ratio;
                }

                // Tri par ordre croissant du ratio
                $counter = [];
                foreach ($membersRatio as $key => $row) {
                    $id[$key]      = $row['id'];
                    $counter[$key] = $row['counter'];
                }
                array_multisort($counter, SORT_ASC, $membersRatio);

                // Réduction du nombre de membres
                $membersPresents = array_slice($membersRatio, 0, $nbActivitiesPlus);
                $members = [];
                foreach ($membersPresents as $memberPresent) {
                    $members[] = $memberPresent['id'];
                }

                // ### Etape Y : Pour chaque membre et pour chaque activity calcul du ratio de répartition homogène
                while ($members != null && $activities != null && count($members) > 0 && count($activities) > 0) {
                    $result = $this->getMemberAndActivityWithRatioRepartitionHomogeneMin($members, $activities, $skill, $pastCinescenies);

                    if ($result['memberSelected'] != null && $result['activitySelected'] != null) {
                        // ### Affectation de l'activity au membre qui a le ratio de répartition homogène le plus bas
                        $membersSelected[] = $result['memberSelected'];
                        $this->setActivityForMember($result['memberSelected'], $result['activitySelected'], $cinescenie);

                        // ### Nettoyage des listes en enlevant le membre et l'activity
                        $keyMember = array_search($result['memberSelected'], $members);
                        unset($members[$keyMember]);

                        $keyActivity = array_search($result['activitySelected'], $activities);
                        unset($activities[$keyActivity]);
                    } else {
                        $members = null;
                        //$activities = null;
                    }
                }
            }
            */
        }
    }

    private function getMemberAndActivityWithRatioRepartitionHomogeneMin($members, $activities, $numberMembersForSkill, $numberMaxCinescenies, $numberActivitiesSaison, $numberActivitiesByMember, $maxActivity)
    {
        // Nombre de personnes pour la compétence du rôle
        /*$numberMembersForSkill = count($skill->getMemberSkills());

        // Nombre de séances max
        $cinescenies = $this
            ->em
            ->getRepository('AppBundle:Cinescenie')
            ->findBy(['isTraining' => 0])
        ;
        $numberMaxCinescenies = count($cinescenies);
*/
        /*
        // Numéro de cinéscénie en cours
        $numberCurrentCinescenies = count($pastCinescenies);

        // Nombre de personnes pour les rôles du groupe
        $numberMembersForActivities = $numberMembersForSkill * count($activities);

        // Calcul du nombre de fois max par personne il faudrait faire ce rôle
        $numberMaxToDoActivity = $numberMaxCinescenies / $numberMembersForActivities;

        // Nombre de fois max que le membre peut faire tous les rôles de la compétence
        $maxForActivities = ceil($numberMaxCinescenies / $numberMembersForSkill);

        // Nombre de fois max que le membre peut faire un des les rôles de la compétences
        $maxForActivity = ceil($numberMaxCinescenies / $numberMembersForActivities);
        */
        // ----

        // Nombre de rôles disponibles dans la saison
        /*$numberActivitiesSaison = $numberMaxCinescenies * count($activities);

        // Nombre de rôles max par membre dans la saison
        $numberActivitiesByMember = $numberActivitiesSaison / $numberMembersForSkill;

        // Nombre max de fois qu'un rôle peut être attribué
        $maxActivity = $numberActivitiesByMember / count($activities);
*/
        $quantityMin = 1000;
        $result = [];
        $result['memberSelected'] = null;
        $result['activitySelected'] = null;
//$listMembersDoActivity = '';
        foreach ($members as $member) {
            /*
            // Nombre de présences du membre durant la saison
            $schedules = $this
                ->em
                ->getRepository('AppBundle:Schedule')
                ->findBy(['member' => $member, 'isTraining' => 0])
            ;
            $numberPresences = count($schedules);

            // Calcul de la période à laquelle le membre devrait faire le rôle
            $period = $numberPresences / $numberMaxToDoActivity;

            // Nombre de fois à laquelle le membre aurait du faire le rôle à l'instant de la cinéscénie
            $numberMemberShouldPlayActivity = $numberCurrentCinescenies / $period;
            */
            // Nombre de fois à laquelle le membre à déjà fait les rôles
            $schedulesActivity = $this
                ->em
                ->getRepository('AppBundle:Schedule')
                ->getForMemberAndActivities($member, $activities)
            ;
            $numberMemberDoActivity = count($schedulesActivity);
/*$listActivities = '';
foreach ($activities as $act) {
    $listActivities .= $act->getName().', ';
}
var_dump('#### '.$listActivities);*/

//$listMembersDoActivity .= 'Membre : '.$member.', numberMemberDoActivity: '.$numberMemberDoActivity.', quantityMin: '.$quantityMin.', ';

            // Calcul du ratio entre numberMemberDoActivity et numberMemberShouldPlayActivity
            /*$ratio = 0;
            if ($numberMemberDoActivity > 0 && $numberMemberShouldPlayActivity > 0) {
                $ratio = $numberMemberDoActivity / $numberMemberShouldPlayActivity;
            }*/

            // On garde le plus petit ratio de tous avec l'activity et le member
            //if ($ratio < $ratioMin) {
            if ($numberMemberDoActivity < $quantityMin) {
                //$ratioMin = $ratio;
                $quantityMin = $numberMemberDoActivity;
                $result['memberSelected'] = $member;
            }
        }
/*var_dump('getMemberAndActivity : '.$listMembersDoActivity);
var_dump('Membre retenu : '.$result['memberSelected']);*/

        if ($result['memberSelected'] != null) {
            $activitySort = [];
//$listActivities = '';
            foreach ($activities as $key => $activity) {
                // Nombre de fois à laquelle le membre à déjà fait le rôle
                /*$schedulesActivity = $this
                    ->em
                    ->getRepository('AppBundle:Schedule')
                    ->findBy(['member' => $result['memberSelected'], 'activity' => $activity])
                ;*/
                $schedulesActivity = $this
                    ->em
                    ->getRepository('AppBundle:Schedule')
                    ->getForMemberAndActivities($result['memberSelected'], $activity)
                ;
//$listActivities .= 'Rôle : '.$activity->getName().', count($schedulesActivity): '.count($schedulesActivity).', $maxActivity: '.$maxActivity.' # ';

                if (count($schedulesActivity) < $maxActivity) {
                    $activitySort[$key]['id'] = $activity;
                    $activitySort[$key]['counter'] = count($schedulesActivity);
                }
            }
//var_dump('Liste des rôels : '.$listActivities);

            // Tri par ordre croissant du nombre de fois le rôle déjà fait
            $counter = [];
            foreach ($activitySort as $key => $row) {
                $id[$key]      = $row['id'];
                $counter[$key] = $row['counter'];
            }
            array_multisort($counter, SORT_ASC, $activitySort);

            // Rôle assigné
            if (count($activitySort) > 0) {
                $result['activitySelected'] = $activitySort[0]['id'];
//var_dump('Rôle sélectionné : '.$result['activitySelected']->getName());
            } else {
                foreach ($activities as $act) {
                    $result['activitySelected'] = $act;
                    break;
                }
                
            }
        }

        return $result; 
    }

    private function sortGroupActivities($groupActivities, $cinescenie)
    {
        foreach ($groupActivities as $key => $groupActivity) {
            $activities = $groupActivity->getActivities();
            $firstActivity = $activities[0];
            if (!$firstActivity->getAllowForDivision()) {
                continue;
            }

            $skillActivities = $firstActivity->getSkillActivities();
            $skill = $skillActivities[0]->getSkill();
            $members = $this->getForDivisionT2([$skill]);
            $membersPresents = $this->filterByPresence($members, $cinescenie, []);

            $groupActivitiesSorted[$key]['id'] = $groupActivity;
            $groupActivitiesSorted[$key]['counter'] = count($membersPresents);
        } 

        // Tri par ordre croissant du nombre de personnes présente pour cette compétence
        $counter = [];
        foreach ($groupActivitiesSorted as $key => $row) {
            $id[$key]      = $row['id'];
            $counter[$key] = $row['counter'];
        }
        array_multisort($counter, SORT_ASC, $groupActivitiesSorted);

        return $groupActivitiesSorted;
    }

    private function sortSkills($cinescenie)
    {
        $skills = $this
            ->em
            ->getRepository('AppBundle:Skill')
            ->findAll()
        ;

        $skillsSorted = [];
        foreach ($skills as $key => $skill) {
            $members = $this->getForDivisionT2([$skill]);
            $membersPresents = $this->filterByPresence($members, $cinescenie, []);

            $skillsSorted[$key]['id'] = $skill;
            $skillsSorted[$key]['counter'] = count($membersPresents);
        }

        // Tri par ordre croissant
        $counter = [];
        foreach ($skillsSorted as $key => $row) {
            $id[$key]      = $row['id'];
            $counter[$key] = $row['counter'];
        }
        array_multisort($counter, SORT_ASC, $skillsSorted);

        return $skillsSorted;
    }

    // Cette fonction renvoie les membres qui ne sont pas déjà choisi dans le planning et qui sont présent le jour de la cinéscénie
    private function filterByPresence($members, $cinescenie, $membersSelected)
    {
        $schedules = $this->em
            ->getRepository('AppBundle:Schedule')
            ->findBy([
                'member'     => $members,
                'cinescenie' => $cinescenie,
            ])
        ;

        $membersResult = [];
        foreach ($schedules as $schedule) {
            $memberId = $schedule->getMember()->getId();
            if (!in_array($memberId, $membersSelected)) {
                $membersResult[] = $memberId;
            }
        }

        return $membersResult;
    }

    // Cette fonction renvoie les membres qui n'ont pas fait le même rôle la dernière fois qu'ils étaient présents
    private function filterByDifferentLastActivity($members, $cinescenie, $date, $activity)
    {
        $membersSort         = [];
        $membersSortGroup    = [];
        $membersSortActivity = [];
        $groupActivities     = $activity->getGroupActivities();
        foreach ($members as $member) {
            $schedules = $this
                ->em
                ->getRepository('AppBundle:Schedule')
                ->getLastActivity($member, $date, $cinescenie->getDate())
            ;

            if (empty($schedules)) {
                $membersSortGroup[]    = $member;
                $membersSortActivity[] = $member;
            } else {
                $lastActivity = $schedules[0]->getActivity();
                $lastGroupActivities = $lastActivity->getGroupActivities();
                  
                if ($lastGroupActivities->getId() != $groupActivities->getId()) {
                    $membersSortGroup[] = $member;
                }

                if ($lastActivity->getId() != $activity->getId()) {
                    $membersSortActivity[] = $member;
                }
            }
        }

        $membersSort = $membersSortGroup;

        if (empty($membersSortGroup)) {
            $membersSort = $membersSortActivity;
        }

        return $membersSort;
    }

    // Cette fonction renvoie les membres qui n'ont pas fait le même rôle depuis un temps conséquent par rapport au nombre de ptésences
    private function filterByWeightProcess($members, $cinescenie, $date, $activity, $byPass)
    {
        // Taux du poids
        if ($byPass) {
            $toDoActivityWeight = 10;
            $everyActivityWeight = 90;
            $ratioWeight = 0;
        } else {
            $toDoActivityWeight = 10;
            $everyActivityWeight = 30;
            $ratioWeight = 60; 
        }

        // ------------------ Répartition homogène dans la saison

        // Nombre de personnes pour la compétence du rôle
        $numberMembersForSkill = $this->getNumberMembersForSkill($activity);

        // Nombre de personnes pour les rôles du groupe
        $groupActivities = $activity->getGroupActivities();
        $activities = $groupActivities->getActivities();
        $numberMembersForActivities = $numberMembersForSkill * count($activities);

        // Nombre de séances max
        $cinescenies = $this
            ->em
            ->getRepository('AppBundle:Cinescenie')
            ->findBy(['isTraining' => 0])
        ;
        $numberMaxCinescenies = count($cinescenies);

        // Calcul du nombre de fois max par personne il faudrait faire ce rôle
        $numberMaxToDoActivity = $numberMaxCinescenies / $numberMembersForActivities;

        // Numéro de cinéscénie en cours
        $pastCinescenies = $this
            ->em
            ->getRepository('AppBundle:Cinescenie')
            ->getCinesceniesBetween($this->serviceDate->getSeasonDate(), $cinescenie->getDate())
        ;
        $numberCurrentCinescenies = count($pastCinescenies);


        $membersSort = [];
        foreach ($members as $key => $member) {
            // Nombre de présences du membre durant la saison
            $schedules = $this
                ->em
                ->getRepository('AppBundle:Schedule')
                ->findBy(['member' => $member, 'isTraining' => 0])
            ;
            $numberPresences = count($schedules);

            // Calcul de la période à laquelle le membre devrait faire le rôle
            $period = $numberPresences / $numberMaxToDoActivity;

            // Nombre de fois à laquelle le membre aurait du faire le rôle à l'instant de la cinéscénie
            $numberMemberShouldPlayActivity = $numberCurrentCinescenies / $period;

            // Nombre de fois à laquelle le membre à déjà fait les rôles du groupe de rôle
            $schedulesActivity = $this
                ->em
                ->getRepository('AppBundle:Schedule')
                ->getForMemberAndActivities($member, $activities)
            ;
            $numberMemberDoActivity = count($schedulesActivity);

            // Calcul du ratio entre numberMemberDoActivity et numberMemberShouldPlayActivity
            $ratio = 0;
            if ($numberMemberDoActivity > 0 && $numberMemberShouldPlayActivity > 0) {
                $ratio = $numberMemberDoActivity / $numberMemberShouldPlayActivity;
            }

            $membersSort[$key]['id'] = $member;
            $membersSort[$key]['counter'] = $ratio;
        }

        // Tri par ordre croissant du ratio
        $counter = [];
        foreach ($membersSort as $key => $row) {
            $id[$key]      = $row['id'];
            $counter[$key] = $row['counter'];
        }
        array_multisort($counter, SORT_ASC, $membersSort);

        // Nombre de membres
        $numberMembers = count($members);

        if ($numberMembers > 1) {
            $numberMembers = $numberMembers - 1;
        }

        $weightResult = [];
        $i = 0;
        foreach ($membersSort as $key => $memberSort) {
            $weightResult[$key]['id'] = $memberSort['id'];
            $weightResult[$key]['counter'] = ($toDoActivityWeight / $numberMembers) * $i;
            $i++;
        }

        // ------------------ Nombre de fois fait le rôle

        $membersSort = [];
        foreach ($members as $key => $member) {
            // Nombre de fois à laquelle le membre à déjà fait le rôle
            $schedulesActivity = $this
                ->em
                ->getRepository('AppBundle:Schedule')
                ->findBy(['member' => $member, 'activity' => $activity])
            ;

            $membersSort[$key]['id'] = $member;
            $membersSort[$key]['counter'] = count($schedulesActivity);
        }

        // Tri par ordre croissant du nombre de fois le rôle déjà fait
        $counter = [];
        foreach ($membersSort as $key => $row) {
            $id[$key]      = $row['id'];
            $counter[$key] = $row['counter'];
        }
        array_multisort($counter, SORT_ASC, $membersSort);

        $activityWeightResult = [];
        $i = 0;
        foreach ($membersSort as $key => $memberSort) {
            $activityWeightResult[$key]['id'] = $memberSort['id'];
            $activityWeightResult[$key]['counter'] = ($everyActivityWeight / $numberMembers) * $i;
            $i++;

            foreach ($weightResult as $wResult) {
                if ($wResult['id'] == $memberSort['id']) {
                    $activityWeightResult[$key]['counter'] = $activityWeightResult[$key]['counter'] + $wResult['counter'];
                    break;
                }
            }
        }

        // ------------------ Ratio
        $ratioWeightResult = [];
        if (!$byPass) {
            $totalCineMembers = $this
                ->em
                ->getRepository('AppBundle:Member')
                ->getTotalCineByMember($members, $date, $cinescenie->getDate())
            ;

            $totalCinePlayMembers = $this
                ->em
                ->getRepository('AppBundle:Member')
                ->getTotalCinePlayByMember($members, $date, $cinescenie->getDate())
            ;

            $membersResult = [];
            foreach ($totalCineMembers as $key => $totalCineMember) {
                $isFind = false;
                foreach ($totalCinePlayMembers as $totalCinePlayMember) {
                    if ($totalCinePlayMember['id'] == $totalCineMember['id']) {
                        $totalCinePlay = $totalCinePlayMember['totalCinePlay'];
                        $isFind = true;
                        break;
                    }
                }

                if ($isFind) {
                    $ratio = round($totalCinePlay / $totalCineMember['totalCine'] * 100, 0);
                } else {
                    $ratio = 0;
                }

                $membersResult[$key]['id']      = $totalCineMember['id'];
                $membersResult[$key]['counter'] = $ratio;
            }

             // Tri par ordre croissant du ratio
            $counter = [];
            foreach ($membersResult as $key => $row) {
                $id[$key]      = $row['id'];
                $counter[$key] = $row['counter'];
            }
            array_multisort($counter, SORT_ASC, $membersResult);

            $i = 0;
            foreach ($membersResult as $key => $mResult) {
                $ratioWeightResult[$key]['id'] = $mResult['id'];
                $ratioWeightResult[$key]['counter'] = ($ratioWeight / $numberMembers) * $i;
                $i++;

                foreach ($activityWeightResult as $actWeightResult) {
                    if ($actWeightResult['id'] == $mResult['id']) {
                        $ratioWeightResult[$key]['counter'] = $ratioWeightResult[$key]['counter'] + $actWeightResult['counter'];
                        break;
                    }
                }
            }
        }

        if (empty($ratioWeightResult)) {
            $ratioWeightResult = $activityWeightResult;
        }

        // ------------------ Fin

        if (!empty($ratioWeightResult)) {
            return $this->isolateFirstMembers($ratioWeightResult);
        } else {
            return [];
        }
    }

    private function getNumberMembersForSkill($activity)
    {
        $skillActivity = $this
            ->em
            ->getRepository('AppBundle:SkillActivity')
            ->findOneBy(['activity' => $activity])
        ;

        $memberSkills = $this
            ->em
            ->getRepository('AppBundle:MemberSkill')
            ->findBy(['skill' => $skillActivity->getSkill()])
        ;

        return count($memberSkills);
    }

    // Cette fonction ordonne les membres par le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées du plus petit au plus grand
    private function orderByRatio($members, $cinescenie, $date)
    {
        $totalCineMembers = $this
            ->em
            ->getRepository('AppBundle:Member')
            ->getTotalCineByMember($members, $date, $cinescenie->getDate())
        ;

        $totalCinePlayMembers = $this
            ->em
            ->getRepository('AppBundle:Member')
            ->getTotalCinePlayByMember($members, $date, $cinescenie->getDate())
        ;

        $membersResult = [];
        foreach ($totalCineMembers as $key => $totalCineMember) {
            $isFind = false;
            foreach ($totalCinePlayMembers as $totalCinePlayMember) {
                if ($totalCinePlayMember['id'] == $totalCineMember['id']) {
                    $totalCinePlay = $totalCinePlayMember['totalCinePlay'];
                    $isFind = true;
                    break;
                }
            }

            if ($isFind) {
                $ratio = round($totalCinePlay / $totalCineMember['totalCine'] * 100, 0);
            } else {
                $ratio = 0;
            }

            $membersResult[$key]['id']      = $totalCineMember['id'];
            $membersResult[$key]['counter'] = $ratio;
        }

        if (!empty($membersResult)) {
            $members = $this->isolateFirstMembers($membersResult);
        }

        return $members;
    }

    private function orderByGroupActivities($members, $activity, $pastCinescenies)
    {
        $groupActivities = $activity->getGroupActivities();
        $activities      = $groupActivities->getActivities();

        $members = $this
            ->em
            ->getRepository('AppBundle:Member')
            ->getOrderByGroupActivities($members, $activities, $pastCinescenies)
        ;

        $members = $this->isolateFirstMembers($members);

        return $members;
    }

    private function orderByActivity($members, $activity, $pastCinescenies)
    {
        $members = $this
            ->em
            ->getRepository('AppBundle:Member')
            ->getOrderByActivity($members, $activity, $pastCinescenies)
        ;

        $members = $this->isolateFirstMembers($members);

        return $members;
    }

    // Cette fonction permet d'isoler les premiers membres 
    private function isolateFirstMembers($members)
    {
        foreach ($members as $key => $row) {
            $id[$key]      = $row['id'];
            $counter[$key] = $row['counter'];
        }

        array_multisort($counter, SORT_ASC, $members);

        $counter       = $members[0]['counter'];
        $membersResult = [];
        foreach ($members as $member) {
            if ($member['counter'] > $counter) {
                break;
            }
            $membersResult[] = $member['id'];
        }

        return $membersResult;
    }

    // Cette fonction renvoie les membres qui n'ont pas fait la même spécialité la dernière fois qu'ils étaient présents
    public function filterByDifferentLastSpecialty($members, $cinescenie, $date, $specialty)
    {
        $membersSort = [];
        foreach ($members as $member) {
            $schedules = $this->em
                ->getRepository('AppBundle:Schedule')
                ->getLastSpecialty($member, $date, $cinescenie->getDate())
            ;

            $lastSpecialty = null;
            if (!empty($schedules)) {
                $lastSpecialty = $schedules[0]->getSpecialty();
                if (is_null($lastSpecialty)) {
                    $membersSort[] = $member;
                } else {
                    if ($lastSpecialty->getId() != $specialty->getId()) {
                        $membersSort[] = $member;
                    }
                }
            } else {
                $membersSort[] = $member;
            }
        }

        return $membersSort;
    }

    public function setActivityForMember($member, $activity, $cinescenie)
    {

        $schedule = $this->em
          ->getRepository('AppBundle:Schedule')
          ->findOneBy([
              'member'     => $member,
              'cinescenie' => $cinescenie,
          ])
        ;

        $schedule->setActivity($activity);
        $this->em->persist($schedule);
    }

    // T2
    /*
        Membres avec la compétence demandée
    */
    public function getForDivisionT2($skills)
    {
        $members = $this->em
            ->getRepository('AppBundle:Member')
            ->getForDivisionT2($skills)
        ;

        $resultMembers = [];
        foreach ($members as $member) {
            $resultMembers[] = $member['id'];
        }

        return $resultMembers;
    }

    public function setActivityAndSpecialtyForMember($member, $activity, $specialty, $cinescenie)
    {
        $schedule = $this->em
          ->getRepository('AppBundle:Schedule')
          ->findOneBy([
              'member'     => $member,
              'cinescenie' => $cinescenie,
          ])
        ;

        $schedule->setActivity($activity);
        $schedule->setSpecialty($specialty);
        $this->em->persist($schedule);
    }

    // Cette fonction renvoie le dernier groupe d'activité d'un membre
    public function getLastGroupActivities($member, $cinescenie, $date)
    {
        $lastGroupActivities = null;
        $schedules = $this->em
            ->getRepository('AppBundle:Schedule')
            ->getLastActivity($member, $date, $cinescenie->getDate())
        ;

        $lastActivity = null;
        if (!empty($schedules)) {
            $lastActivity = $schedules[0]->getActivity();
            if (!is_null($lastActivity)) {
                $lastGroupActivities = $lastActivity->getGroupActivities();
            }
        }

        return $lastGroupActivities;
    }

    // Cette fonction renvoie le rôle d'un membre en fonction d'une spécialité et d'un groupe de rôle à éviter
    public function getActivityBySpecialityAndLastGroupActivities($member, $specialty, $lastGroupActivities, $activitiesComplete)
    {
        $member = $this
            ->em
            ->getRepository('AppBundle:Member')
            ->find($member)
        ;

        // Liste des rôles autorisés pour le membre
        $mainSkill    = $member->getMainSkill();
        $memberSkills = $member->getMemberSkills();
        $skills       = [];
        foreach ($memberSkills as $memberSkill) {
            $skills[] = $memberSkill->getSkill();
        }

        $activitiesAllowed = [];
        foreach ($skills as $skill) {
            // Réduction de la liste des rôles autorisés si le membre à une compétence principale
            //if ((!is_null($mainSkill) && $mainSkill->getId() == $skill->getId()) || is_null($mainSkill)) {
                $skillActivities = $skill->getSkillActivities();

                foreach ($skillActivities as $skillActivity) {
                    $activitiesAllowed[] = $skillActivity->getActivity()->getId();
                }
            //}
        }

        // Liste des rôles possibles en comparant avec ceux autorisés par la spécialité
        $specialtyActivities = $specialty->getSpecialtyActivities();

        $activitiesAvailable   = [];
        $activitiesAvailableId = [];
        foreach ($specialtyActivities as $specialtyActivity) {
            $activity = $specialtyActivity->getActivity();

            if (in_array($activity->getId(), $activitiesAllowed)) {
                $activitiesAvailable[]   = $activity;
                $activitiesAvailableId[] = $activity->getId();
            }
        }

        if (empty($activitiesAvailable)) {
            return null;
        }

        // Tentative de ne garder que des rôles dont le groupe n'a pas été fait la dernière fois
        // s'il n'y a plus de rôles alors on garder ceux déjà fait
        $activitiesResult = [];
        if (!is_null($lastGroupActivities)) {
            foreach ($activitiesAvailable as $activityAvailable) {
                $groupActivities = $activityAvailable->getGroupActivities();
                if ($lastGroupActivities->getId() != $groupActivities->getId()) {
                    $activitiesResult[]   = $activityAvailable;
                    $activitiesResultId[] = $activityAvailable->getId();
                }
            }
        }

        if (empty($activitiesResult)) {
            $activitiesResult   = $activitiesAvailable;
            $activitiesResultId = $activitiesAvailableId;
        }

        // Récupération des groupes de rôles
        $groupActivitiesAllowed = [];
        foreach ($activitiesResult as $activityResult) {
            $groupActivitiesId = $activityResult->getGroupActivities()->getId();
            if (!in_array($groupActivitiesId, $groupActivitiesAllowed)) {
                $groupActivitiesAllowed[] = $groupActivitiesId;
            }
        }

        // Chercher le groupe de rôle le moins fait dans ceux possibles
        $groupActivities = $this
            ->em
            ->getRepository('AppBundle:GroupActivities')
            ->getOrderByNumberOfTimesForGroup($groupActivitiesAllowed, $member)
        ;
        $groupActivities = $groupActivities[0][0];

        // Ne garder que les rôles de ce groupe
        $activitiesInGroup = [];
        $activities        = $groupActivities->getActivities();
        foreach ($activities as $activity) {
            if (in_array($activity->getId(), $activitiesResultId)) {
                $activitiesInGroup[] = $activity->getId();
            }
        }

        // Dans ce groupe de rôle chercher le rôle le moins fait
        $activities = $this
            ->em
            ->getRepository('AppBundle:Activity')
            ->getOrderByNumberOfTimesForMemberAndActivities($member, $activitiesInGroup)
        ;

        return $activities[0][0];

        // ------
        /*
        $specialtyActivities = $specialty->getSpecialtyActivities();

        $speActivitiesLast      = [];
        $speGroupActivitiesLast = [];
        $speActivities          = [];
        foreach ($specialtyActivities as $specialtyActivity) {
            $activity        = $specialtyActivity->getActivity();
            $groupActivities = $activity->getGroupActivities();
            $speActivities[] = $activity->getId();

            if ((!is_null($lastGroupActivities) && $lastGroupActivities->getId() != $groupActivities->getId()) || is_null($lastGroupActivities)) {
                $speActivitiesLast[] = $activity->getId();

                if (!in_array($groupActivities->getId(), $speGroupActivitiesLast)) {
                    $speGroupActivitiesLast[] = $groupActivities->getId();
                }
            }
        }

        $member = $this
            ->em
            ->getRepository('AppBundle:Member')
            ->find($member)
        ;

        $groupActivities = $this
            ->em
            ->getRepository('AppBundle:GroupActivities')
            ->getOrderByNumberOfTimesForMember($member)
        ;

        $resultGroupActivities = null;
        foreach ($groupActivities as $groupAct) {
            if (empty($speGroupActivitiesLast) || in_array($groupAct[0]->getId(), $speGroupActivitiesLast)) {
                $resultGroupActivities = $groupAct[0];
                break;
            }
        }

        if (is_null($resultGroupActivities)) {
            $resultGroupActivities = $lastGroupActivities;
        }

        $activities = $this
            ->em
            ->getRepository('AppBundle:Activity')
            ->getOrderByNumberOfTimesForMemberAndGroupActivities($member, $resultGroupActivities)
        ;

        $resultActivity = null;
        foreach ($activities as $activity) {
            if (!in_array($activity[0]->getId(), $activitiesComplete) && ((empty($speActivitiesLast) && in_array($activity[0]->getId(), $speActivities)) || (in_array($activity[0]->getId(), $speActivitiesLast)))) {
                $resultActivity = $activity[0];
                break;
            }
        }

        return $resultActivity;
        */
    }

    // Choix des spécialistes
    /*
        Membres avec la spécialité demandée
    */
    public function getForDivisionSpecialty($specialty)
    {
        $members = $this
            ->em
            ->getRepository('AppBundle:Member')
            ->getForDivisionSpecialty($specialty)
        ;

        $resultMembers = [];
        foreach ($members as $member) {
            $resultMembers[] = $member['id'];
        }

        return $resultMembers;
    }

    /*
        Membres présents et qui ne sont pas déjà sélectionnés
        Membres dont la dernière spécialité faite n'est pas celle demandée / Si pas de membres alors ce critère est ignoré
        Membres dont le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées est le plus petit
    */
    public function filterSpecialtyBy($members, $membersSelected, $cinescenie, $date, $specialty)
    {
        // Membres présents et qui ne sont pas déjà sélectionnés
        $presenceMembers = $this->filterByPresence($members, $cinescenie, $membersSelected);

        if (empty($presenceMembers)) {
            // Pas de membres disponible le rôle sera vide
            return null;
        } elseif (count($presenceMembers) == 1) {
            // Un seul membre disponible le rôle est pour lui
            return $presenceMembers[0];
        }

        // Membres dont la dernière spécialité faite n'est pas celle demandée / Si pas de membres alors ce critère est ignoré
        $diffLastSpeMembers = $this->filterByDifferentLastSpecialty($presenceMembers, $cinescenie, $date, $specialty);

        if (empty($diffLastSpeMembers)) {
            // Aucun membre n'a de spécialité différente depuis la dernière fois
            // Ce critère est donc mis de côté et la précédente liste est utilisée
            $diffLastSpeMembers = $presenceMembers;
        } elseif (count($diffLastSpeMembers) == 1) {
            // Un seul membre disponible le rôle est pour lui
            return $diffLastSpeMembers[0];
        }

        // Membres dont le ratio entre le nombre de séances où ils sont présents et le nombre de séances jouées est le plus petit
        $ratioMembers = $this->orderByRatio($diffLastSpeMembers, $cinescenie, $date); 

        if (count($ratioMembers) == 1) {
            // Un seul membre disponible le rôle est pour lui
            return $ratioMembers[0];
        }

        // Le premier membre est sélectionné
        return $ratioMembers[0];
    }
}