<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface;
use Drupal\multiversion\MultiversionManagerInterface;

class SequenceIndex implements SequenceIndexInterface {

  const COLLECTION_PREFIX = 'entity_sequence_index:';

  /**
   * @var \Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface
   */
  protected $sortedSetFactory;

  /**
   * @var \Drupal\multiversion\MultiversionManagerInterface
   */
  protected $multiversionManager;

  public function __construct(KeyValueSortedSetFactoryInterface $sorted_set_factory, MultiversionManagerInterface $multiversion_manager) {
    $this->sortedSetFactory = $sorted_set_factory;
    $this->multiversionManager = $multiversion_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function add(ContentEntityInterface $entity, $parent_revision_id, $conflict = FALSE) {
    $workspace_name = $this->multiversionManager->getActiveWorkspaceName();
    $record = $this->buildRecord($entity, $parent_revision_id, $conflict);
    $sequence_id = $entity->_local_seq->id;
    $this->sortedSetFactory->get(self::COLLECTION_PREFIX . $workspace_name)->add($sequence_id, $record);
  }

  /**
   * {@inheritdoc}
   */
  public function getRange($start, $stop = NULL) {
    $workspace_name = $this->multiversionManager->getActiveWorkspaceName();
    return $this->sortedSetFactory->get(self::COLLECTION_PREFIX . $workspace_name)->getRange($start, $stop);
  }

  protected function buildRecord(ContentEntityInterface $entity, $parent_revision_id, $conflict) {
    return array(
      'local_seq' => $entity->_local_seq->id,
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'revision_id' => $entity->getRevisionId(),
      'parent_revision_id' => $parent_revision_id,
      'deleted' => $entity->_deleted->value,
      'conflict' => $conflict,
    );
  }
}
