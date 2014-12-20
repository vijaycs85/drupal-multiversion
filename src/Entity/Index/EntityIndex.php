<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;

class EntityIndex extends IndexBase implements EntityIndexInterface {

  /**
   * @var string
   */
  protected $collection_prefix = 'entity.index.id.';

  /**
   * {@inheritdoc}
   */
  protected function buildKey(EntityInterface $entity) {
    return $entity->getEntityTypeId() . ':' . $entity->id();
  }

  /**
   * {@inheritdoc}
   */
  protected function buildValue(EntityInterface $entity) {
    // @todo: Rename 'entity_type' to 'entity_type_id' for consistency.
    return array(
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'revision_id' => $entity->getRevisionId(),
      'rev' => $entity->_revs_info->rev,
      'uuid' => $entity->uuid(),
    );
  }
}
