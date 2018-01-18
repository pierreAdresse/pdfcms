<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Skill
 *
 * @ORM\Table(name="skill")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SkillRepository")
 */
class Skill
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
     * @ORM\OneToMany(targetEntity="SkillActivity", mappedBy="skill")
     */
    private $skillActivities;


    /**
     * @ORM\OneToMany(targetEntity="UserSkill", mappedBy="skill")
     */
    private $userSkills;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="mainSkill")
     */
    private $users;

    public function __construct() {
        $this->skillActivities = new ArrayCollection();
        $this->userSkills      = new ArrayCollection();
        $this->users           = new ArrayCollection();
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
     * @return Skill
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
     * Add skillActivity
     *
     * @param \AppBundle\Entity\SkillActivity $skillActivity
     *
     * @return Skill
     */
    public function addSkillActivity(\AppBundle\Entity\SkillActivity $skillActivity)
    {
        $this->skillActivities[] = $skillActivity;

        return $this;
    }

    /**
     * Remove skillActivity
     *
     * @param \AppBundle\Entity\SkillActivity $skillActivity
     */
    public function removeSkillActivity(\AppBundle\Entity\SkillActivity $skillActivity)
    {
        $this->skillActivities->removeElement($skillActivity);
    }

    /**
     * Get skillActivities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSkillActivities()
    {
        return $this->skillActivities;
    }

    /**
     * Add userSkill
     *
     * @param \AppBundle\Entity\UserSkill $userSkill
     *
     * @return Skill
     */
    public function addUserSkill(\AppBundle\Entity\UserSkill $userSkill)
    {
        $this->userSkills[] = $userSkill;

        return $this;
    }

    /**
     * Remove userSkill
     *
     * @param \AppBundle\Entity\UserSkill $userSkill
     */
    public function removeUserSkill(\AppBundle\Entity\UserSkill $userSkill)
    {
        $this->userSkills->removeElement($userSkill);
    }

    /**
     * Get userSkills
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserSkills()
    {
        return $this->userSkills;
    }

    /**
     * Add user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Skill
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
}
