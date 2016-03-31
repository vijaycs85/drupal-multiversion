<?php

/**
 * @file
 * Contains \Drupal\multiversion\Workspace\WorkspaceManager.
 */

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\UseCacheBackendTrait;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @todo Cache the active workspace in a cache backend. Caching in a class
 *   property is causing issues when there might be multiple instances of the
 *   manager.
 */
class WorkspaceManager implements WorkspaceManagerInterface {

  use UseCacheBackendTrait;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

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
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   */
  public function __construct(RequestStack $request_stack, EntityManagerInterface $entity_manager, AccountProxyInterface $current_user, CacheBackendInterface $cache_backend) {
    $this->requestStack = $request_stack;
    $this->entityManager = $entity_manager;
    $this->currentUser = $current_user;
    $this->cacheBackend = $cache_backend;
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
    $cid = $this->getCacheId();
    if ($cache = $this->cacheGet($cid)) {
      return $this->load($cache->data);
    }
    else {
      $request = $this->requestStack->getCurrentRequest();
      foreach ($this->getSortedNegotiators() as $negotiator) {
        if ($negotiator->applies($request)) {
          if ($workspace_id = $negotiator->getWorkspaceId($request)) {
            if ($workspace = $this->load($workspace_id)) {
              $this->cacheSet($cid, $workspace->id(), Cache::PERMANENT, $this->getCacheTags());
              return $workspace;
            }
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
        $this->cacheSet($this->getCacheId(), $workspace->id(), Cache::PERMANENT, $this->getCacheTags());
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

  /**
   * @return array
   */
  protected function getCacheTags() {
    return ['workspace_values', 'entity_field_info'];
  }

  /**
   * @return string
   */
  protected function getCacheId() {
    $path = $this->requestStack->getCurrentRequest()->getPathInfo();
    return 'active_workspace_id:' . $this->currentUser->id() . ':' . $path;
  }

}
