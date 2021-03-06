<?php

namespace AppBundle\Service;

use AppBundle\Service\Date;
use Doctrine\ORM\EntityManagerInterface;

class Cinescenie
{
	private $em;
    private $serviceDate;
    private $quota;

	public function __construct(EntityManagerInterface $em, Date $serviceDate)
	{
		$this->em          = $em;
        $this->serviceDate = $serviceDate;
        $this->quota       = 28;
	}

    /*
     * Cette fonction renvoie les Cinéscénies de la saison en cours.
     * Dès le mois d'octobre de l'année en cours alors elle renvoit les dates des Cinéscénies de la saison suivante.
     * Exemple : mois entre 1 et 9 alors 2017 (année en cours), mois entre 10 et 12 alors 2018 (année en cours +1).
     */
	public function getCurrents()
	{
        $date = $this->serviceDate->getSeasonDate();

        $cinescenies = $this->em
            ->getRepository('AppBundle:Cinescenie')
            ->getByDateGreaterThan($date)
        ;

        return $cinescenies;
	}

    public function getCurrentsWithoutTraining()
    {
        $date = $this->serviceDate->getSeasonDate();

        $cinescenies = $this->em
            ->getRepository('AppBundle:Cinescenie')
            ->getByDateGreaterThanWithoutTraining($date)
        ;

        return $cinescenies;
    }

    public function getCurrentsByMember($member)
    {
        $date = $this->serviceDate->getSeasonDate();

        $cinescenies = $this->em
            ->getRepository('AppBundle:Cinescenie')
            ->getByMemberAndDateGreaterThan($member, $date)
        ;

        return $cinescenies;
    }

    public function getFutures()
    {
        $cinescenies = $this->em
            ->getRepository('AppBundle:Cinescenie')
            ->getByDateGreaterThanWithoutTraining(new \Datetime('now'))
        ;

        return $cinescenies;
    }

    public function getCinesceniesBetween($from, $to)
    {
        $cinescenies = $this->em
            ->getRepository('AppBundle:Cinescenie')
            ->getCinesceniesBetween($from, $to)
        ;

        return $cinescenies;
    }

    public function getQuota()
    {
        return $this->quota;
    }
}