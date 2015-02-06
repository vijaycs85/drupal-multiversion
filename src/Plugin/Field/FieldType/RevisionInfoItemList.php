<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;

class RevisionInfoItemList extends FieldItemList {

  public function preSave() {

    $entity = $this->getEntity();

    if (!isset($entity->new_edits) || ($entity->new_edits !== FALSE)) {
      // Generate the revision hash.
      $i = $this->isEmpty() ? 0 : $this->count();

      $rev = \Drupal::service('multiversion.manager')
        ->newRevisionId($entity, $i);

      // Append the hash to our field.
      $this->get($i)->rev = $rev;

      // Reverse the item list to have the last revision first.
      $items = array_reverse($this->getValue());
      $this->setValue($items);
   }

    parent::preSave();
  }
}
