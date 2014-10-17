<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\multiversion\Entity\Transaction\TransactionManagerInterface;

interface ContentEntityStorageInterface extends EntityStorageInterface {

  /**
   * @return \Drupal\multiversion\Entity\Transaction\TransactionManagerInterface
   */
  public function getTransactionManager();

  /**
   * @param integer $id
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   */
  public function loadDeleted($id);

  /**
   * @param array $ids
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   */
  public function loadMultipleDeleted(array $ids = NULL);

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function purge($entities);

}
