<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\ConversationThread;
use App\Service\ConversationManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
final class AiReservationStatsSubscriber
{
    /**
     * @var array<int, true> Set of clientIds whose stats need to be republished after the current flush.
     *                       Doctrine post* events fire inside the flush; we publish synchronously here
     *                       (the Mercure hub is a fast HTTP POST and tolerates concurrent calls).
     */
    private array $dirtyClients = [];

    public function __construct(
        private readonly HubInterface $hub,
        private readonly ConversationManager $conversationManager,
    ) {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->collect($args->getObject());
        $this->flushPublications();
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->collect($args->getObject());
        $this->flushPublications();
    }

    private function collect(object $entity): void
    {
        if (!$entity instanceof ConversationThread) {
            return;
        }

        $client = $entity->getClient();
        if (!$client || !$client->getId()) {
            return;
        }

        $this->dirtyClients[(int) $client->getId()] = true;
    }

    private function flushPublications(): void
    {
        if (!$this->dirtyClients) {
            return;
        }

        foreach (array_keys($this->dirtyClients) as $clientId) {
            try {
                $stats = $this->buildStatsPayload($clientId);
                $topic = sprintf('/admin/ai-reservation/stats/%d', $clientId);

                $this->hub->publish(new Update(
                    topics: $topic,
                    data: json_encode($stats, \JSON_THROW_ON_ERROR),
                    private: true,
                ));
            } catch (\Throwable) {
                // Mercure failure must never break a Doctrine flush.
            }
        }

        $this->dirtyClients = [];
    }

    /**
     * @return array<string, int>
     */
    private function buildStatsPayload(int $clientId): array
    {
        $counts = $this->conversationManager->getStats($clientId);

        return [
            'pending' => $counts['pending'] ?? 0,
            'analyzing' => $counts['analyzing'] ?? 0,
            'proposing' => $counts['proposing'] ?? 0,
            'awaiting_customer' => $counts['awaiting_customer'] ?? 0,
            'awaiting_club' => $counts['awaiting_club'] ?? 0,
            'confirmed' => $counts['confirmed'] ?? 0,
            'cancelled' => $counts['cancelled'] ?? 0,
            'total' => array_sum($counts),
        ];
    }
}
