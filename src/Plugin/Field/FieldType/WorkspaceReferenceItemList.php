<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;

class WorkspaceReferenceItemList extends FieldItemList {

  public function preSave() {
    if (!$this->offsetExists(0)) {
      $workspace = \Drupal::service('multiversion.manager')
        ->getActiveWorkspaceId();
      $this->offsetSet(0, $workspace);
    }
    parent::preSave();
  }

}
