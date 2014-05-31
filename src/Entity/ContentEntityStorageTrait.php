<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;

trait ContentEntityStorageTrait {

  /**
   * @var boolean
   */
  protected $loadDeleted = FALSE;

  public function loadDeleted($id) {
    $entities = $this->loadMultipleDeleted(array($id));
    return isset($entities[$id]) ? $entities[$id] : NULL;
  }

  public function loadMultipleDeleted(array $ids = NULL) {
    $this->loadDeleted = TRUE;
    return parent::loadMultiple($ids);
  }

  /**
   * {@inhertidoc}
   *
   * @todo Conflict detection.
   */
  public function save(EntityInterface $entity) {
    // Force new revision. 
    $entity->setNewRevision();
    // Get the revision ID of the unchanged entity.
    $parent_revision_id = $entity->getRevisionId();
    // Run the normal save method.
    $return = parent::save($entity);
    // Index the event.
    \Drupal::service('entity.sequence_index')->add($entity, $parent_revision_id);
    \Drupal::service('entity.rev_index')->add($entity);
    return $return;
  }

  public function delete(array $entities) {
    // Deleting an entity is simply a matter of setting the deleted flag and
    // saving a new revision.
    foreach ($entities as $entity) {
      $entity->_deleted->value = '1';
      $this->save($entity);
    }
  }

  public function deleteRevision($revision_id) {
    // Do nothing, by design.
  }

  public function purge($entities) {
    return parent::delete($entities);
  }
}
