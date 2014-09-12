<?php

namespace Drupal\multiversion\Entity\Sequence;

use Drupal\Core\Database\Connection;
use Drupal\Component\Uuid\UuidInterface;

class DatabaseStorageFactory extends SequenceFactoryBase {

  public function __construct(Connection $connection, UuidInterface $uuid_service) {
    $this->connection = $connection;
    $this->uuidService = $uuid_service;
  }

  public function workspace($workspace_name = NULL) {
    return new DatabaseStorage($this->connection, $this->uuidService, $this->resolveWorkspaceName($workspace_name));
  }
}
