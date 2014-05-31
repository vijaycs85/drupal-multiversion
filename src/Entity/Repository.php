<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * The content repository entity class.
 *
 * The content repository entity is neither an implementation of
 * ContentEntityInterface or ConfigEntityInterface. It's not a content entity
 * because those are contained within a repository itself. And it's not a
 * config entity because certain field data (such as document count etc.) is
 * constantly changing in production, and the config system is not designed
 * to handle those situations.
 *
 * @EntityType(
 *   id = "repository",
 *   label = @Translation("Content repository"),
 *   controllers = {
 *     "storage" = "\Drupal\Core\Entity\EntityDatabaseStorage"
 *   },
 *   base_table = "repository",
 *   uri_callback = "multiversion_repository_uri",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "name",
 *     "label" = "name"
 *   }
 * )
 */
class Repository extends Entity implements RepositoryInterface {

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
