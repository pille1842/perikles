<?php

namespace App\Service;

use App\Entity\Poll;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class VotingService
{
    private $mailer;
    private $entityManager;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    public function startVote(Poll $poll): void
    {
        // Create a ticket for every voter
        $this->entityManager->beginTransaction();
        try {
            $voters = $poll->getVoters();
            // Randomize the array so you can't guess the voter from the ticket ID
            shuffle($voters);

            foreach ($voters as $voter) {
                $ticket = new Ticket();
                $ticket->setPoll($poll);
                $ticket->setVoter($voter);
                $ticket->setValid(true);
                $ticket->setPasscode($this->generatePasscode());
                $this->entityManager->persist($ticket);
            }
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        // Write the tickets to the database
        $this->entityManager->commit();
        $this->entityManager->flush();

        // Send a message to every voter
        $tickets = $this->entityManager->getRepository(Ticket::class)->findByPoll($poll);
        foreach ($tickets as $ticket) {
            $email = (new TemplatedEmail())
                ->to($ticket->getVoter()->getEmail())
                ->subject("Wahlbenachrichtigung: " . $poll->getTitle())
                ->htmlTemplate('email/pollingcard.html.twig')
                ->context([
                    'poll'   => $poll,
                    'voter'  => $ticket->getVoter(),
                    'ticket' => $ticket,
                ]);
            try {
                $this->mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                // TODO: Better error handling
                throw $e;
            }

            // Finally, remove the reference to the voter from the ticket
            $ticket->setVoter(null);
            $this->entityManager->persist($ticket);
        }
        $this->entityManager->flush();
    }

    private function generatePasscode(): string
    {
        return substr(
            sha1(random_bytes(64)),
            0, 8
        );
    }
}
