<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;

class LocalSequenceItemList extends FieldItemList {

  public function preSave() {
    $item = $this->get(0)->id = (string) microtime(TRUE);
    parent::preSave();
  }
}
