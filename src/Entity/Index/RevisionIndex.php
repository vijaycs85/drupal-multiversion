<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;

class RevisionIndex extends EntityIndex implements RevisionIndexInterface {

  /**
   * @var string
   */
  protected $collection_prefix = 'entity.index.rev.';

  /**
   * {@inheritdoc}
   */
  protected function buildKey(EntityInterface $entity) {
    return $entity->uuid() . ':' . $entity->_revs_info->rev;
  }
}
