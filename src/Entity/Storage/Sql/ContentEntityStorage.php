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
    $query->condition("$alias._status", $this->storageStatus ?: ContentEntityStorageInterface::STATUS_AVAILABLE);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function updateStorageStatus(array $updates) {
    $table = $this->entityType->isTranslatable()
      ? $this->getRevisionDataTable()
      : $this->getRevisionTable();

    // @todo Figure out if we can run one query for all status updates.
    foreach ($updates as $status => $revision_ids) {
      $this->database->update($table)
        ->fields(array(
          '_status' => $status,
        ))
        ->condition($this->entityType->getKey('revision'), $revision_ids)
        ->execute();
    }
  }

}
