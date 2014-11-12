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
   * @var array
   */
  protected $makeDefaultOnCommit = array();

  /**
   * {@inheritdoc}
   */
  public function save(ContentEntityInterface $entity) {
    $was_default_revision = $entity->isDefaultRevision();
    $entity->isDefaultRevision(FALSE);
    $return = $this->storage->save($entity);

    $id = $entity->id();
    if ($was_default_revision) {
      $this->makeDefaultOnCommit[$id] = $entity->getRevisionId();
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $this->storage->onTransactionCommit($this->makeDefaultOnCommit);
    // Reset the stored revisions.
    $this->makeDefaultOnCommit = array();
  }

}
