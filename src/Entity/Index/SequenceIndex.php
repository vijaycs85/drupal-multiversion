<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface;
use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\MultiversionManagerInterface;
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
   * @var \Drupal\multiversion\MultiversionManagerInterface
   */
  protected $multiversionManager;

  /**
   * @param \Drupal\key_value\KeyValueStore\KeyValueSortedSetFactoryInterface $sorted_set_factory
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface          $workspace_manager
   * @param \Drupal\multiversion\MultiversionManagerInterface                 $multiversion_manager
   */
  public function __construct(KeyValueSortedSetFactoryInterface $sorted_set_factory, WorkspaceManagerInterface $workspace_manager, MultiversionManagerInterface $multiversion_manager) {
    $this->sortedSetFactory = $sorted_set_factory;
    $this->workspaceManager = $workspace_manager;
    $this->multiversionManager = $multiversion_manager;
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
      $record = $this->buildRecord($entity);
      $pairs[] = array($record['seq'] => $record);
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
      // @todo: Rename 'entity_type' to 'entity_type_id' for consistency.
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'entity_uuid' => $entity->uuid(),
      'revision_id' => $entity->getRevisionId(),
      'parent_revision_id' => ($entity->_revs_info->count() > 1) ? $entity->_revs_info[1]->rev : 0,
      'deleted' => $entity->_deleted->value,
      'rev' => $entity->_revs_info->rev,
      'seq' => $this->multiversionManager->newSequenceId(),
      'local' => (boolean) $entity->getEntityType()->get('local'),
    );
  }
}
