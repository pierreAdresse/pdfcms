<?php

namespace AppBundle\Repository;

class ScheduleRepository extends \Doctrine\ORM\EntityRepository
{
    public function getLastActivity($member, $from, $to)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT s
            FROM AppBundle:Schedule s
            JOIN s.cinescenie c WITH (c.date > :from AND c.date < :to)
            WHERE s.member = :member
            AND s.activity IS NOT NULL
            ORDER BY c.date DESC
            '
        )->setParameters([
            'member'  => $member,
            'from'    => $from,
            'to'      => $to,
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }

    public function getLastPresence($member, $date)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT s
            FROM AppBundle:Schedule s
            JOIN s.cinescenie c WITH (c.date < :date)
            WHERE s.member = :member
            ORDER BY c.date DESC
            '
        )->setParameters([
            'member'  => $member,
            'date'    => $date
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }

    public function getLastSpecialty($member, $from, $to)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT s
            FROM AppBundle:Schedule s
            JOIN s.cinescenie c WITH (c.date > :from AND c.date < :to)
            WHERE s.member = :member
            ORDER BY c.date DESC
            '
        )->setParameters([
            'member' => $member,
            'from'   => $from,
            'to'     => $to,
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

    public function getForMemberAndActivities($member, $activities)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT s
            FROM AppBundle:Schedule s
            WHERE s.activity IN (:activities)
            AND s.member = :member
            '
        )->setParameters([
            'member'      => $member,
            'activities' => $activities
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }
}
