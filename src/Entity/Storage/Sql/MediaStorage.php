<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\Storage\Sql\MediaStorage.
 */

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;
use Drupal\media_entity\MediaStorage as CoreMediaStorage;

/**
 * Storage handler for files.
 */
class MediaStorage extends CoreMediaStorage implements ContentEntityStorageInterface {

  use ContentEntityStorageTrait;

}