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
    const TELEPILOTE           = 1;
    const ECLAIRAGISTE         = 2;
    const REGISSEUR            = 3;
    const GCS_1                = 4;
    const GCS_2                = 5;
    const NEOPTER_1            = 6;
    const NEOPTER_2            = 7;
    const NEOPTER_3            = 8;
    const NEOPTER_4            = 9;
    const NEOPTER_5            = 10;
    const SECURITE_1           = 11;
    const SECURITE_2           = 12;
    const SECURITE_3           = 13;
    const SECURITE_4           = 14;
    const SECURITE_5           = 15;
    const VISUEL_1             = 16;
    const VISUEL_2             = 17;
    const VISUEL_3             = 18;
    const VISUEL_4             = 19;
    const VISUEL_5             = 20;
    const SUPPLEANT            = 21;
    const SUPPLEANT_CINESCENIE = 22;

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
}
