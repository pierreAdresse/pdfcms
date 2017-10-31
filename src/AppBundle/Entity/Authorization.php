<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Authorization
 *
 * @ORM\Table(name="authorization")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AuthorizationRepository")
 */
class Authorization
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
     * @ORM\OneToMany(targetEntity="UserAuthorization", mappedBy="authorization")
     */
    private $userAuthorizations;

    public function __construct() {
        $this->userAuthorizations = new ArrayCollection();
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
     * @return Authorization
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
     * Add userAuthorization
     *
     * @param \AppBundle\Entity\UserAuthorization $userAuthorization
     *
     * @return Authorization
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
