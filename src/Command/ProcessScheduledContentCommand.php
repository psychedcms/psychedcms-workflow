<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Command;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Calendar\Repository\CalendarEventRepositoryInterface;
use PsychedCms\Workflow\Calendar\PublishContentEvent;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\UseCase\AutoPublishInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'psychedcms:workflow:process-scheduled',
    description: 'Process scheduled content and publish items that are ready',
)]
final class ProcessScheduledContentCommand extends Command
{
    public function __construct(
        private readonly CalendarEventRepositoryInterface $calendarEventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AutoPublishInterface $autoPublish,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Show what would be processed without making changes'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('Running in dry-run mode - no changes will be made');
        }

        $events = array_filter(
            iterator_to_array($this->calendarEventRepository->findDueEvents()),
            static fn ($event): bool => $event instanceof PublishContentEvent && !$event->isProcessed()
        );

        if (empty($events)) {
            $io->success('No scheduled content ready to publish');

            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d content item(s) ready to publish', count($events)));

        $published = 0;
        $errors = 0;

        foreach ($events as $event) {
            assert($event instanceof PublishContentEvent);
            $io->writeln(sprintf('  Processing %s#%s...', $event->getTargetClass(), $event->getTargetId()));

            if ($dryRun) {
                $io->writeln('    [DRY-RUN] Would publish');
                $published++;
                continue;
            }

            $content = $this->entityManager->find($event->getTargetClass(), $event->getTargetId());

            if ($content === null) {
                $io->warning(sprintf('    Content not found: %s#%s', $event->getTargetClass(), $event->getTargetId()));
                $errors++;
                continue;
            }

            if (!$content instanceof PublicationWorkflowAwareInterface) {
                $io->warning(sprintf('    Content does not implement workflow interface: %s#%s', $event->getTargetClass(), $event->getTargetId()));
                $errors++;
                continue;
            }

            try {
                $this->autoPublish->execute($content);
                $event->markProcessed();
                $this->entityManager->flush();
                $io->writeln('    Published successfully');
                $published++;
            } catch (\Throwable $e) {
                $io->error(sprintf('    Failed to publish: %s', $e->getMessage()));
                $errors++;
            }
        }

        $io->newLine();

        if ($dryRun) {
            $io->success(sprintf('[DRY-RUN] Would publish %d content item(s)', $published));
        } else {
            $io->success(sprintf('Published %d content item(s)', $published));
        }

        if ($errors > 0) {
            $io->warning(sprintf('%d error(s) occurred', $errors));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
