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

    $revision_alias = 'revision';
    if ($this->entityType->isTranslatable()) {
      // Join the revision data table in order to set the delete condition.
      $revision_table = $this->getRevisionDataTable();
      $revision_alias = 'revision_data';
      if ($revision_id) {
        $query->join($revision_table, $revision_alias, "$revision_alias.{$this->revisionKey} = revision.{$this->revisionKey} AND $revision_alias.{$this->revisionKey} = :revisionId");
      }
      else {
        $query->join($revision_table, $revision_alias, "$revision_alias.{$this->revisionKey} = revision.{$this->revisionKey}");
      }
    }
    $query->condition("$revision_alias._deleted", (int) $this->isDeleted);
    // Entities in other workspaces than the active one can only be queried with
    // the Entity Query API and not by the storage handler itself.
    $query->condition("$revision_alias._workspace", $this->getActiveWorkspaceId());
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function onTransactionCommit(array $revision_ids) {
    foreach ($revision_ids as $id => $revision) {
      $this->database->update($this->baseTable)
        ->fields(array(
            $this->revisionKey => $revision,
          ))
        ->condition($this->idKey, $id)
        ->execute();
    }
  }

}
