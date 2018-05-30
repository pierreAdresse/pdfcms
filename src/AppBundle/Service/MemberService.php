<?php
namespace AppBundle\Service;

use AppBundle\Service\Date;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Cinescenie;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Member;

class MemberService
{
	private $em;
	private $serviceDate;

	public function __construct(EntityManagerInterface $em, Date $serviceDate)
	{
		$this->em          = $em;
		$this->serviceDate = $serviceDate;
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
            $speActivitiesLast     = $speActivities;
        }

        $activities = $this
            ->em
            ->getRepository('AppBundle:Activity')
            ->getOrderByNumberOfTimesForMemberAndGroupActivities($member, $resultGroupActivities)
        ;

        $resultActivity = null;
        foreach ($activities as $activity) {
            if (!in_array($activity[0]->getId(), $activitiesComplete) && ((empty($speActivitiesLast) || in_array($activity[0]->getId(), $speActivitiesLast)))) {
                $resultActivity = $activity[0];
                break;
            }
        }

        return $resultActivity;
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