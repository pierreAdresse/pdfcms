<?php

namespace AppBundle\Repository;

class ActivityRepository extends \Doctrine\ORM\EntityRepository
{
    public function getSchedulesForMember($member)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT a.name, COUNT(s) AS numberOfTimes
            FROM AppBundle:Activity a
            LEFT JOIN a.schedules s WITH (s.member = :member AND s.activity IS NOT NULL)
            JOIN s.cinescenie c WITH (c.isTraining = 0)
            WHERE a.allowForDivision = 1
            GROUP BY a.id
            ORDER BY a.ranking ASC
            '
        )->setParameters([
            'member' => $member,
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }

    public function getOrderByNumberOfTimesForMemberAndGroupActivities($member, $groupActivities)
    {
        $whereClause = ' ';
        $parameters  = ['member' => $member];

        if (!is_null($groupActivities)) {
            $whereClause = ' AND a.groupActivities = :groupActivities ';
            $parameters['groupActivities']  = $groupActivities;
        }

        $sql =
            '
            SELECT a, COUNT(s) AS numberOfTimes
            FROM AppBundle:Activity a
            LEFT JOIN a.schedules s WITH (s.member = :member)
            WHERE a.allowForDivision = 1
            '
            .$whereClause.
            '
            GROUP BY a.id
            ORDER BY numberOfTimes ASC
            '
        ;

        $em = $this->getEntityManager();
        $query = $em
            ->createQuery($sql)
            ->setParameters($parameters)
        ;
        $activities = $query->getResult();

        return $activities;
    }
}
