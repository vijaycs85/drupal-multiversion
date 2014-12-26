<?php

namespace Drupal\multiversion\Entity\Transaction;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Temporary mock object to use for functionality that wants to be forward
 * compatible until transactions can handle multiple entity types.
 */
class MockTransaction extends TransactionBase implements TransactionInterface {

  /**
   * Constructor.
   */
  public function __construct() {
    // Require nothing by design.
  }

  /**
   * {@inheritdoc}
   */
  public function save(ContentEntityInterface $entity) {
    $entity->save();
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    // Do nothing by design.
  }

}
