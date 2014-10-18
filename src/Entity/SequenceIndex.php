<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface;
use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\MultiversionManagerInterface;

class SequenceIndex implements SequenceIndexInterface {

  const COLLECTION_PREFIX = 'entity_sequence_index:';

  /**
   * @var string
   */
  protected $workspaceName;

  /**
   * @var \Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface
   */
  protected $sortedSetFactory;

  /**
   * @var \Drupal\multiversion\MultiversionManagerInterface
   */
  protected $multiversionManager;

  /**
   * @param \Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface $sorted_set_factory
   * @param \Drupal\multiversion\MultiversionManagerInterface $multiversion_manager
   */
  public function __construct(KeyValueSortedSetFactoryInterface $sorted_set_factory, MultiversionManagerInterface $multiversion_manager) {
    $this->sortedSetFactory = $sorted_set_factory;
    $this->multiversionManager = $multiversion_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function useWorkspace($name) {
    $this->workspaceName = $name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function add(ContentEntityInterface $entity, $conflict = FALSE) {
    $record = $this->buildRecord($entity, $conflict);
    $sequence_id = $entity->_local_seq->value;
    $this->sortedSetStore()->add($sequence_id, $record);
  }

  /**
   * {@inheritdoc}
   */
  public function getRange($start, $stop = NULL) {
    return $this->sortedSetStore()->getRange($start, $stop);
  }

  /**
   * {@inheritdoc}
   */
  public function getLastSequenceId() {
    return $this->sortedSetStore()->getMaxScore();
  }

  /**
   * @return \Drupal\key_value\KeyValueStore\KeyValueStoreSortedSetInterface
   */
  protected function sortedSetStore() {
    $workspace_name = $this->workspaceName ?: $this->multiversionManager->getActiveWorkspaceName();
    return $this->sortedSetFactory->get(self::COLLECTION_PREFIX . $workspace_name);
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param $conflict
   * @return array
   */
  protected function buildRecord(ContentEntityInterface $entity, $conflict) {
    return array(
      'local_seq' => $entity->_local_seq->value,
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'entity_uuid' => $entity->uuid(),
      'revision_id' => $entity->getRevisionId(),
      'parent_revision_id' => ($entity->_revs_info->count() > 1) ? $entity->_revs_info[1]->rev : 0,
      'deleted' => $entity->_deleted->value,
      'conflict' => $conflict,
      'local' => $entity->_local->value,
      'rev' => $entity->_revs_info->rev,
    );
  }
}
