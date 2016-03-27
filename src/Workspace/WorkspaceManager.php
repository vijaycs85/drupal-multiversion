<?php

/**
 * @file
 * Contains \Drupal\multiversion\Workspace\WorkspaceManager.
 */

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @todo Cache the active workspace in a cache backend. Caching in a class
 *   property is causing issues when there might be multiple instances of the
 *   manager.
 */
class WorkspaceManager implements WorkspaceManagerInterface {

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   */
  public function __construct(RequestStack $request_stack, EntityManagerInterface $entity_manager) {
    $this->requestStack = $request_stack;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function addNegotiator(WorkspaceNegotiatorInterface $negotiator, $priority) {
    $this->negotiators[$priority][] = $negotiator;
    $this->sortedNegotiators = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function load($workspace_id) {
    return $this->entityManager->getStorage('workspace')->load($workspace_id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $workspace_ids = NULL) {
    return $this->entityManager->getStorage('workspace')->loadMultiple($workspace_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function loadByMachineName($machine_name) {
    $workspaces = $this->entityManager->getStorage('workspace')->loadByProperties(['machine_name' => $machine_name]);
    return current($workspaces);
  }

  /**
   * {@inheritdoc}
   *
   * @todo {@link https://www.drupal.org/node/2600382 Access check.}
   */
  public function getActiveWorkspace() {
    $request = $this->requestStack->getCurrentRequest();
    foreach ($this->getSortedNegotiators() as $negotiator) {
      if ($negotiator->applies($request)) {
        if ($workspace_id = $negotiator->getWorkspaceId($request)) {
          if ($active_workspace = $this->load($workspace_id)) {
            return $active_workspace;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveWorkspace(WorkspaceInterface $workspace) {
    // Set the workspace on the proper negotiator.
    $request = $this->requestStack->getCurrentRequest();
    foreach ($this->getSortedNegotiators() as $negotiator) {
      if ($negotiator->applies($request)) {
        $negotiator->persist($workspace);
        break;
      }
    }

    return $this;
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
