<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;

class UuidIndex extends EntityIndex {

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
}
