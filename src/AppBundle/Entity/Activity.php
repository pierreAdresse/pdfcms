<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Activity
 *
 * @ORM\Table(name="activity")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ActivityRepository")
 */
class Activity
{
    const SUPPLEANT           = 21;
    const SUPPLEANT_SPECTACLE = 22;
    const LAISSEZ_PASSER      = 23;

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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="GroupActivities", inversedBy="activities")
     * @ORM\JoinColumn(name="groupActivities_id", referencedColumnName="id")
     */
    private $groupActivities;

    /**
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="activity")
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="SkillActivity", mappedBy="activity")
     */
    private $skillActivities;

    /**
     * @ORM\OneToMany(targetEntity="SpecialtyActivity", mappedBy="activity")
     */
    private $specialtyActivities;

    /**
     * @var int
     *
     * @ORM\Column(name="ranking", type="integer", unique=false)
     */
    private $ranking;

    /**
     * @var int
     *
     * @ORM\Column(name="order_display", type="integer", unique=false)
     */
    private $orderDisplay;

    /**
     * @var bool
     *
     * @ORM\Column(name="allowForDivision", type="boolean")
     */
    private $allowForDivision;

    /**
     * @var string
     *
     * @ORM\Column(name="cellExcel", type="string", length=255)
     */
    private $cellExcel;

    public function __construct() {
        $this->shedules        = new ArrayCollection();
        $this->skillActivities = new ArrayCollection();
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
     * @return Activity
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
     * Set groupActivities
     *
     * @param \AppBundle\Entity\GroupActivities $groupActivities
     *
     * @return Activity
     */
    public function setGroupActivities(\AppBundle\Entity\GroupActivities $groupActivities = null)
    {
        $this->groupActivities = $groupActivities;

        return $this;
    }

    /**
     * Get groupActivities
     *
     * @return \AppBundle\Entity\GroupActivities
     */
    public function getGroupActivities()
    {
        return $this->groupActivities;
    }

    /**
     * Add skillActivity
     *
     * @param \AppBundle\Entity\SkillActivity $skillActivity
     *
     * @return Activity
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
     * Add schedule
     *
     * @param \AppBundle\Entity\Schedule $schedule
     *
     * @return Activity
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
     * Set ranking
     *
     * @param integer $ranking
     *
     * @return Activity
     */
    public function setRanking($ranking)
    {
        $this->ranking = $ranking;

        return $this;
    }

    /**
     * Get ranking
     *
     * @return integer
     */
    public function getRanking()
    {
        return $this->ranking;
    }

    /**
     * Set allowForDivision
     *
     * @param boolean $allowForDivision
     *
     * @return Activity
     */
    public function setAllowForDivision($allowForDivision)
    {
        $this->allowForDivision = $allowForDivision;

        return $this;
    }

    /**
     * Get allowForDivision
     *
     * @return boolean
     */
    public function getAllowForDivision()
    {
        return $this->allowForDivision;
    }

    /**
     * Add specialtyActivity
     *
     * @param \AppBundle\Entity\SpecialtyActivity $specialtyActivity
     *
     * @return Activity
     */
    public function addSpecialtyActivity(\AppBundle\Entity\SpecialtyActivity $specialtyActivity)
    {
        $this->specialtyActivities[] = $specialtyActivity;

        return $this;
    }

    /**
     * Remove specialtyActivity
     *
     * @param \AppBundle\Entity\SpecialtyActivity $specialtyActivity
     */
    public function removeSpecialtyActivity(\AppBundle\Entity\SpecialtyActivity $specialtyActivity)
    {
        $this->specialtyActivities->removeElement($specialtyActivity);
    }

    /**
     * Get specialtyActivities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpecialtyActivities()
    {
        return $this->specialtyActivities;
    }

    /**
     * Set orderDisplay
     *
     * @param integer $orderDisplay
     *
     * @return Activity
     */
    public function setOrderDisplay($orderDisplay)
    {
        $this->orderDisplay = $orderDisplay;

        return $this;
    }

    /**
     * Get orderDisplay
     *
     * @return integer
     */
    public function getOrderDisplay()
    {
        return $this->orderDisplay;
    }

    /**
     * Set cellExcel
     *
     * @param string $cellExcel
     *
     * @return Activity
     */
    public function setCellExcel($cellExcel)
    {
        $this->cellExcel = $cellExcel;

        return $this;
    }

    /**
     * Get cellExcel
     *
     * @return string
     */
    public function getCellExcel()
    {
        return $this->cellExcel;
    }
}
