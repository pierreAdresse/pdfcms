<?php

namespace AppBundle\Repository;

class GroupActivitiesRepository extends \Doctrine\ORM\EntityRepository
{
    public function getOrderByNumberOfTimesForMember($member)
    {
        $memberSkills = $member->getMemberSkills();
        $skills       = [];
        foreach ($memberSkills as $memberSkill) {
            $skills[] = $memberSkill->getSkill()->getId();    
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT ga, COUNT(s) AS numberOfTimes
            FROM AppBundle:GroupActivities ga
            JOIN ga.activities a
            LEFT JOIN a.schedules s WITH (s.member = :member)
            JOIN a.skillActivities sa WITH (sa.skill IN (:skills))
            WHERE a.allowForDivision = 1
            GROUP BY ga.id
            ORDER BY numberOfTimes ASC
            '
        )->setParameters([
            'member' => $member,
            'skills' => $skills,
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
            JOIN s.cinescenie c WITH (c.isTraining = 0)
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
