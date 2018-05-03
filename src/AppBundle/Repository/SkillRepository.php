<?php

namespace AppBundle\Repository;

class SkillRepository extends \Doctrine\ORM\EntityRepository
{
    public function getByMember($member)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            SELECT s
            FROM AppBundle:Skill s
            JOIN s.memberSkills ms WITH (ms.member = :member)
            '
        )->setPArameters([
        	'member' => $member
        ]);
        $skills = $query->getResult();

        return $skills;
    }
}
