<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;
use Drupal\multiversion\Entity\Index\IndexBase;
use Drupal\multiversion\Entity\Index\RevisionIndexInterface;

class RevisionIndex extends IndexBase implements RevisionIndexInterface {

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
