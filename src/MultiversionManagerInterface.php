<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

interface MultiversionManagerInterface {

  /**
   * @return string
   */
  public function getActiveWorkspaceName();

  /**
   * @param string $workspace_name
   */
  public function setActiveWorkspaceName($workspace_name);

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
