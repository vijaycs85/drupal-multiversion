<?php

namespace Drupal\multiversion\Entity\Transaction;

use Drupal\Core\Entity\ContentEntityInterface;

interface TransactionManagerInterface {

  const POLICY_ATOMIC = 1;

  const POLICY_ALL_OR_NOTHING = 2;

  /**
   * @param integer $policy
   * @return \Drupal\multiversion\Entity\Transaction\TransactionManagerInterface
   */
  public function setPolicy($policy);

  /**
   * @return integer
   */
  public function lockPolicy();

  /**
   * Stages an entity for commit.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return \Drupal\multiversion\Entity\Transaction\TransactionManagerInterface
   */
  public function stage(ContentEntityInterface $entity);

  /**
   * Commits all stacked entities to the sequence index.
   * @return array
   */
  public function commit();

  /**
   * Handles the deletion of a revision.
   *
   * @param string $entity_type_id
   * @param integer $revision_id
   * @throws \Drupal\multiversion\Entity\Exception\ConflictException
   */
  public function onDeleteRevision($entity_type_id, $revision_id);

}
