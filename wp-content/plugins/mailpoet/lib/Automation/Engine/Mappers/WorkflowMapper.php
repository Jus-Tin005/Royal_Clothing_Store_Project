<?php declare(strict_types = 1);

namespace MailPoet\Automation\Engine\Mappers;

if (!defined('ABSPATH')) exit;


use DateTimeImmutable;
use MailPoet\Automation\Engine\Data\NextStep;
use MailPoet\Automation\Engine\Data\Step;
use MailPoet\Automation\Engine\Data\Workflow;
use MailPoet\Automation\Engine\Data\WorkflowStatistics;
use MailPoet\Automation\Engine\Storage\WorkflowStatisticsStorage;

class WorkflowMapper {
  /** @var WorkflowStatisticsStorage */
  private $statisticsStorage;

  public function __construct(
    WorkflowStatisticsStorage $statisticsStorage
  ) {
    $this->statisticsStorage = $statisticsStorage;
  }

  public function buildWorkflow(Workflow $workflow): array {
    return [
      'id' => $workflow->getId(),
      'name' => $workflow->getName(),
      'status' => $workflow->getStatus(),
      'created_at' => $workflow->getCreatedAt()->format(DateTimeImmutable::W3C),
      'updated_at' => $workflow->getUpdatedAt()->format(DateTimeImmutable::W3C),
      'activated_at' => $workflow->getActivatedAt() ? $workflow->getActivatedAt()->format(DateTimeImmutable::W3C) : null,
      'author' => [
        'id' => $workflow->getAuthor()->ID,
        'name' => $workflow->getAuthor()->display_name,
      ],
      'stats' => $this->statisticsStorage->getWorkflowStats($workflow->getId())->toArray(),
      'steps' => array_map(function (Step $step) {
        return [
          'id' => $step->getId(),
          'type' => $step->getType(),
          'key' => $step->getKey(),
          'args' => $step->getArgs(),
          'next_steps' => array_map(function (NextStep $nextStep) {
            return $nextStep->toArray();
          }, $step->getNextSteps()),
        ];
      }, $workflow->getSteps()),
    ];
  }

  /** @param Workflow[] $workflows */
  public function buildWorkflowList(array $workflows): array {
    $statistics = $this->statisticsStorage->getWorkflowStatisticsForWorkflows(...$workflows);
    return array_map(function (Workflow $workflow) use ($statistics) {
      return $this->buildWorkflowListItem($workflow, $statistics[$workflow->getId()]);
    }, $workflows);
  }

  private function buildWorkflowListItem(Workflow $workflow, WorkflowStatistics $statistics): array {
    return [
      'id' => $workflow->getId(),
      'name' => $workflow->getName(),
      'status' => $workflow->getStatus(),
      'created_at' => $workflow->getCreatedAt()->format(DateTimeImmutable::W3C),
      'updated_at' => $workflow->getUpdatedAt()->format(DateTimeImmutable::W3C),
      'stats' => $statistics->toArray(),
      'activated_at' => $workflow->getActivatedAt() ? $workflow->getActivatedAt()->format(DateTimeImmutable::W3C) : null,
      'author' => [
        'id' => $workflow->getAuthor()->ID,
        'name' => $workflow->getAuthor()->display_name,
      ],
    ];
  }
}
