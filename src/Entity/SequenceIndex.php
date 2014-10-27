<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface;
use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;

class SequenceIndex implements SequenceIndexInterface {

  const COLLECTION_PREFIX = 'entity.index.sequence.';

  /**
   * @var string
   */
  protected $workspaceId;

  /**
   * @var \Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface
   */
  protected $sortedSetFactory;

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * @param \Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface $sorted_set_factory
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface $workspace_manager
   */
  public function __construct(KeyValueSortedSetFactoryInterface $sorted_set_factory, WorkspaceManagerInterface $workspace_manager) {
    $this->sortedSetFactory = $sorted_set_factory;
    $this->workspaceManager = $workspace_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function useWorkspace($id) {
    $this->workspaceId = $id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function add(ContentEntityInterface $entity) {
    $this->addMultiple(array($entity));
  }

  /**
   * {@inheritdoc}
   */
  public function addMultiple(array $entities) {
    $pairs = array();
    foreach ($entities as $entity) {
      $sequence_id = $entity->_local_seq->value;
      $pairs[] = array($sequence_id => $this->buildRecord($entity));
    }
    $this->sortedSetStore()->addMultiple($pairs);
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
    $workspace_id = $this->workspaceId ?: $this->workspaceManager->getActiveWorkspace()->id();
    return $this->sortedSetFactory->get(self::COLLECTION_PREFIX . $workspace_id);
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return array
   */
  protected function buildRecord(ContentEntityInterface $entity) {
    return array(
      'local_seq' => $entity->_local_seq->value,
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'entity_uuid' => $entity->uuid(),
      'revision_id' => $entity->getRevisionId(),
      'parent_revision_id' => ($entity->_revs_info->count() > 1) ? $entity->_revs_info[1]->rev : 0,
      'deleted' => $entity->_deleted->value,
      'conflict' => FALSE, //@todo
      'local' => $entity->_local->value,
      'rev' => $entity->_revs_info->rev,
    );
  }
}
