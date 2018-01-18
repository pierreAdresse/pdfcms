<?php

namespace AppBundle\Service;

use AppBundle\Service\Date;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Cinescenie;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\User as UserEnt;

class User
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

        $users = $this->em
            ->getRepository('AppBundle:User')
            ->getAndCountSchedules($date)
        ;

        return $users;
	}

	public function getForDivision(Cinescenie $cinescenie, $skills)
	{
        $date = $this->serviceDate->getSeasonDate();

        $users = $this->em
            ->getRepository('AppBundle:User')
            ->getForDivision($cinescenie, $skills, $date)
        ;

        return $users;
	}


    // T1 - Tentative 1
        /*
        Utilisateur avec la compétence demandée
        Utilisateur avec comme compétence principale la compétence demandée et le quota non atteint
        Utilisateur dont le dernier rôle fait n'est pas celui demandé
        */
	public function getForDivisionT1($pastCinescenies, $skills, $activity, $quota)
	{
        $users = $this->em
            ->getRepository('AppBundle:User')
            ->getForDivisionT1($pastCinescenies, $skills, $activity, $quota)
        ;

        return $users;
	}

    // T3 - Tentative 3
        /*
        Utilisateur avec la compétence demandée
        Utilisateur dont le dernier rôle fait n'est pas celui demandé
        */
    public function getForDivisionT3($pastCinescenies, $skills, $activity)
    {
        $users = $this->em
            ->getRepository('AppBundle:User')
            ->getForDivisionT3($pastCinescenies, $skills, $activity)
        ;

        return $users;
    }

    // Cette fonction renvoie les utilisateurs qui n'ont pas fait le même rôle la dernière fois qu'ils étaient présents
    public function filterByDifferentLastActivity($users, $cinescenie, $date, $activity)
    {
        $usersSort = [];
        foreach ($users as $user) {
            $schedules = $this->em
                ->getRepository('AppBundle:Schedule')
                ->getLastActivity($user, $date, $cinescenie->getDate())
            ;

            $lastActivity = null;
            if (!empty($schedules)) {
                $lastActivity = $schedules[0]->getActivity();
                if (is_null($lastActivity)) {
                    $usersSort[] = $user;
                } else {
                    $lastGroup       = $lastActivity->getGroupActivities();
                    $groupActivities = $activity->getGroupActivities();

                    if ($lastGroup->getId() != $groupActivities->getId()) {
                        $usersSort[] = $user;
                    }
                }
            } else {
                $usersSort[] = $user;
            }
        }

        return $usersSort;
    }

    // Cette fonction renvoie les utilisateurs qui ne sont pas déjà choisi dans le planning et qui sont présent le jour de la cinéscénie
    public function filterUserPresent($users, $cinescenie, $usersSelected)
    {
        $usersResult = [];
        foreach ($users as $user) {
            $user = $user[0];

            $isPresent = false;
            $schedule = $this->em
                ->getRepository('AppBundle:Schedule')
                ->findOneBy([
                    'user'       => $user,
                    'cinescenie' => $cinescenie,
                ])
            ;
            if ($schedule instanceof Schedule) {
                $isPresent = true;
            }

            if (!in_array($user->getId(), $usersSelected) && $isPresent) {
                $usersResult[] = $user;
            }
        }

        return $usersResult;
    }

    public function setActivityForUser($user, $activity, $cinescenie)
    {
        $schedule = $this->em
          ->getRepository('AppBundle:Schedule')
          ->findOneBy([
              'user'       => $user,
              'cinescenie' => $cinescenie,
          ])
        ;

        $schedule->setActivity($activity);
        $this->em->persist($schedule);
    }

    // Cette focntion permet d'effacer les rôles d'une cinéscénie et d'enlever les éventuels Laissez passer
    public function cleanSchedules($cinescenie)
    {
        // On commence par effacer tous les rôles
        $schedules = $this->em
            ->getRepository('AppBundle:Schedule')
            ->findBy([
              'cinescenie' => $cinescenie,
            ])
        ;

        foreach ($schedules as $schedule) {
            $user = $schedule->getUser();
            if (!is_null($user) && $user->getId() == UserEnt::LAISSEZ_PASSER) {
                $this->em->remove($schedule);
            } else {
                $schedule->setActivity(null);
                $this->em->persist($schedule);
            }
        }

        $this->em->flush();
    }
}