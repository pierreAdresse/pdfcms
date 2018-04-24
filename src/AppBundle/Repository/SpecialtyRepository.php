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
}
