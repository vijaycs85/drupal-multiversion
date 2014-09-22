<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage as CoreSqlContentEntityStorage;
use Drupal\multiversion\Entity\Sequence\DatabaseStorage;

class SqlContentEntityStorage extends CoreSqlContentEntityStorage {

  use SqlContentEntityStorageTrait;

  public function getQueryServiceName() {
    return 'entity.query.sql.multiversion';
  }

  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);
    $table = $revision_id ? 'revision' : 'base';
    $query->condition("$table._deleted", $this->loadDeleted ? 1 : 0);
    return $query;
  }
}
