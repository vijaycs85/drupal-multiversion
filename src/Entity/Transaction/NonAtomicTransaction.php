<?php

namespace Drupal\multiversion\Entity\Transaction;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\multiversion\Entity\Transaction\TransactionBase;

class NonAtomicTransaction extends TransactionBase {

  /**
   * {@inheritdoc}
   */
  public function save(ContentEntityInterface $entity) {
    return $this->storage->save($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    // Do nothing by design.
  }

}
