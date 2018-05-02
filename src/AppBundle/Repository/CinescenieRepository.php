<?php

namespace AppBundle\Repository;

class CinescenieRepository extends \Doctrine\ORM\EntityRepository
{
    public function getByMemberAndDateGreaterThan($member, $date)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT c
            FROM AppBundle:Cinescenie c
            JOIN c.schedules s WITH (s.member = :member)
            WHERE c.date > :date
            ORDER BY c.date ASC
            '
        )->setParameters([
        	'member' => $member,
            'date'   => $date,
        ]);
        $cinescenies = $query->getResult();

        return $cinescenies;
    }

    public function getByDateGreaterThan($date)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT c
            FROM AppBundle:Cinescenie c
            WHERE c.date >= :date
            ORDER BY c.date ASC
            '
        )->setParameters([
            'date' => $date,
        ]);
        $cinescenies = $query->getResult();

        return $cinescenies;
    }

    public function getCinesceniesBetween($from, $to)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT c
            FROM AppBundle:Cinescenie c
            WHERE c.date > :from AND c.date < :to
            ORDER BY c.date ASC
            '
        )->setParameters([
            'from' => $from,
            'to'   => $to,
        ]);
        $cinescenies = $query->getResult();

        return $cinescenies;
    }

    public function countActAndSpe($cinescenies)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT c, COUNT(a.id) AS counterAct, COUNT(s.id) AS counterSpe
            FROM AppBundle:Cinescenie c
            JOIN c.schedules sc
            LEFT JOIN sc.activity a WITH (sc.activity IS NOT NULL)
            LEFT JOIN sc.specialty s WITH (sc.specialty IS NOT NULL)
            WHERE c IN (:cinescenies)
            GROUP BY c.id
            '
        )->setParameters([
            'cinescenies' => $cinescenies,
        ]);
        $cinescenies = $query->getResult();

        return $cinescenies; 
    }
}
