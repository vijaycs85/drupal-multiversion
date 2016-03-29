<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\Storage\Sql\UserStorageSchema.
 */

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\entity_storage_migrate\Entity\Storage\ContentEntityStorageSchemaTrait;
use Drupal\user\UserStorageSchema as CoreUserStorageSchema;

/**
 * Storage schema handler for users.
 */
class UserStorageSchema extends CoreUserStorageSchema {

  use ContentEntityStorageSchemaTrait;

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    // Add workspace to the unique index as well.
    $schema['users_field_data']['unique keys']['user__name'] = ['name', 'langcode', 'workspace'];

    // @todo: Optimize indexes with the workspace field.
    return $schema;
  }
}
