<?php

namespace AppBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserAuthorization
 *
 * @ORM\Table(name="user_authorization")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserAuthorizationRepository")
 */
class UserAuthorization
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userAuthorizations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Authorization", inversedBy="userAuthorizations")
     * @ORM\JoinColumn(name="authorization_id", referencedColumnName="id")
     */
    private $authorization;

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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserAuthorization
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set authorization
     *
     * @param \AppBundle\Entity\Authorization $authorization
     *
     * @return UserAuthorization
     */
    public function setAuthorization(\AppBundle\Entity\Authorization $authorization = null)
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Get authorization
     *
     * @return \AppBundle\Entity\Authorization
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }
}
