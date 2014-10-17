<?php

namespace Drupal\multiversion\Entity\Transaction;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\multiversion\Entity\Exception\ConflictException;
use Drupal\multiversion\Entity\Index\HeadIndexInterface;
use Drupal\multiversion\Entity\Index\RevisionIndexInterface;
use Drupal\multiversion\Entity\Index\SequenceIndexInterface;

class TranscationManager implements TransactionManagerInterface {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface 
   */
  protected $entityManager;

  /**
   * @var \Drupal\multiversion\Entity\Index\SequenceIndexInterface
   */
  protected $seqIndex;

  /**
   * @var \Drupal\multiversion\Entity\Index\RevisionIndexInterface
   */
  protected $revIndex;

  /**
   * @var \Drupal\multiversion\Entity\Index\HeadIndexInterface
   */
  protected $headIndex;

  /**
   * @var \Drupal\Core\Entity\ContentEntityInterface[]
   */
  protected $stack;

  /**
   * @var boolean
   */
  protected $policyIsLocked = FALSE;

  /**
   * @var integer
   */
  protected $policy = NULL;

  /**
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\multiversion\Entity\Index\SequenceIndexInterface $seq_index
   * @param \Drupal\multiversion\Entity\Index\RevisionIndexInterface $rev_index
   * @param \Drupal\multiversion\Entity\Index\HeadIndexInterface $head_index
   */
  public function __construct(EntityManagerInterface $entity_manager, SequenceIndexInterface $seq_index, RevisionIndexInterface $rev_index, HeadIndexInterface $head_index) {
    $this->entityManager = $entity_manager;
    $this->seqIndex = $sequence_index;
    $this->revIndex = $rev_index;
    $this->headIndex = $head_index;
  }

  /**
   * {@inheritdoc}
   */
  public function setPolicy($policy) {
    if ($this->policyIsLocked) {
      throw new \LogicException('The transaction policy is locked and can not be changed.');
    }
    if (!in_array($policy, array(TransactionManagerInterface::POLICY_ATOMIC, TransactionManagerInterface::POLICY_ALL_OR_NOTHING))) {
      throw new \InvalidArgumentException(String::format('@policy is an invalid transaction policy', array('@policy' => $policy)));
    }
    $this->policy = $policy;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function lockPolicy() {
    $this->policyIsLocked = TRUE;
    return $this->policy ?: TransactionManagerInterface::POLICY_ATOMIC;
  }

  /**
   * {@inheritdoc}
   */
  public function stage(ContentEntityInterface $entity) {
    $policy = $this->lockPolicy();

    $this->stack[] = $entity;

    if ($policy == TransactionManagerInterface::POLICY_ATOMIC) {
      $this->commit();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    foreach ($this->stack as $entity) {
      $this->seqIndex->add($entity);
      $this->revIndex->add($entity);
      $this->headIndex->add($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onDeleteRevision($entity_type_id, $revision_id) {
    $entity = $this->entityManager->getStorage($entity_type_id)->loadRevision($revision_id);
    throw new ConflictException($entity, 'The storage model implemented by Multiversion module does not allow deleting revisions.');
  }
}
