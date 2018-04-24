<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialtyActivity
 *
 * @ORM\Table(name="specialty_activity")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialtyActivityRepository")
 */
class SpecialtyActivity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Specialty", inversedBy="specialtyActivities")
     * @ORM\JoinColumn(name="specialty_id", referencedColumnName="id")
     */
    private $specialty;

    /**
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="specialtyActivities")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id")
     */
    private $activity;

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
     * Set specialty
     *
     * @param \AppBundle\Entity\Specialty $specialty
     *
     * @return SpecialtyActivity
     */
    public function setSpecialty(\AppBundle\Entity\Specialty $specialty = null)
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * Get specialty
     *
     * @return \AppBundle\Entity\Specialty
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }

    /**
     * Set activity
     *
     * @param \AppBundle\Entity\Activity $activity
     *
     * @return SpecialtyActivity
     */
    public function setActivity(\AppBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \AppBundle\Entity\Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
