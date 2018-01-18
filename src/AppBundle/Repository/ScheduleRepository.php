<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;

/**
 * ScheduleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ScheduleRepository extends \Doctrine\ORM\EntityRepository
{
    public function getLastActivity(User $user, $from, $to)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT s
            FROM AppBundle:Schedule s
            JOIN s.cinescenie c WITH (c.date > :from AND c.date < :to)
            WHERE s.user = :user
            ORDER BY c.date DESC
            '
        )->setParameters([
            'user' => $user,
            'from' => $from,
            'to'   => $to,
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }

    public function getWithoutActivity($cinescenies)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT s
            FROM AppBundle:Schedule s
            WHERE s.activity IS NULL
            AND s.cinescenie IN (:cinescenies)
            '
        )->setParameters([
            'cinescenies' => $cinescenies,
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }
}
