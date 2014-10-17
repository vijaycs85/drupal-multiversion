<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;

trait ContentEntityStorageTrait {

  /**
   * @var boolean
   */
  protected $loadDeleted = FALSE;

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    $this->loadDeleted = FALSE;
    return parent::load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    $this->loadDeleted = FALSE;
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
    $this->loadDeleted = TRUE;
    return parent::loadMultiple($ids);
  }

  /**
   * {@inhertidoc}
   *
   * @todo Transaction detection.
   * @todo Consider implementing hook_entity_insert/update for this to make
   *   the system depend less on this storage controller.
   */
  public function save(EntityInterface $entity) {
    // Force new revision.
    $entity_type = $entity->getEntityType();
    // Respect if the entity type has defined itself to be local.
    // @todo Consider moving this logic into the field itself instead.
    if ($entity_type->get('local')) {
      $entity->_local->value = TRUE;
    }
    // Get the revision ID of the unchanged entity.
    $parent_revision_id = $entity->getRevisionId();
    // Run the normal save method.
    $return = parent::save($entity);
    // Index the event.
    \Drupal::service('entity.sequence_index')->add($entity, $parent_revision_id);
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
    // Deleting an entity is simply a matter of setting the deleted flag and
    // saving a new revision.
    foreach ($entities as $entity) {
      $entity->_deleted->value = '1';
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
