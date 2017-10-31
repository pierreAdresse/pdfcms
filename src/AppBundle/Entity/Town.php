<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Town
 *
 * @ORM\Table(name="town")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TownRepository")
 */
class Town
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
     * @ORM\OneToMany(targetEntity="Hut", mappedBy="town")
     */
    private $huts;

    public function __construct() {
        $this->huts = new ArrayCollection();
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
     * @return Town
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
     * Set hut
     *
     * @param \AppBundle\Entity\Hut $hut
     *
     * @return Town
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
     * Add hut
     *
     * @param \AppBundle\Entity\Hut $hut
     *
     * @return Town
     */
    public function addHut(\AppBundle\Entity\Hut $hut)
    {
        $this->huts[] = $hut;

        return $this;
    }

    /**
     * Remove hut
     *
     * @param \AppBundle\Entity\Hut $hut
     */
    public function removeHut(\AppBundle\Entity\Hut $hut)
    {
        $this->huts->removeElement($hut);
    }

    /**
     * Get huts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHuts()
    {
        return $this->huts;
    }
}
