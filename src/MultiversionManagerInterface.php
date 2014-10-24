<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

interface MultiversionManagerInterface {

  /**
   * @return string
   * @deprecated Should no longer be used.
   * @see \Drupal\multiversion\WorkspaceManager::getActiveId()
   */
  public function getActiveWorkspaceName();

  /**
   * @param string $id
   * @deprecated Should no longer be used.
   * @see \Drupal\multiversion\WorkspaceManager::setActiveId()
   */
  public function setActiveWorkspaceName($id);

  /**
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @return boolean
   */
  public function isSupportedEntityType(EntityTypeInterface $entity_type);

  /**
   * @return integer
   */
  public function newSequenceId();

  /**
   * @return string
   */
  public function newRevisionId(ContentEntityInterface $entity, $index = 0);

}
