<?php

/**
 * @file
 * Contains \Drupal\multiversion\LinkItem.
 */

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\link\Plugin\Field\FieldType\LinkItem as CoreLinkItem;

/**
 * Alternative link field item type class.
 */
class LinkItem extends CoreLinkItem {

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    if ($this->hasNewEntity()) {
      // As part of a bulk or replication operation there might be multiple
      // parent entities wanting to auto-create the same reference. So at this
      // point this entity might already be saved, so we look it up by UUID and
      // map it correctly.
      // @see \Drupal\relaxed\BulkDocs\BulkDocs::save()
      if ($this->uri->isNew()) {
        $uuid = $this->uri->uuid();
        if ($uuid && $record = \Drupal::service('multiversion.entity_index.uuid')->get($uuid)) {
          /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
          $entity_type_manager = \Drupal::service('entity_type.manager');
          $entity_type_id = $this->uri->getEntityTypeId();

          // Now we have to decide what revision to use.
          $id_key = $entity_type_manager
            ->getDefinition($entity_type_id)
            ->getKey('id');

          // If the referenced entity is a stub, but a full entity already was
          // created, then load and use that entity instead without saving.
          if ($this->uri->_rev->is_stub && !$record['is_stub']) {
            $this->uri = $entity_type_manager
              ->getStorage($entity_type_id)
              ->load($record['entity_id']);
          }
          // If the referenced entity is not a stub then map it with the correct
          // ID from the existing record and save it.
          elseif (!$this->uri->_rev->is_stub) {
            $this->uri->{$id_key}->value = $record['entity_id'];
            $this->uri->enforceIsNew(FALSE);
            $this->uri->save();
          }
        }
        // Just save the entity if no previous record exists.
        else{
          $this->uri->save();
        }
      }
      // Set the correct value.
      $this->uri = 'entity:' . $this->uri->getEntityTypeId() . '/' . $this->uri->id();
    }
  }

  /**
   * Determines whether the item holds an unsaved entity.
   *
   * @return bool
   *   TRUE if the item holds an unsaved entity.
   */
  public function hasNewEntity() {
    return !$this->isEmpty() && $this->uri instanceof ContentEntityInterface && $this->uri->isNew();
  }

}
