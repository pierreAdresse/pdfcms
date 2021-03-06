<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Member
 *
 * @ORM\Table(name="member")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MemberRepository")
 */
class Member
{
    const LAISSEZ_PASSER = 2;

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
     * @var int
     *
     * @ORM\Column(name="code", type="integer", unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=180)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=20, nullable=true)
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="arrival", type="date")
     */
    private $arrival;

    /**
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="member")
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="MemberSkill", mappedBy="member")
     */
    private $memberSkills;

    /**
     * @ORM\OneToMany(targetEntity="MemberSpecialty", mappedBy="member")
     */
    private $memberSpecialties;

    /**
     * @ORM\ManyToOne(targetEntity="Skill", inversedBy="members")
     * @ORM\JoinColumn(name="skill_id", referencedColumnName="id")
     */
    private $mainSkill;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_new", type="boolean")
     */
    private $isNew;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=255)
     */
    private $nickname;

    public function __construct() {
        $this->shedules     = new ArrayCollection();
        $this->memberSkills = new ArrayCollection();
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
     * Set code
     *
     * @param integer $code
     *
     * @return Member
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Member
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Member
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Member
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Member
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return Member
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set address
     *
     * @param text $address
     *
     * @return Member
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return text
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set arrival
     *
     * @param \DateTime $arrival
     *
     * @return Member
     */
    public function setArrival($arrival)
    {
        $this->arrival = $arrival;

        return $this;
    }

    /**
     * Get arrival
     *
     * @return \DateTime
     */
    public function getArrival()
    {
        return $this->arrival;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return Member
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Add schedule
     *
     * @param \AppBundle\Entity\Schedule $schedule
     *
     * @return Member
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
     * Set mainSkill
     *
     * @param \AppBundle\Entity\Skill $mainSkill
     *
     * @return Member
     */
    public function setMainSkill(\AppBundle\Entity\Skill $mainSkill = null)
    {
        $this->mainSkill = $mainSkill;

        return $this;
    }

    /**
     * Get mainSkill
     *
     * @return \AppBundle\Entity\Skill
     */
    public function getMainSkill()
    {
        return $this->mainSkill;
    }

    /**
     * Add memberSkill
     *
     * @param \AppBundle\Entity\MemberSkill $memberSkill
     *
     * @return Member
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

    /**
     * Add memberSpecialty
     *
     * @param \AppBundle\Entity\MemberSpecialty $memberSpecialty
     *
     * @return Member
     */
    public function addMemberSpecialty(\AppBundle\Entity\MemberSpecialty $memberSpecialty)
    {
        $this->memberSpecialties[] = $memberSpecialty;

        return $this;
    }

    /**
     * Remove memberSpecialty
     *
     * @param \AppBundle\Entity\MemberSpecialty $memberSpecialty
     */
    public function removeMemberSpecialty(\AppBundle\Entity\MemberSpecialty $memberSpecialty)
    {
        $this->memberSpecialties->removeElement($memberSpecialty);
    }

    /**
     * Get memberSpecialties
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMemberSpecialties()
    {
        return $this->memberSpecialties;
    }

    /**
     * Set isNew
     *
     * @param boolean $isNew
     *
     * @return Member
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;

        return $this;
    }

    /**
     * Get isNew
     *
     * @return boolean
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Set nickname
     *
     * @param string $nickname
     *
     * @return Member
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get nickname
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }
}
