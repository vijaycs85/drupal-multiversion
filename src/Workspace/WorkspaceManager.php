<?php

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;

class WorkspaceManager implements WorkspaceManagerInterface {

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var array
   */
  protected $negotiators = array();

  /**
   * @var array
   */
  protected $sortedNegotiators;

  /**
   * @var \Drupal\multiversion\Entity\WorkspaceInterface
   */
  protected $activeWorkspace;

  /**
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   */
  public function __construct(RouteMatchInterface $route_match, EntityManagerInterface $entity_manager) {
    $this->routeMatch = $route_match;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveWorkspace() {
    if (!isset($this->activeWorkspace)) {
      foreach ($this->getSortedNegotiators() as $negotiator) {
        if ($negotiator->applies($this->routeMatch)) {
          if ($workspace_id = $negotiator->getWorkspaceId($this->routeMatch)) {
            if ($workspace = $this->entityManager->getStorage('workspace')->load($workspace_id)) {
              $negotiator->persist($workspace);
              $this->activeWorkspace = $workspace;
            }
          }
        }
      }
    }
    return $this->activeWorkspace;
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveWorkspace(WorkspaceInterface $workspace) {
    $this->activeWorkspace = $workspace;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addNegotiator(WorkspaceNegotiatorInterface $negotiator, $priority) {
    $this->negotiators[$priority][] = $negotiator;
    $this->sortedNegotiators = NULL;
  }

  /**
   * @return \Drupal\multiversion\Workspace\WorkspaceNegotiatorInterface[]
   */
  protected function getSortedNegotiators() {
    if (!isset($this->sortedNegotiators)) {
      // Sort the negotiators according to priority.
      krsort($this->negotiators);
      // Merge nested negotiators from $this->negotiators into
      // $this->sortedNegotiators.
      $this->sortedNegotiators = array();
      foreach ($this->negotiators as $builders) {
        $this->sortedNegotiators = array_merge($this->sortedNegotiators, $builders);
      }
    }
    return $this->sortedNegotiators;
  }

}
