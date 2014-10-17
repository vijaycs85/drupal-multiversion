<?php

namespace Drupal\multiversion\Entity\Query;

trait QueryTrait {

  protected $isDeleted = FALSE;

  public function isDeleted() {
    $this->isDeleted = TRUE;
    return $this;
  }

  public function isNotDeleted() {
    $this->isDeleted = FALSE;
    return $this;
  }

  public function prepare() {
    parent::prepare();
    $this->condition('_deleted', (string) $this->isDeleted);
    return $this;
  }

}
