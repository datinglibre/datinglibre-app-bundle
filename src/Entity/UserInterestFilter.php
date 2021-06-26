<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Entity(repositoryClass="DatingLibre\AppBundle\Repository\UserInterestFilterRepository")
 * @ORM\Table(name="datinglibre.user_interest_filters")
 */
class UserInterestFilter
{
    /**
     * @Id()
     * @OneToOne(targetEntity="Interest")
     * @JoinColumn(name = "interest_id", referencedColumnName = "id")
     */
    private Interest $interest;

    /**
     * @Id()
     * @OneToOne(targetEntity="user")
     * @JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private User $user;

    public function getInterest(): Interest
    {
        return $this->interest;
    }

    public function setInterest(Interest $interest): void
    {
        $this->interest = $interest;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
