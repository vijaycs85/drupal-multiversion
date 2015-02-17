<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\Storage\Sql\MultiversionNodeStorage.
 */

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;
use Drupal\node\NodeStorage;

/**
 * Defines the controller class for nodes.
 */
class MultiversionNodeStorage extends NodeStorage implements ContentEntityStorageInterface {

  use ContentEntityStorageTrait;

}
