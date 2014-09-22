<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;

interface MultiversionManagerInterface {

  static public function requiredWorkspaceDefinitions();

  public function ensureRequiredRepositories();

  public function getActiveWorkspaceName();

  public function setActiveWorkspaceName($workspace_name);

  public function newRevisionId(ContentEntityInterface $entity, $index = 0);

}
