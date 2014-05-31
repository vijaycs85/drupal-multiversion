<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;

interface MultiversionManagerInterface {

  static public function requiredRepositoryDefinitions();

  static public function requiredFieldDefinitions();

  public function ensureRequiredRepositories();

  public function ensureRequiredFields();

  public function attachRequiredFields($entity_type, $bundle);

  public function getActiveRepositoryName();

  public function setActiveRepositoryName($repository_name);

  public function newRevisionId(ContentEntityInterface $entity, $index = 0);

}
