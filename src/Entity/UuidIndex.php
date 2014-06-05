<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;

class UuidIndex extends IndexBase {

  const COLLECTION_NAME = 'entity_uuid_index';

  protected function buildKey(EntityInterface $entity) {
    return $entity->uuid();
  }

  protected function buildValue(EntityInterface $entity) {
    return array(
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
    );
  }
}
