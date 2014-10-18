<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\multiversion\Entity\Index\IndexBase;
use Drupal\multiversion\Entity\Index\RevisionIndexInterface;

class RevisionIndex extends IndexBase implements RevisionIndexInterface {

  const COLLECTION_PREFIX = 'entity_rev_index:';

  /**
   * {@inheritdoc}
   */
  protected function buildKey(EntityInterface $entity) {
    return $entity->uuid() . ':' . $entity->_revs_info->rev;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildValue(EntityInterface $entity) {
    return array(
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'revision_id' => $entity->getRevisionId(),
      'local_seq' => $entity->_local_seq->value,
      'rev' => $entity->_revs_info->rev,
    );
  }
}
