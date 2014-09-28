<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;

class LocalSequenceItemList extends FieldItemList {

  public function preSave() {
    // Multiply the microtime by 1 million to ensure we get an accurate integer.
    // Credit goes to @letharion and @logaritmisk for this simple but genius
    // solution.
    $this->get(0)->value = (int) (microtime(TRUE) * 1000000);
    parent::preSave();
  }
}
