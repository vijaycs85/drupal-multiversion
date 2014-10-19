<?php

namespace Drupal\multiversion\Entity\Transaction;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Decorates a content entity storage handler with transaction support.
 */
interface TransactionInterface {

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $containerInterface
   * @param \Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface $storage
   * @return \Drupal\multiversion\Entity\Transaction\TransactionInterface
   */
  public static function createInstance(ContainerInterface $containerInterface, ContentEntityStorageInterface $storage);

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @see \Drupal\Core\Entity\EntityStorageInterface::save()
   */
  public function save(ContentEntityInterface $entity);

  /**
   * @return null
   */
  public function commit();

  /**
   * Must be used to proxy non-dectorated methods to the storage handler.
   *
   * @param string $method
   * @param array $args
   * @return mixed
   */
  public function __call($method, $args);

}
