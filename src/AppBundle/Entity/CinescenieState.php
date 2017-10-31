<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CinescenieState
 * Etat de chaque Cinéscénie suivant le groupe
 *
 * @ORM\Table(name="cinescenie_state")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CinescenieStateRepository")
 */
class CinescenieState
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
     * @var int
     *
     * @ORM\Column(name="state", type="integer")
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="Cinescenie", inversedBy="cinescenieStates")
     * @ORM\JoinColumn(name="cinescenie_id", referencedColumnName="id")
     */
    private $cinescenie;

    /**
     * @ORM\ManyToOne(targetEntity="Hut", inversedBy="cinescenieStates")
     * @ORM\JoinColumn(name="hut_id", referencedColumnName="id")
     */
    private $hut;


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
     * Set state
     *
     * @param integer $state
     *
     * @return CinescenieState
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set cinescenie
     *
     * @param \AppBundle\Entity\Cinescenie $cinescenie
     *
     * @return CinescenieState
     */
    public function setCinescenie(\AppBundle\Entity\Cinescenie $cinescenie = null)
    {
        $this->cinescenie = $cinescenie;

        return $this;
    }

    /**
     * Get cinescenie
     *
     * @return \AppBundle\Entity\Cinescenie
     */
    public function getCinescenie()
    {
        return $this->cinescenie;
    }

    /**
     * Set hut
     *
     * @param \AppBundle\Entity\Hut $hut
     *
     * @return CinescenieState
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
