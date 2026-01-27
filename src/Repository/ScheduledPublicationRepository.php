<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Repository;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PsychedCms\Workflow\Calendar\PublishContentEvent;

/**
 * @extends ServiceEntityRepository<PublishContentEvent>
 */
class ScheduledPublicationRepository extends ServiceEntityRepository implements ScheduledPublicationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublishContentEvent::class);
    }

    public function findByTarget(string $class, int|string $id): ?PublishContentEvent
    {
        return $this->createQueryBuilder('e')
            ->where('e.targetClass = :class')
            ->andWhere('e.targetId = :id')
            ->andWhere('e.processedAt IS NULL')
            ->setParameter('class', $class)
            ->setParameter('id', (string) $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findScheduledPublications(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?string $contentClass = null,
    ): iterable {
        $qb = $this->createQueryBuilder('e')
            ->where('e.scheduledAt >= :from')
            ->andWhere('e.scheduledAt <= :to')
            ->andWhere('e.processedAt IS NULL')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('e.scheduledAt', 'ASC');

        if ($contentClass !== null) {
            $qb->andWhere('e.targetClass = :contentClass')
                ->setParameter('contentClass', $contentClass);
        }

        return $qb->getQuery()->getResult();
    }
}
