<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\multiversion\Entity\WorkspaceInterface;

/**
 * The content workspace entity class.
 *
 * It's config entity not a content entity because those are contained within a workspace itself.
 *
 * @ConfigEntityType(
 *   id = "workspace",
 *   label = @Translation("Content workspace"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *   },
 *   config_prefix = "workspace",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "name",
 *     "label" = "name",
 *     "name" = "name"
 *   }
 * )
 */
class Workspace extends ConfigEntityBase implements WorkspaceInterface {

  public $name;

  /**
   * The name (plugin ID) of the workspace.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the workspace entity.
   *
   * @var string
   */
  public $label;

  /**
   * {@inheritdoc}
   */
  public function uuid() {
    return $this->name();
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->name();
  }

  /**
   * {@inheritdoc}
   */
  public function name() {
    return $this->name;
  }

  public function preSave(EntityStorageInterface $storage, $update = TRUE) {
    return parent::preSave($storage, $update);
  }
}
