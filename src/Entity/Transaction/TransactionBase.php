<?php

namespace Drupal\multiversion\Entity\Transaction;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TransactionBase implements TransactionInterface {

  /**
   * @var \Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface
   */
  protected $storage;

  /**
   * @var array
   */
  protected $records;

  /**
   * @param \Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface $storage
   */
  public function __construct(ContentEntityStorageInterface $storage) {
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, ContentEntityStorageInterface $storage) {
    return new static($storage);
  }

  /**
   * Proxy all other methods calls directly to the decorated storage handler.
   *
   * @param string $method
   * @param array $args
   * @return mixed
   */
  public function __call($method, $args) {
    return call_user_func_array(array($this->storage, $method), $args);
  }

}
