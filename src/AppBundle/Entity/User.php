<?php
namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    const LAISSEZ_PASSER = 46;

    use TimestampableEntity;
    use BlameableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="user")
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="UserSkill", mappedBy="user")
     */
    private $userSkills;

    /**
     * @ORM\ManyToOne(targetEntity="Skill", inversedBy="users")
     * @ORM\JoinColumn(name="skill_id", referencedColumnName="id")
     */
    private $mainSkill;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    public function __construct() {
        parent::__construct();
        $this->shedules           = new ArrayCollection();
        $this->userSkills         = new ArrayCollection();
    }


    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
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
     * @return User
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
     * Add userSkill
     *
     * @param \AppBundle\Entity\UserSkill $userSkill
     *
     * @return User
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
     * Add schedule
     *
     * @param \AppBundle\Entity\Schedule $schedule
     *
     * @return User
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
     * Set phone
     *
     * @param integer $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return integer
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mainSkill
     *
     * @param \AppBundle\Entity\Skill $mainSkill
     *
     * @return User
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
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return User
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
}
