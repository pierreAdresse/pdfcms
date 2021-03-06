<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Specialty
 *
 * @ORM\Table(name="specialty")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialtyRepository")
 */
class Specialty
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
     * @var stringl
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="SpecialtyActivity", mappedBy="specialty")
     */
    private $specialtyActivities;


    /**
     * @ORM\OneToMany(targetEntity="MemberSpecialty", mappedBy="specialty")
     */
    private $memberSpecialties;

    /**
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="specialty")
     */
    private $schedules;

    /**
     * @var int
     *
     * @ORM\Column(name="ranking", type="integer", unique=false)
     */
    private $ranking;

    /**
     * @var string
     *
     * @ORM\Column(name="cellExcel", type="string", length=255)
     */
    private $cellExcel;

    public function __construct() {
        $this->specialtyActivities = new ArrayCollection();
        $this->memberSpecialties   = new ArrayCollection();
        $this->schedules           = new ArrayCollection();
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
     * @return Specialty
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
     * Add specialtyActivity
     *
     * @param \AppBundle\Entity\SpecialtyActivity $specialtyActivity
     *
     * @return Specialty
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
     * Add memberSpecialty
     *
     * @param \AppBundle\Entity\MemberSpecialty $memberSpecialty
     *
     * @return Specialty
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
     * Add schedule
     *
     * @param \AppBundle\Entity\Schedule $schedule
     *
     * @return Specialty
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
     * @return Specialty
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
     * Set cellExcel
     *
     * @param string $cellExcel
     *
     * @return Specialty
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
