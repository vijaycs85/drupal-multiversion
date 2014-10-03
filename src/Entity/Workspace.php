<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\multiversion\Entity\WorkspaceInterface;

/**
 * The workspace entity class.
 *
 * @ConfigEntityType(
 *   id = "workspace",
 *   label = @Translation("Content workspace"),
 *   config_prefix = "workspace",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   }
 * )
 */
class Workspace extends ConfigEntityBase implements WorkspaceInterface {

  /**
   * The name of the workspace.
   *
   * @var string
   */
  public $id;

}
