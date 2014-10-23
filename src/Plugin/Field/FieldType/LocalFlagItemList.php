<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;

class LocalFlagItemList extends FieldItemList {

  public function preSave() {
    if ($this->getEntity()->getEntityType()->get('local')) {
      $this->get(0)->value = TRUE;
    }
    parent::preSave();
  }

}
