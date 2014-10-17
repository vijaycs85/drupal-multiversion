<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

interface MultiversionManagerInterface {

  static public function requiredWorkspaceDefinitions();

  public function ensureRequiredRepositories();

  public function getActiveWorkspaceName();

  public function setActiveWorkspaceName($workspace_name);

  public function isSupportedEntityType(EntityTypeInterface $entity_type);

  public function newSequenceId();

  public function newRevisionId(ContentEntityInterface $entity, $index = 0);

}
