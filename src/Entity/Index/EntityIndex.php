<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;

class EntityIndex implements EntityIndexInterface {

  /**
   * @var string
   */
  protected $collectionPrefix = 'multiversion.entity_index.id.';

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
  public function __construct(KeyValueFactoryInterface $key_value_factory, WorkspaceManagerInterface $workspace_manager) {
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
  public function get($key) {
    $values = $this->getMultiple(array($key));
    return isset($values[$key]) ? $values[$key] : array();
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\Core\State\State::getMultiple()
   */
  public function getMultiple(array $keys) {
    $workspace_id = $this->getWorkspaceId();
    // Initialize the cache storage.
    if (!isset($this->cache[$workspace_id])) {
      $this->cache[$workspace_id] = array();
    }

    $values = array();
    $load = array();
    foreach ($keys as $key) {
      // Check if we have a value in the cache.
      if (isset($this->cache[$workspace_id][$key])) {
        $values[$key] = $this->cache[$workspace_id][$key];
      }
      // Load the value if we don't have an explicit NULL value.
      elseif (!array_key_exists($key, $this->cache[$workspace_id])) {
        $load[] = $key;
      }
    }

    if ($load) {
      $loaded_values = $this->keyValueStore($workspace_id)->getMultiple($load);
      if (count($keys) != count($loaded_values)) {
        $loaded_values2 = $this->keyValueStore()->getMultiple($load);
        $loaded_values = array_merge($loaded_values, $loaded_values2);
      }
      foreach ($load as $key) {
        // If we find a value, even one that is NULL, add it to the cache and
        // return it.
        if (isset($loaded_values[$key]) || array_key_exists($key, $loaded_values)) {
          $values[$key] = $loaded_values[$key];
          $this->cache[$workspace_id][$key] = $loaded_values[$key];
        }
        else {
          $this->cache[$workspace_id][$key] = NULL;
        }
      }
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function add(EntityInterface $entity) {
    $this->addMultiple(array($entity));
  }

  /**
   * {@inheritdoc}
   */
  public function addMultiple(array $entities) {
    $workspace_id = $this->getWorkspaceId();
    $values = [];
    /** @var ContentEntityInterface $entity */
    foreach ($entities as $entity) {
      $key = $this->buildKey($entity);
      $value = $this->buildValue($entity);
      if ($entity->getEntityType()->get('workspace') == FALSE) {
        $values[0][$key] = $value;
      }
      else {
        $values[$workspace_id][$key] = $value;
      }
    }

    $this->cache = $values;

    foreach ($values as $workspace_id => $value) {
      $this->keyValueStore($workspace_id)->setMultiple($value);
    }
  }

  /**
   * @param int $workspace_id
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected function keyValueStore($workspace_id = 0) {
    return $this->keyValueFactory->get($this->collectionPrefix . $workspace_id);
  }

  /**
   * Helper method for building the key to be indexed.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @return string
   */
  protected function buildKey(EntityInterface $entity) {
    return $entity->getEntityTypeId() . ':' . $entity->id();
  }

  /**
   * Helper method for building the value to be indexed.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @return array
   */
  protected function buildValue(EntityInterface $entity) {
    !$is_new = $entity->isNew();
    $revision_id = $is_new ? 0 : $entity->getRevisionId();
    // We assign a temporary status to the revision since we are indexing it
    // pre save. It will be updated post save with the final status. This will
    // help identifying failures and exception scenarios during entity save.
    $status = 'indexed';
    if (!$is_new && $revision_id) {
      $status = $entity->_deleted->value ? 'deleted' : 'available';
    }

    return array(
      'entity_type_id' => $entity->getEntityTypeId(),
      'entity_id' => $is_new ? 0 : $entity->id(),
      'revision_id' => $revision_id,
      'uuid' => $entity->uuid(),
      'rev' => $entity->_rev->value,
      'is_stub' => $entity->_rev->is_stub,
      'status' => $status,
    );
  }

  /**
   * Helper method for getting what workspace ID to query.
   */
  protected function getWorkspaceId() {
    return $this->workspaceId ?: $this->workspaceManager->getActiveWorkspace()->id();
  }

}
