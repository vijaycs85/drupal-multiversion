<?php

namespace Drupal\multiversion\Entity\Transaction;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Transaction\TransactionBase;

class AllOrNothingTransaction extends TransactionBase {

  /**
   * @var array
   */
  protected $revisionIds = array();

  /**
   * {@inheritdoc}
   */
  public function save(ContentEntityInterface $entity) {
    $entity->_trx->value = TRUE;
    $return = $this->storage->save($entity);
    $this->revisionIds[] = $entity->getRevisionId();
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $this->storage->onTransactionCommit($this->revisionIds);
    // Reset the stored revisions.
    $this->revisionIds = array();
  }

}
