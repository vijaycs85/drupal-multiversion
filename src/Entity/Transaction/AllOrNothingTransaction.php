<?php

namespace Drupal\multiversion\Entity\Transaction\Mode;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Transaction\TransactionBase;

class AllOrNothingTransaction extends TransactionBase {

  /**
   * @var array
   */
  protected $statuses;

  /**
   * {@inheritdoc}
   */
  public function save(ContentEntityInterface $entity) {
    $original_status = $entity->_status->value;
    $entity->_status->value = ContentEntityStorageInterface::STATUS_IN_TRANSACTION;
    $return = $this->storage->save($entity);
    $this->statuses[$entity->getRevisionId()] = $original_status;
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $this->storage->updateStorageStatus($this->statuses);
  }

}
