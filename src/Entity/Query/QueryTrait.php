<?php

namespace Drupal\multiversion\Entity\Query;

trait QueryTrait {

  /**
   * @var boolean
   */
  protected $isDeleted = FALSE;

  /**
   * @var boolean
   */
  protected $isTransacting = FALSE;

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isDeleted()
   */
  public function isDeleted() {
    $this->isDeleted = TRUE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isNotDeleted()
   */
  public function isNotDeleted() {
    $this->isDeleted = FALSE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isTransacting()
   */
  public function isTransacting() {
    $this->isTransacting = TRUE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isNotTransacting()
   */
  public function isNotTransacting() {
    $this->isTransacting = FALSE;
    return $this;
  }

  public function prepare() {
    parent::prepare();
    $this->condition('_deleted', (int) $this->isDeleted);
    $this->condition('_trx', (int) $this->isTransacting);
    return $this;
  }

}
