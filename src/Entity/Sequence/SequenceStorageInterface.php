<?php

namespace Drupal\multiversion\Entity\Sequence;

use Drupal\Core\Entity\ContentEntityInterface;

interface SequenceStorageInterface {

  public function record(ContentEntityInterface $entity, $conflict = FALSE);

  public function doRecord($uuid, $entity_type, $entity_id, $entity_revision_id, $deleted, $conflict);

  public function ensureSchemaExists();

  public function schemaDefinition();
}
