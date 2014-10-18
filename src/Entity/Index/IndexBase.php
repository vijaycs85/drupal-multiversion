<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\multiversion\MultiversionManagerInterface;

abstract class IndexBase implements IndexInterface {

  const COLLECTION_PREFIX = 'entity_index:';

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValueFactory;

  /**
   * @var \Drupal\multiversion\MultiversionManagerInterface
   */
  protected $multiversionManager;

  /**
   * @var string
   */
  protected $workspaceName;

  /**
   * @var array
   */
  protected $cache = array();

  /**
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   * @param \Drupal\multiversion\MultiversionManagerInterface $multiversion_manager
   */
  function __construct(KeyValueFactoryInterface $key_value_factory, MultiversionManagerInterface $multiversion_manager) {
    $this->keyValueFactory = $key_value_factory;
    $this->multiversionManager = $multiversion_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function useWorkspace($name) {
    $this->workspaceName = $name;
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
    $ws = $this->workspaceName ?: $this->multiversionManager->getActiveWorkspaceName();
    // Initialize the cache storage.
    if (!isset($this->cache[$ws])) {
      $this->cache[$ws] = array();
    }

    $values = array();
    $load = array();
    foreach ($keys as $key) {
      // Check if we have a value in the cache.
      if (isset($this->cache[$ws][$key])) {
        $values[$key] = $this->cache[$ws][$key];
      }
      // Load the value if we don't have an explicit NULL value.
      elseif (!array_key_exists($key, $this->cache[$ws])) {
        $load[] = $key;
      }
    }

    if ($load) {
      $loaded_values = $this->keyValueStore($ws)->getMultiple($load);
      foreach ($load as $key) {
        // If we find a value, even one that is NULL, add it to the cache and
        // return it.
        if (isset($loaded_values[$key]) || array_key_exists($key, $loaded_values)) {
          $values[$key] = $loaded_values[$key];
          $this->cache[$ws][$key] = $loaded_values[$key];
        }
        else {
          $this->cache[$ws][$key] = NULL;
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
    $ws = $this->workspaceName ?: $this->multiversionManager->getActiveWorkspaceName();
    $values = array();
    foreach ($entities as $entity) {
      $key = $this->buildKey($entity);
      $value = $this->buildValue($entity);
      $values[$key] = $value;
      $this->cache[$ws][$key] = $value;
    }
    $this->keyValueStore($ws)->setMultiple($values);
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
    $ws = $this->workspaceName ?: $this->multiversionManager->getActiveWorkspaceName();
    foreach ($keys as $key) {
      unset($this->cache[$ws][$key]);
    }
    $this->keyValueStore($ws)->deleteMultiple($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $ws = $this->workspaceName ?: $this->multiversionManager->getActiveWorkspaceName();
    $this->cache[$ws] = array();
    $this->keyValueStore($ws)->deleteAll();
  }

  /**
   * @param string $workspace_name
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected function keyValueStore($workspace_name) {
    return $this->keyValueFactory->get(self::COLLECTION_PREFIX . $workspace_name);
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
