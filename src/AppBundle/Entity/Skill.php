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
     * @ORM\OneToMany(targetEntity="MemberSkill", mappedBy="skill")
     */
    private $memberSkills;

    /**
     * @ORM\OneToMany(targetEntity="Member", mappedBy="mainSkill")
     */
    private $members;

    public function __construct() {
        $this->skillActivities = new ArrayCollection();
        $this->memberSkills    = new ArrayCollection();
        $this->members         = new ArrayCollection();
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
     * Add member
     *
     * @param \AppBundle\Entity\Member $member
     *
     * @return Skill
     */
    public function addMember(\AppBundle\Entity\Member $member)
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member
     *
     * @param \AppBundle\Entity\Member $member
     */
    public function removeMember(\AppBundle\Entity\Member $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add memberSkill
     *
     * @param \AppBundle\Entity\MemberSkill $memberSkill
     *
     * @return Skill
     */
    public function addMemberSkill(\AppBundle\Entity\MemberSkill $memberSkill)
    {
        $this->memberSkills[] = $memberSkill;

        return $this;
    }

    /**
     * Remove memberSkill
     *
     * @param \AppBundle\Entity\MemberSkill $memberSkill
     */
    public function removeMemberSkill(\AppBundle\Entity\MemberSkill $memberSkill)
    {
        $this->memberSkills->removeElement($memberSkill);
    }

    /**
     * Get memberSkills
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMemberSkills()
    {
        return $this->memberSkills;
    }
}
