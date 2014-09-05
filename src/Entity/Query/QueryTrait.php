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
    // Check so that the field has been installed.
    // @todo Change to proper dependency injection
    if ($field = \Drupal::service('entity.manager')->getStorage('field_storage_config')->load($this->entityTypeId . '._deleted')) {
      $this->condition('_deleted', (string) $this->isDeleted);
    }
    return $this;
  }
}
