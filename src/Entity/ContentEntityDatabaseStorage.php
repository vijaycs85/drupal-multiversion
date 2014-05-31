<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\ContentEntityDatabaseStorage as CoreContentEntityDatabaseStorage;
use Drupal\multiversion\Entity\Sequence\DatabaseStorage;

class ContentEntityDatabaseStorage extends CoreContentEntityDatabaseStorage {

  use ContentEntityStorageTrait;

  public function getQueryServiceName() {
    return 'entity.query.sql.multiversion';
  }

  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);

    // @todo Generate the table names with self::_fieldTableName()
    if ($revision_id) {
      $table = $this->entityType->id() . '_revision___deleted';
      $query->join($table, 't', "t.entity_id = base.{$this->idKey} AND t.revision_id = :revisionId", array(':revisionId' => $revision_id));
    }
    else {
      $table = $this->entityType->id() . '___deleted';
      $query->join($table, 't', "t.entity_id = base.{$this->idKey}");
    }
    $query->condition('t._deleted_value', $this->loadDeleted ? 1 : 0);
    return $query;
  }
}
