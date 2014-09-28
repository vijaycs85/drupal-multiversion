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
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *   },
 *   config_prefix = "workspace",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "name" = "name",
 *   }
 * )
 */
class Workspace extends ConfigEntityBase implements WorkspaceInterface {

  /**
   * The name of the workspace.
   *
   * @var string
   */
  public $name;

  /**
   * The ID of the workspace.
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
   * Overrides Entity::__construct().
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
  }

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
