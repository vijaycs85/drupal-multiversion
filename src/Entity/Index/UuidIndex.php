<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;
use Drupal\multiversion\Entity\Index\IndexBase;

class UuidIndex extends IndexBase {

  /**
   * @var string
   */
  protected $collection_prefix = 'entity.index.uuid.';

  /**
   * {@inheritdoc}
   */
  protected function buildKey(EntityInterface $entity) {
    return $entity->uuid();
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
      'local_seq' => $entity->_local_seq->value,
      'rev' => $entity->_revs_info->rev,
    );
  }
}
