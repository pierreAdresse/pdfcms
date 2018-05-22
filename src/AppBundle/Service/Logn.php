<?php

namespace AppBundle\Service;

use AppBundle\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;

class Logn
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em          = $em;
    }

    public function log($user, $message)
    {
        $log = new Log();
        $log->setUser($user);
        $log->setMessage($message);
        $log->setDate(new \Datetime('now'));

        $this->em->persist($log);
        $this->em->flush();
    }
}