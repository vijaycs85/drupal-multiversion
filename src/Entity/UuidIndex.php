<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;

class UuidIndex extends IndexBase {

  const COLLECTION_NAME = 'entity_uuid_index';

  protected function buildKey(EntityInterface $entity) {
    return $entity->uuid();
  }

  protected function buildValue(EntityInterface $entity) {
    // @todo: Rename 'entity_type' to 'entity_type_id' for consistency.
    $value = array(
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
    );

    /** @var \Drupal\multiversion\MultiversionManagerInterface $manager */
    $manager = \Drupal::service('multiversion.manager');
    $entity_type = $entity->getEntityType();
    if ($manager->isSupportedEntityType($entity_type)) {
      $value['rev'] = $entity->_revs_info->rev;
      $value['revision_id'] = $entity->getRevisionId();
      $value['local_seq'] = $entity->_local_seq->value;
    }

    return $value;
  }
}
