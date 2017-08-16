<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Schedule
 *
 * @ORM\Table(name="schedule")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ScheduleRepository")
 */
class Schedule
{
    use TimestampableEntity;
    use BlameableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cinescenie", inversedBy="schedules")
     * @ORM\JoinColumn(name="cinescenie_id", referencedColumnName="id")
     */
    private $cinescenie;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="schedules")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="schedules")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id")
     */
    private $activity;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cinescenie
     *
     * @param \AppBundle\Entity\Cinescenie $cinescenie
     *
     * @return Schedule
     */
    public function setCinescenie(\AppBundle\Entity\Cinescenie $cinescenie = null)
    {
        $this->cinescenie = $cinescenie;

        return $this;
    }

    /**
     * Get cinescenie
     *
     * @return \AppBundle\Entity\Cinescenie
     */
    public function getCinescenie()
    {
        return $this->cinescenie;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Schedule
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set activity
     *
     * @param \AppBundle\Entity\Activity $activity
     *
     * @return Schedule
     */
    public function setActivity(\AppBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \AppBundle\Entity\Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
