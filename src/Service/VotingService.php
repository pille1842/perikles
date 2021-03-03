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
    const PASSCODE_LENGTH = 16;

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
            $voters = $poll->getVoters()->toArray();
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

            // Finally, remove the reference to the voter from the ticket...
            $ticket->setVoter(null);
            // ... and store the passcode as a SHA-512 hash instead
            $ticket->setPasscode(hash('sha512', $ticket->getPasscode()));

            $this->entityManager->persist($ticket);
        }
        $this->entityManager->flush();
    }

    private function generatePasscode(): string
    {
        // Don't generate passcodes containing l, I, 1, 0, or O
        $characters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $passcode   = '';

        for ($i = 0; $i < $this::PASSCODE_LENGTH; $i++) {
            $index = rand(0, strlen($characters) - 1);
            if ($i > 0 && $i % 4 == 0) {
                // Insert a separating character
                $passcode .= '-';
            }
            $passcode .= $characters[$index];
        }

        return $passcode;
    }
}
