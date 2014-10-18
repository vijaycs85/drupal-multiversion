<?php

namespace Drupal\multiversion\Entity\Query;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;

trait QueryTrait {

  /**
   * @var int
   */
  protected $storageStatus = NULL;

  public function isDeleted() {
    $this->storageStatus = ContentEntityStorageInterface::STATUS_DELETED;
    return $this;
  }

  public function isNotDeleted() {
    $this->storageStatus = ContentEntityStorageInterface::STATUS_AVAILABLE;
    return $this;
  }

  public function prepare() {
    parent::prepare();
    $this->condition('_status', $this->storageStatus ?: ContentEntityStorageInterface::STATUS_AVAILABLE);
    return $this;
  }

}
