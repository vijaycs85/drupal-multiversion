<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\Storage\Sql\NodeStorage.
 */

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;

/**
 * Defines the controller class for nodes.
 */
class NodeStorage extends ContentEntityStorage implements NodeStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(NodeInterface $node) {
    return $this->database->query(
      'SELECT vid FROM {node_revision} WHERE nid=:nid ORDER BY vid',
      array(':nid' => $node->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {node_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(NodeInterface $node) {
    return $this->database->query('SELECT COUNT(*) FROM {node_field_revision} WHERE nid = :nid AND default_langcode = 1', array(':nid' => $node->id()))->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function updateType($old_type, $new_type) {
    return $this->database->update('node')
      ->fields(array('type' => $new_type))
      ->condition('type', $old_type)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('node_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
