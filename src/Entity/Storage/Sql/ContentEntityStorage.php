<?php

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;

class ContentEntityStorage extends SqlContentEntityStorage implements ContentEntityStorageInterface {

  use ContentEntityStorageTrait;

  /**
   * {@inheritdoc}
   */
  public function getQueryServiceName() {
    return 'entity.query.sql.multiversion';
  }

  /**
   * {@inheritdoc}
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);
    $alias = 'revision';
    if ($this->entityType->isTranslatable()) {
      $table = $this->getRevisionDataTable();
      $alias = 'revision_data';
      if ($revision_id) {
        $query->join($table, $alias, "$alias.{$this->revisionKey} = revision.{$this->revisionKey} AND $alias.{$this->revisionKey} = :revisionId");
      }
      else {
        $query->join($table, $alias, "$alias.{$this->revisionKey} = revision.{$this->revisionKey}");
      }
    }
    $query->condition("$alias._deleted", (int) $this->isDeleted);
    // Entities in transaction can only be queried with the Entity Query API
    // and not by the storage handler itself.
    $query->condition("$alias._trx", 0);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function onTransactionCommit(array $revision_ids) {
    $table = $this->entityType->isTranslatable()
      ? $this->getRevisionDataTable()
      : $this->getRevisionTable();

    $this->database->update($table)
      ->fields(array(
        '_trx' => 0,
      ))
      ->condition($this->entityType->getKey('revision'), $revision_ids)
      ->execute();
  }

}
