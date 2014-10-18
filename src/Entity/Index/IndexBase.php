<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\multiversion\Entity\Index\IndexInterface;

abstract class IndexBase implements IndexInterface {

  const COLLECTION_NAME = 'entity_index';

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $keyValueStore;

  /**
   * @var array
   */
  protected $cache = array();

  /**
   * {@inheritdoc}
   */
  function __construct(KeyValueFactoryInterface $key_value_factory) {
    $this->keyValueStore = $key_value_factory->get(self::COLLECTION_NAME);
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
    $values = array();
    $load = array();
    foreach ($keys as $key) {
      // Check if we have a value in the cache.
      if (isset($this->cache[$key])) {
        $values[$key] = $this->cache[$key];
      }
      // Load the value if we don't have an explicit NULL value.
      elseif (!array_key_exists($key, $this->cache)) {
        $load[] = $key;
      }
    }

    if ($load) {
      $loaded_values = $this->keyValueStore->getMultiple($load);
      foreach ($load as $key) {
        // If we find a value, even one that is NULL, add it to the cache and
        // return it.
        if (isset($loaded_values[$key]) || array_key_exists($key, $loaded_values)) {
          $values[$key] = $loaded_values[$key];
          $this->cache[$key] = $loaded_values[$key];
        }
        else {
          $this->cache[$key] = NULL;
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
    $values = array();
    foreach ($entities as $entity) {
      $key = $this->buildKey($entity);
      $value = $this->buildValue($entity);
      $values[$key] = $value;
      $this->cache[$key] = $value;
    }
    $this->keyValueStore->setMultiple($values);
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
    foreach ($keys as $key) {
      unset($this->cache[$key]);
    }
    $this->keyValueStore->deleteMultiple($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $this->cache = array();
    $this->keyValueStore->deleteAll();
  }

  abstract protected function buildKey(EntityInterface $entity);

  abstract protected function buildValue(EntityInterface $entity);

}
