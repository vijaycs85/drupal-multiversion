<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;

class WorkspaceReferenceItemList extends FieldItemList {

  public function preSave() {
    if ($this->get(0)->isEmpty()) {
      $this->get(0)->target_id = \Drupal::service('multiversion.manager')
        ->getActiveWorkspaceName();
    }
    parent::preSave();
  }

}
