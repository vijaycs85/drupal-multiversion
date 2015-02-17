<?php

/**
 * @file
 * Definition of Drupal\multiversion\Entity\Storage\Sql\CommentStorage.
 */

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;
use Drupal\comment\CommentStorage as CoreCommentStorage;

/**
 * Defines the controller class for comments.
 */
class CommentStorage extends CoreCommentStorage implements ContentEntityStorageInterface {

  use ContentEntityStorageTrait {
    delete as deleteEntities;
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
