<?php

namespace Drupal\multiversion\Entity\Transaction;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Transaction\TransactionBase;

class AllOrNothingTransaction extends TransactionBase {

  /**
   * @var array
   */
  protected $statuses = array();

  /**
   * {@inheritdoc}
   */
  public function save(ContentEntityInterface $entity) {
    $original_status = $entity->_status->value;
    $entity->_status->value = ContentEntityStorageInterface::STATUS_IN_TRANSACTION;
    $return = $this->storage->save($entity);
    if (!isset($this->statuses[$original_status])) {
      $this->statuses[$original_status] = array();
    }
    $this->statuses[$original_status][] = $entity->getRevisionId();
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $this->storage->updateStorageStatus($this->statuses);
    // Flush all logged statuses.
    $this->statuses = array();
  }

}
