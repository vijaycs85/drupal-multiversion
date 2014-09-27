<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage as CoreSqlContentEntityStorage;

class SqlContentEntityStorage extends CoreSqlContentEntityStorage {

  use ContentEntityStorageTrait;

  public function getQueryServiceName() {
    return 'entity.query.sql.multiversion';
  }

  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);

    $translatable = $this->entityType->isTranslatable();

    if ($translatable) {
      $table = $this->getRevisionDataTable();
      $alias = 'revision_data';
      if ($revision_id) {
        $query->join($table, $alias, "$alias.{$this->revisionKey} = revision.{$this->revisionKey} AND $alias.{$this->revisionKey} = :revisionId");
      }
      else {
        $query->join($table, $alias, "$alias.{$this->revisionKey} = revision.{$this->revisionKey}");
      }
    }
    else {
      $alias = 'revision';
    }
    $query->condition("$alias._deleted", $this->loadDeleted ? 1 : 0);
    return $query;
  }

}
