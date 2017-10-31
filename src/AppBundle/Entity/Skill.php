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
    const TELEPILOTE           = 1;
    const REGISSEUR            = 2;
    const GCS                  = 3;
    const ECLAIRAGISTE         = 4;
    const RESPONSABLE_NEOPTER  = 5;
    const OPERATEUR_NEOPTER    = 6;
    const RESPONSABLE_SECURITE = 7;
    const OPERATEUR_SECURITE   = 8;
    const VISUEL_REGIE         = 9;
    const VISUEL               = 10;
    const SUPPLEANT            = 11;

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
     * @ORM\ManyToOne(targetEntity="Hut", inversedBy="skills")
     * @ORM\JoinColumn(name="hut_id", referencedColumnName="id")
     */
    private $hut;

    public function __construct() {
        $this->skillActivities = new ArrayCollection();
        $this->userSkills      = new ArrayCollection();
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
     * Set hut
     *
     * @param \AppBundle\Entity\Hut $hut
     *
     * @return Skill
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
}
