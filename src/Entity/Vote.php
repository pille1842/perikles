<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VoteRepository::class)
 */
class Vote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Poll::class, inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

    /**
     * @ORM\ManyToOne(targetEntity=Option::class, inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $votefor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $passcode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(?Poll $poll): self
    {
        $this->poll = $poll;

        return $this;
    }

    public function getVotefor(): ?Option
    {
        return $this->votefor;
    }

    public function setVotefor(?Option $votefor): self
    {
        $this->votefor = $votefor;

        return $this;
    }

    public function getPasscode(): ?string
    {
        return $this->passcode;
    }

    public function setPasscode(string $passcode): self
    {
        $this->passcode = $passcode;

        return $this;
    }
}
