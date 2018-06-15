<?php

namespace AppBundle\Repository;

class ActivityRepository extends \Doctrine\ORM\EntityRepository
{
    public function getSchedulesForMember($member)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT a.id, a.name, COUNT(s) AS numberOfTimes
            FROM AppBundle:Activity a
            LEFT JOIN a.schedules s WITH (s.member = :member AND s.activity IS NOT NULL)
            JOIN s.cinescenie c WITH (c.isTraining = 0)
            WHERE a.allowForDivision = 1
            GROUP BY a.id
            ORDER BY a.orderDisplay ASC
            '
        )->setParameters([
            'member' => $member,
        ]);
        $schedules = $query->getResult();

        return $schedules;
    }

    public function getOrderByNumberOfTimesForMemberAndGroupActivities($member, $groupActivities)
    {
        $sql =
            '
            SELECT a, COUNT(s) AS numberOfTimes
            FROM AppBundle:Activity a
            LEFT JOIN a.schedules s WITH (s.member = :member AND s.isTraining = 0)
            WHERE a.allowForDivision = 1
            AND a.groupActivities = :groupActivities
            GROUP BY a.id
            ORDER BY numberOfTimes ASC, a.ranking ASC
            '
        ;

        $em = $this->getEntityManager();
        $query = $em
            ->createQuery($sql)
            ->setParameters([
                'member' => $member,
                'groupActivities' => $groupActivities,
            ])
        ;
        $activities = $query->getResult();

        return $activities;
    }

    public function getOrderByNumberOfTimesForMemberAndActivities($member, $activitiesId)
    {
        $sql =
            '
            SELECT a, COUNT(s) AS numberOfTimes
            FROM AppBundle:Activity a
            LEFT JOIN a.schedules s WITH (s.member = :member AND s.isTraining = 0)
            WHERE a.allowForDivision = 1
            AND a.id IN (:activitiesId)
            GROUP BY a.id
            ORDER BY numberOfTimes ASC, a.ranking ASC
            '
        ;

        $em = $this->getEntityManager();
        $query = $em
            ->createQuery($sql)
            ->setParameters([
                'member'       => $member,
                'activitiesId' => $activitiesId,
            ])
        ;
        $activities = $query->getResult();

        return $activities;
    }
}
