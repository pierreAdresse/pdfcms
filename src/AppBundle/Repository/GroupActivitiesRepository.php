<?php

namespace AppBundle\Repository;

class GroupActivitiesRepository extends \Doctrine\ORM\EntityRepository
{
    public function getOrderByNumberOfTimesForMember($member)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT ga, COUNT(s) AS numberOfTimes
            FROM AppBundle:GroupActivities ga
            JOIN ga.activities a
            LEFT JOIN a.schedules s WITH (s.member = :member)
            WHERE a.allowForDivision = 1
            GROUP BY ga.id
            ORDER BY numberOfTimes ASC
            '
        )->setParameters([
            'member'     => $member,
        ]);
        $groupActivities = $query->getResult();

        return $groupActivities;
    }

    public function getSchedulesForMember($member)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT ga.name, COUNT(s) AS numberOfTimes
            FROM AppBundle:GroupActivities ga
            JOIN ga.activities a
            LEFT JOIN a.schedules s WITH (s.member = :member AND s.activity IS NOT NULL)
            WHERE a.allowForDivision = 1
            GROUP BY ga.id
            '
        )->setParameters([
            'member' => $member,
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }
}
