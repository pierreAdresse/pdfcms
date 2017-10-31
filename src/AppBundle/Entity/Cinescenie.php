<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cinescenie
 *
 * @ORM\Table(name="cinescenie")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CinescenieRepository")
 */
class Cinescenie
{
    const STATE_DRAFT    = 0;
    const STATE_VALIDATE = 1;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="integer")
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="cinescenie")
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="CinescenieState", mappedBy="cinescenie")
     */
    private $cinescenieStates;

    public function __construct() {
        $this->schedules        = new ArrayCollection();
        $this->cinescenieStates = new ArrayCollection();
    }


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Cinescenie
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return Cinescenie
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Add schedule
     *
     * @param \AppBundle\Entity\Schedule $schedule
     *
     * @return Cinescenie
     */
    public function addSchedule(\AppBundle\Entity\Schedule $schedule)
    {
        $this->schedules[] = $schedule;

        return $this;
    }

    /**
     * Remove schedule
     *
     * @param \AppBundle\Entity\Schedule $schedule
     */
    public function removeSchedule(\AppBundle\Entity\Schedule $schedule)
    {
        $this->schedules->removeElement($schedule);
    }

    /**
     * Get schedules
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSchedules()
    {
        return $this->schedules;
    }

    /**
     * Add cinescenieState
     *
     * @param \AppBundle\Entity\CinescenieState $cinescenieState
     *
     * @return Cinescenie
     */
    public function addCinescenieState(\AppBundle\Entity\CinescenieState $cinescenieState)
    {
        $this->cinescenieStates[] = $cinescenieState;

        return $this;
    }

    /**
     * Remove cinescenieState
     *
     * @param \AppBundle\Entity\CinescenieState $cinescenieState
     */
    public function removeCinescenieState(\AppBundle\Entity\CinescenieState $cinescenieState)
    {
        $this->cinescenieStates->removeElement($cinescenieState);
    }

    /**
     * Get cinescenieStates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCinescenieStates()
    {
        return $this->cinescenieStates;
    }
}
