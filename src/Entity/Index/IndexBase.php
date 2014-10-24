<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\multiversion\WorkspaceManagerInterface;

abstract class IndexBase implements IndexInterface {

  /**
   * @var string
   */
  protected $collection_prefix = 'entity.index.';

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValueFactory;

  /**
   * @var \Drupal\multiversion\WorkspaceManagerInterface
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
   * @param \Drupal\multiversion\WorkspaceManagerInterface $workspace_manager
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
    $workspace_id = $this->workspaceId ?: $this->workspaceManager->getActiveId();
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
    $workspace_id = $this->workspaceId ?: $this->workspaceManager->getActiveId();
    $values = array();
    foreach ($entities as $entity) {
      $key = $this->buildKey($entity);
      $value = $this->buildValue($entity);
      $values[$key] = $value;
      $this->cache[$workspace_id][$key] = $value;
    }
    $this->keyValueStore($workspace_id)->setMultiple($values);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    $this->deleteMultiple(array($key));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    $workspace_id = $this->workspaceId ?: $this->workspaceManager->getActiveId();
    foreach ($keys as $key) {
      unset($this->cache[$workspace_id][$key]);
    }
    $this->keyValueStore($workspace_id)->deleteMultiple($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $workspace_id = $this->workspaceId ?: $this->workspaceManager->getActiveId();
    $this->cache[$workspace_id] = array();
    $this->keyValueStore($workspace_id)->deleteAll();
  }

  /**
   * @param string $workspace_name
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected function keyValueStore($workspace_name) {
    return $this->keyValueFactory->get($this->collection_prefix . $workspace_name);
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @return string
   */
  abstract protected function buildKey(EntityInterface $entity);

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @return mixed
   */
  abstract protected function buildValue(EntityInterface $entity);

}
