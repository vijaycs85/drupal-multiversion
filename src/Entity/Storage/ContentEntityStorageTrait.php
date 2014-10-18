<?php

namespace Drupal\multiversion\Entity\Storage;

use Drupal\Core\Entity\EntityInterface;

trait ContentEntityStorageTrait {

  /**
   * @var int
   */
  protected $storageStatus = NULL;

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    $this->storageStatus = ContentEntityStorageInterface::STATUS_AVAILABLE;
    return parent::loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function loadDeleted($id) {
    $entities = $this->loadMultipleDeleted(array($id));
    return isset($entities[$id]) ? $entities[$id] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleDeleted(array $ids = NULL) {
    $this->storageStatus = ContentEntityStorageInterface::STATUS_DELETED;
    return parent::loadMultiple($ids);
  }

  /**
   * {@inhertidoc}
   */
  public function save(EntityInterface $entity) {
    // Force new revision.
    $entity_type = $entity->getEntityType();
    // Respect if the entity type has defined itself to be local.
    // @todo Consider moving this logic into the field itself instead.
    if ($entity_type->get('local')) {
      $entity->_local->value = TRUE;
    }
    // Run the normal save method.
    $return = parent::save($entity);
    // Index the event.
    \Drupal::service('entity.sequence_index')->add($entity);
    \Drupal::service('entity.rev_index')->add($entity);
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  protected function doSave($id, EntityInterface $entity) {
    // Force new revision.
    $entity->setNewRevision();
    return parent::doSave($id, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    // Deleting an entity is simply a matter of updating the storage status flag
    // and saving a new revision.
    foreach ($entities as $entity) {
      $entity->_status->value = ContentEntityStorageInterface::STATUS_DELETED;
      $this->save($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRevision($revision_id) {
    // Do nothing, by design.
  }

  /**
   * {@inheritdoc}
   */
  public function purge($entities) {
    return parent::delete($entities);
  }
}
