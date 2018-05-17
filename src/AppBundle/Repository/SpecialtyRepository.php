<?php

namespace AppBundle\Repository;

class SpecialtyRepository extends \Doctrine\ORM\EntityRepository
{
	public function getByMember($member)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT s
            FROM AppBundle:Specialty s
            JOIN s.memberSpecialties ms WITH (ms.member = :member)
            '
        )->setPArameters([
        	'member' => $member
        ]);
        $specialties = $query->getResult();

        return $specialties;
    }

    public function getSchedulesForMember($member)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT sp.name, COUNT(sc) AS numberOfTimes
            FROM AppBundle:Specialty sp
            LEFT JOIN sp.schedules sc WITH (sc.member = :member AND sc.specialty IS NOT NULL)
            JOIN sc.cinescenie c WITH (c.isTraining = 0)
            GROUP BY sp.id
            ORDER BY sp.ranking ASC
            '
        )->setParameters([
            'member' => $member,
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }
}
