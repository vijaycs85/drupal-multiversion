<?php

namespace Drupal\multiversion\Entity\Storage;

use Drupal\Core\Entity\EntityInterface;
use Drupal\multiversion\Entity\Exception\ConflictException;

trait ContentEntityStorageTrait {

  /**
   * @var boolean
   */
  protected $isDeleted = FALSE;

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    $this->isDeleted = FALSE;
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
    $this->isDeleted = TRUE;
    return parent::loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  protected function doSave($id, EntityInterface $entity) {
    // Entities are always saved as new revisions when using a Multiversion
    // storage handler.
    $entity->setNewRevision();
    return parent::doSave($id, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    // Entites are always "deleted" as new revisions when using a Multiversion
    // storage handler.
    foreach ($entities as $entity) {
      $entity->_deleted->value = TRUE;
      $this->save($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRevision($revision_id) {
    throw new ConflictException(NULL, 'Revisions can not be deleted when using a Multiversion storage handler.');
  }

  /**
   * {@inheritdoc}
   */
  public function purge($entities) {
    // Purge equals that of a traditional delete when using a Multiversion
    // storage handler.
    return parent::delete($entities);
  }

}
