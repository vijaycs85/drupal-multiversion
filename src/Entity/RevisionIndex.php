<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;

class RevisionIndex extends IndexBase {

  const COLLECTION_NAME = 'entity_rev_index';

  protected function buildKey(EntityInterface $entity) {
    return $entity->uuid() . ':' . $entity->_revs_info->rev;
  }

  protected function buildValue(EntityInterface $entity) {
    return array(
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'revision_id' => $entity->id(),
      'local_seq' => $entity->_local_seq->id,
    );
  }
}
