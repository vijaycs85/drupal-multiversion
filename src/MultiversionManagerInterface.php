<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;

interface MultiversionManagerInterface {

  static public function requiredWorkspaceDefinitions();

  static public function requiredFieldDefinitions();

  public function ensureRequiredRepositories();

  public function ensureRequiredFields();

  public function attachRequiredFields($entity_type, $bundle);

  public function getActiveWorkspaceName();

  public function setActiveWorkspaceName($workspace_name);

  public function newRevisionId(ContentEntityInterface $entity, $index = 0);

}
