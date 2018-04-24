<?php

namespace AppBundle\Repository;

class MemberRepository extends \Doctrine\ORM\EntityRepository
{
	    public function getAndCountSchedules($date)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m, COUNT(c.id) AS countCinescenies
            FROM AppBundle:Member m
            LEFT JOIN m.schedules sc
            LEFT JOIN sc.cinescenie c WITH c.date > :date
            WHERE m.deleted = 0
            GROUP BY m.id
            ORDER BY m.firstname ASC
            '
        )->setParameters([
            'date' => $date,
        ]);
        $members = $query->getResult();

        return $members;
    }

    public function getAndCountSchedulesForMember($date, $member)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT COUNT(c.id) AS countCinescenies
            FROM AppBundle:Member m
            LEFT JOIN m.schedules sc
            LEFT JOIN sc.cinescenie c WITH c.date > :date
            WHERE m = :member
            GROUP BY m.id
            '
        )->setParameters([
            'date'   => $date,
            'member' => $member,
        ]);
        $members = $query->getResult();

        return $members;
    }

    public function getForActivityWithSkill($cinescenie, $skill)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.cinescenie = :cinescenie and sc.activity is null)
            JOIN m.memberSkills ms WITH (ms.skill = :skill)
            WHERE m.deleted = 0
            ORDER BY m.firstname ASC
            '
        )->setParameters([
            'cinescenie' => $cinescenie,
            'skill'      => $skill,
        ]);
        $members = $query->getResult();

        return $members;
    }

    public function getForActivityWithoutSkill($cinescenie, $members)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.cinescenie = :cinescenie and sc.activity is null)
            WHERE m NOT IN (:members)
            AND m.deleted = 0
            ORDER BY m.firstname ASC
            '
        )->setParameters([
            'cinescenie' => $cinescenie,
            'members'    => $members,
        ]);
        $members = $query->getResult();

        return $members;
    }

    public function getForDivisionT1($pastCinescenies, $skills, $activities, $quota)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id, COUNT(sc.id) AS gaNumOfTim
            FROM AppBundle:Member m
            JOIN m.memberSkills ms WITH ms.skill IN (:skills)
            LEFT JOIN m.schedules sc WITH (sc.activity IN (:activities) AND sc.cinescenie IN (:pastCinescenies))
            WHERE m.mainSkill IN (:skills)
            AND m.deleted = 0
            GROUP BY m.id
            HAVING gaNumOfTim < :quota
            ORDER BY gaNumOfTim ASC
            '
        )->setParameters([
            'skills'          => $skills,
            'activities'      => $activities,
            'pastCinescenies' => $pastCinescenies,
            'quota'           => $quota,
        ]);
        $members = $query->getResult();

        return $members; 
    }

    public function getOrderByGroupActivities($members, $activities, $pastCinescenies)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id, COUNT(sc.id) AS counter
            FROM AppBundle:Member m
            LEFT JOIN m.schedules sc WITH (sc.activity IN (:activities) AND sc.cinescenie IN (:pastCinescenies))
            WHERE m.id IN (:members)
            GROUP BY m.id
            ORDER BY counter ASC
            '
        )->setParameters([
            'members'         => $members,
            'activities'      => $activities,
            'pastCinescenies' => $pastCinescenies,
        ]);
        $members = $query->getResult();

        return $members; 
    }

    public function getOrderByActiviy($members, $activity, $pastCinescenies)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id, COUNT(sc.id) AS counter
            FROM AppBundle:Member m
            LEFT JOIN m.schedules sc WITH (sc.activity = :activity AND sc.cinescenie IN (:pastCinescenies))
            WHERE m.id IN (:members)
            GROUP BY m.id
            ORDER BY counter ASC
            '
        )->setParameters([
            'members'         => $members,
            'activity'        => $activity,
            'pastCinescenies' => $pastCinescenies,
        ]);
        $members = $query->getResult();

        return $members; 
    }

    public function getTotalCineByMember($members, $from, $to) {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id, COUNT(sc.id) AS totalCine
            FROM AppBundle:Member m
            JOIN m.schedules sc
            JOIN sc.cinescenie c WITH (c.date > :from AND c.date < :to)
            WHERE m.id IN (:members)
            GROUP BY m.id
            ORDER BY totalCine ASC
            '
        )->setParameters([
            'members' => $members,
            'from'    => $from,
            'to'      => $to,
        ]);
        $members = $query->getResult();

        return $members;
    }

    public function getTotalCinePlayByMember($members, $from, $to) {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id, COUNT(sc.id) AS totalCinePlay
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.activity IS NOT NULL)
            JOIN sc.cinescenie c WITH (c.date > :from AND c.date < :to)
            WHERE m.id IN (:members)
            GROUP BY m.id
            ORDER BY totalCinePlay ASC
            '
        )->setParameters([
            'members' => $members,
            'from'    => $from,
            'to'      => $to,
        ]);
        $members = $query->getResult();

        return $members;
    }

    public function getOrderForDivision($pastCinescenies, $members, $activity)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m, COUNT(sc.id) AS actNumOfTim
            FROM AppBundle:Member m
            LEFT JOIN m.schedules sc WITH (sc.activity = :activity AND sc.cinescenie IN (:pastCinescenies))
            WHERE m.id IN (:members)
            GROUP BY m.id
            ORDER BY actNumOfTim ASC
            '
        )->setParameters([
            'members'         => $members,
            'activity'        => $activity,
            'pastCinescenies' => $pastCinescenies,
        ]);
        $members = $query->getResult();

        return $members; 
    }

    public function getOrder2ForDivision($pastCinescenies, $members)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m, COUNT(sc.id) AS numOfTim
            FROM AppBundle:Member m
            LEFT JOIN m.schedules sc WITH (sc.activity IS NOT NULL AND sc.cinescenie IN (:pastCinescenies))
            WHERE m.id IN (:members)
            GROUP BY m.id
            ORDER BY numOfTim ASC
            '
        )->setParameters([
            'members'         => $members,
            'pastCinescenies' => $pastCinescenies,
        ]);
        $members = $query->getResult();

        return $members; 
    }

    public function getForDivisionSpecialty($pastCinescenies, $specialty)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m, COUNT(sc.id) AS specialtyNumber
            FROM AppBundle:Member m
            LEFT JOIN m.schedules sc WITH (sc.specialty = :specialty AND sc.cinescenie IN (:pastCinescenies))
            JOIN m.memberSpecialties ms
            WHERE m.deleted = 0
            GROUP BY m.id
            ORDER BY specialtyNumber ASC
            '
        )->setParameters([
            'specialty'       => $specialty,
            'pastCinescenies' => $pastCinescenies,
        ]);
        $members = $query->getResult();

        return $members; 
    }

    public function getForDivisionT2($pastCinescenies, $skills, $activities)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id, COUNT(sc.id) AS gaNumOfTim
            FROM AppBundle:Member m
            JOIN m.memberSkills ms WITH ms.skill IN (:skills)
            LEFT JOIN m.schedules sc WITH (sc.activity IN (:activities) AND sc.cinescenie IN (:pastCinescenies))
            WHERE m.deleted = 0
            GROUP BY m.id
            ORDER BY gaNumOfTim ASC
            '
        )->setParameters([
            'skills'          => $skills,
            'activities'      => $activities,
            'pastCinescenies' => $pastCinescenies,
        ]);
        $members = $query->getResult();

        return $members; 
    }

    public function getWithSpecialty($cinescenie, $specialty)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.cinescenie = :cinescenie)
            JOIN m.memberSpecialties ms WITH (ms.specialty = :specialty)
            WHERE m.deleted = 0
            ORDER BY m.firstname ASC
            '
        )->setParameters([
            'cinescenie' => $cinescenie,
            'specialty'  => $specialty,
        ]);
        $members = $query->getResult();

        return $members;
    }

    public function getWithoutSpecialty($cinescenie, $members)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.cinescenie = :cinescenie)
            WHERE m NOT IN (:members)
            AND m.deleted = 0
            ORDER BY m.firstname ASC
            '
        )->setParameters([
            'cinescenie' => $cinescenie,
            'members'    => $members,
        ]);
        $members = $query->getResult();

        return $members;
    }
}
