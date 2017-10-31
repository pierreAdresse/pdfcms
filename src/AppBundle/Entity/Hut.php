<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Hut
 *
 * @ORM\Table(name="hut")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HutRepository")
 */
class Hut
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="hut")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="Town", inversedBy="huts")
     * @ORM\JoinColumn(name="town_id", referencedColumnName="id")
     */
    private $town;

    /**
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="hut")
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="Skill", mappedBy="hut")
     */
    private $skills;

    /**
     * @ORM\OneToMany(targetEntity="CinescenieState", mappedBy="hut")
     */
    private $cinescenieStates;

    /**
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="hut")
     */
    private $activities;

    public function __construct() {
        $this->users            = new ArrayCollection();
        $this->schedules        = new ArrayCollection();
        $this->skills           = new ArrayCollection();
        $this->activities       = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Hut
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Hut
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
     * Set town
     *
     * @param \AppBundle\Entity\Town $town
     *
     * @return Hut
     */
    public function setTown(\AppBundle\Entity\Town $town = null)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return \AppBundle\Entity\Town
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Add user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Hut
     */
    public function addUser(\AppBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\User $user
     */
    public function removeUser(\AppBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add schedule
     *
     * @param \AppBundle\Entity\Schedule $schedule
     *
     * @return Hut
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
     * Add skill
     *
     * @param \AppBundle\Entity\Skill $skill
     *
     * @return Hut
     */
    public function addSkill(\AppBundle\Entity\Skill $skill)
    {
        $this->skills[] = $skill;

        return $this;
    }

    /**
     * Remove skill
     *
     * @param \AppBundle\Entity\Skill $skill
     */
    public function removeSkill(\AppBundle\Entity\Skill $skill)
    {
        $this->skills->removeElement($skill);
    }

    /**
     * Get skills
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * Add activity
     *
     * @param \AppBundle\Entity\Activity $activity
     *
     * @return Hut
     */
    public function addActivity(\AppBundle\Entity\Activity $activity)
    {
        $this->activities[] = $activity;

        return $this;
    }

    /**
     * Remove activity
     *
     * @param \AppBundle\Entity\Activity $activity
     */
    public function removeActivity(\AppBundle\Entity\Activity $activity)
    {
        $this->activities->removeElement($activity);
    }

    /**
     * Get activities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Add cinescenieState
     *
     * @param \AppBundle\Entity\CinescenieState $cinescenieState
     *
     * @return Hut
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
