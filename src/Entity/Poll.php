<?php

namespace App\Entity;

use App\Repository\PollRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PollRepository::class)
 */
class Poll
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $started;

    /**
     * @ORM\Column(type="boolean")
     */
    private $stopped;

    /**
     * @ORM\OneToMany(targetEntity=Option::class, mappedBy="poll", orphanRemoval=true, cascade={"persist"})
     */
    private $options;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="poll", orphanRemoval=true)
     */
    private $tickets;

    /**
     * @ORM\OneToMany(targetEntity=Vote::class, mappedBy="poll", orphanRemoval=true)
     */
    private $votes;

    /**
     * @ORM\ManyToMany(targetEntity=Voter::class, inversedBy="polls")
     */
    private $voters;

    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->votes   = new ArrayCollection();
        $this->started = false;
        $this->stopped = false;
        $this->voters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStarted(): ?bool
    {
        return $this->started;
    }

    public function setStarted(bool $started): self
    {
        $this->started = $started;

        return $this;
    }

    public function getStopped(): ?bool
    {
        return $this->stopped;
    }

    public function setStopped(bool $stopped): self
    {
        $this->stopped = $stopped;

        return $this;
    }

    /**
     * @return Collection|Option[]
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setPoll($this);
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        if ($this->options->removeElement($option)) {
            // set the owning side to null (unless already changed)
            if ($option->getPoll() === $this) {
                $option->setPoll(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setPoll($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getPoll() === $this) {
                $ticket->setPoll(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Vote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setPoll($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getPoll() === $this) {
                $vote->setPoll(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Voter[]
     */
    public function getVoters(): Collection
    {
        return $this->voters;
    }

    public function addVoter(Voter $voter): self
    {
        if (!$this->voters->contains($voter)) {
            $this->voters[] = $voter;
        }

        return $this;
    }

    public function removeVoter(Voter $voter): self
    {
        $this->voters->removeElement($voter);

        return $this;
    }
}
