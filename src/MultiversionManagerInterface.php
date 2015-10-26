<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

interface MultiversionManagerInterface {

  /**
   * @return string
   * @deprecated Should no longer be used.
   * @see \Drupal\multiversion\Workspace\WorkspaceManager::getActiveWorkspace()
   */
  public function getActiveWorkspaceId();

  /**
   * @param string $id
   * @deprecated Should no longer be used.
   * @see \Drupal\multiversion\Workspace\WorkspaceManager::setActiveWorkspace()
   */
  public function setActiveWorkspaceId($id);

  /**
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param boolean $ignore_status
   * @return boolean
   */
  public function isSupportedEntityType(EntityTypeInterface $entity_type, $ignore_status = FALSE);

  /**
   * @param boolean $ignore_status
   * @return array
   */
  public function getSupportedEntityTypes($ignore_status = FALSE);

  /**
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   */
  public function enableEntityType(EntityTypeInterface $entity_type);

  /**
   * @return \Drupal\multiversion\MultiversionManagerInterface
   */
  public function enableEntityTypes();

  /**
   * @return integer
   */
  public function newSequenceId();

  /**
   * @return integer
   */
  public function lastSequenceId();

  /**
   * @return string
   */
  public function newRevisionId(ContentEntityInterface $entity, $index = 0);

}
