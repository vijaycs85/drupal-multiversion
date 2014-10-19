<?php

namespace Drupal\multiversion\Entity\Transaction;

interface RevertibleTransactionInterface extends TransactionInterface {

  /**
   * Returns the entities that were unsuccessfully rolled back so that further
   * actions can be taken by the application.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   */
  public function rollback();

}
