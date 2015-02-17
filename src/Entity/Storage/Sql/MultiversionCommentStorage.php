<?php

/**
 * @file
 * Definition of Drupal\multiversion\Entity\Storage\Sql\MultiversionCommentStorage.
 */

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\comment\CommentStorage;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;

/**
 * Defines the controller class for comments.
 */
class MultiversionCommentStorage extends CommentStorage implements ContentEntityStorageInterface {

  use ContentEntityStorageTrait {
    delete AS deleteEntities;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    if (!empty($entities)) {
      $child_cids = $this->getChildCids($entities);
      if (!empty($child_cids)) {
        entity_delete_multiple('comment', $child_cids);
      }
    }
    $this->deleteEntities($entities);
  }

}
