<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;

/**
 * @todo Implement caching in a way that avoids stale trees and race conditions.
 */
class RevisionTreeIndex implements RevisionTreeIndexInterface {

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValueFactory;

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * @var string
   */
  protected $workspaceId;

  /**
   * @var array
   */
  protected $cache = array();

  /**
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface $workspace_manager
   */
  function __construct(KeyValueFactoryInterface $key_value_factory, WorkspaceManagerInterface $workspace_manager) {
    $this->keyValueFactory = $key_value_factory;
    $this->workspaceManager = $workspace_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function useWorkspace($id) {
    $this->workspaceId = $id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($uuid) {
    $list = $this->keyValueStore($uuid)->getAll();
    return self::buildTree($list, 0);
  }

  /**
   * {@inheritdoc}
   */
  public function update($uuid, array $branch = array()) {
    $this->keyValueStore($uuid)->setMultiple($branch);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultRevision($uuid) {
    $tree = $this->get($uuid);
    // @todo
  }

  /**
   * {@inheritdoc}
   */
  public function getOpenRevisions($uuid) {
    $tree = $this->get($uuid);
    // @todo
  }

  /**
   * @param string $uuid
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected function keyValueStore($uuid) {
    $workspace_id = $this->workspaceId ?: $this->workspaceManager->getActiveWorkspace()->id();
    return $this->keyValueFactory->get("entity.index.open_revs.$workspace_id.$uuid");
  }

  /**
   * Helper method to recursively build the tree.
   */
  protected static function buildTree(&$list, $parent) {
    // @todo
  }
}
