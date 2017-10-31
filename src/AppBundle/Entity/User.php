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
     * @ORM\OneToMany(targetEntity="UserAuthorization", mappedBy="user")
     */
    private $userAuthorizations;

    /**
     * @ORM\ManyToOne(targetEntity="Hut", inversedBy="users")
     * @ORM\JoinColumn(name="hut_id", referencedColumnName="id")
     */
    private $hut;

    public function __construct() {
        parent::__construct();
        $this->shedules           = new ArrayCollection();
        $this->userSkills         = new ArrayCollection();
        $this->userAuthorizations = new ArrayCollection();
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
     * Set hut
     *
     * @param \AppBundle\Entity\Hut $hut
     *
     * @return User
     */
    public function setHut(\AppBundle\Entity\Hut $hut = null)
    {
        $this->hut = $hut;

        return $this;
    }

    /**
     * Get hut
     *
     * @return \AppBundle\Entity\Hut
     */
    public function getHut()
    {
        return $this->hut;
    }

    /**
     * Add userAuthorization
     *
     * @param \AppBundle\Entity\UserAuthorization $userAuthorization
     *
     * @return User
     */
    public function addUserAuthorization(\AppBundle\Entity\UserAuthorization $userAuthorization)
    {
        $this->userAuthorizations[] = $userAuthorization;

        return $this;
    }

    /**
     * Remove userAuthorization
     *
     * @param \AppBundle\Entity\UserAuthorization $userAuthorization
     */
    public function removeUserAuthorization(\AppBundle\Entity\UserAuthorization $userAuthorization)
    {
        $this->userAuthorizations->removeElement($userAuthorization);
    }

    /**
     * Get userAuthorizations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserAuthorizations()
    {
        return $this->userAuthorizations;
    }
}
