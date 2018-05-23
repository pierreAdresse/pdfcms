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
            LEFT JOIN sc.cinescenie c WITH (c.date > :date AND c.isTraining = 0)
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

    public function getTotalCinePlay($date) {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id, COUNT(sc.id) AS totalCinePlay
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.activity IS NOT NULL)
            JOIN sc.cinescenie c WITH (c.date > :date AND c.isTraining = 0)
            WHERE m.deleted = 0
            GROUP BY m.id
            ORDER BY totalCinePlay ASC
            '
        )->setParameters([
            'date' => $date,
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
        $where  = '';
        $params = ['cinescenie' => $cinescenie];
        if (!empty($members)) {
            $where = 'AND m NOT IN (:members)';
            $params['members'] = $members;
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.cinescenie = :cinescenie and sc.activity is null)
            WHERE m.deleted = 0
            '.$where.'
            ORDER BY m.firstname ASC
            '
        )->setParameters($params);
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
            JOIN sc.cinescenie c WITH (c.date > :from AND c.date < :to AND c.isTraining = 0)
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
            JOIN sc.cinescenie c WITH (c.date > :from AND c.date < :to AND c.isTraining = 0)
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

    public function getForDivisionSpecialty($specialty)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id
            FROM AppBundle:Member m
            JOIN m.memberSpecialties ms WITH ms.specialty = :specialty
            WHERE m.deleted = 0
            GROUP BY m.id
            '
        )->setParameters([
            'specialty' => $specialty,
        ]);
        $members = $query->getResult();

        return $members; 
    }

    public function getForDivisionT2($skills)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m.id
            FROM AppBundle:Member m
            JOIN m.memberSkills ms WITH ms.skill IN (:skills)
            WHERE m.deleted = 0
            GROUP BY m.id
            '
        )->setParameters([
            'skills' => $skills,
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
            JOIN m.schedules sc WITH (sc.cinescenie = :cinescenie and sc.specialty is null)
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

    public function getSelected($cinescenie, $activity)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.cinescenie = :cinescenie AND sc.activity = :activity)
            ORDER BY m.firstname ASC
            '
        )->setParameters([
            'cinescenie' => $cinescenie,
            'activity'   => $activity,
        ]);
        $members = $query->getResult();

        return $members;
    }

    public function getSelectedSpecialty($cinescenie, $specialty)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT m
            FROM AppBundle:Member m
            JOIN m.schedules sc WITH (sc.cinescenie = :cinescenie AND sc.specialty = :specialty)
            ORDER BY m.firstname ASC
            '
        )->setParameters([
            'cinescenie' => $cinescenie,
            'specialty'  => $specialty,
        ]);
        $members = $query->getResult();

        return $members;
    }
}
