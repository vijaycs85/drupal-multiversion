<?php

namespace Drupal\multiversion\Entity\Sequence;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Component\Uuid\UuidInterface;

/**
 * @todo Let the storage class/interface also define it's schema and have
 * methods for ensuring/creating it.
 */
class DatabaseStorage extends SequenceStorageBase implements SequenceStorageInterface {

  const TABLE_PREFIX = 'repository__';

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * @var string
   */
  protected $table;

  /**
   * @var boolean
   */
  protected $schemaExists = FALSE;

  public function __construct(Connection $connection, UuidInterface $uuid_service, $repository_name) {
    $this->connection = $connection;
    $this->uuidService = $uuid_service;
    $this->table = self::TABLE_PREFIX . $repository_name;
  }

  /**
   * @todo Convert to static queries for better performance.
   */
  public function doRecord($uuid, $entity_type, $entity_id, $entity_revision_id, $deleted, $conflict) {
    $last_sequence_id = $this->connection->select($this->table, 't')
      ->fields('t', array('id'))
      ->condition('entity_type', $entity_type)
      ->condition('entity_id', $entity_id)
      // @todo Change condition to revision_id, to truly be MVCC and to avoid
      // race conditions.
      ->condition('next_id', 0)
      ->execute()
      ->fetchField();

    $this->connection->insert($this->table)
      ->fields(array(
        'uuid' => $uuid,
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'entity_revision_id' => $entity_revision_id,
        'deleted' => (int) $deleted,
        'conflict' => (int) $conflict,
        // @todo Modify schema
        //'parent_id' => $last_sequence_id,
        //'current' => 1,
      ))
      ->execute();

    // Connect the last and new sequence.
    if (!empty($last_sequence_id)) {
      // Fetch what became the new sequence ID.
      // @todo Remove when we have modified the schema.
      $new_sequence_id = $this->connection->select($this->table, 't')
        ->fields('t', array('id'))
        ->condition('uuid', $uuid)
        ->execute()
        ->fetchField();

      // Update the last sequence
      $this->connection->update($this->table)
        ->fields(array(
          'next_id' => $new_sequence_id
          // @todo When we have modified the schema, change above to:
          //'current' => 0,
        ))
        ->condition('id', $last_sequence_id)
        ->execute();
    }
  }

  public function ensureSchemaExists() {
    if (!$this->schemaExists) {
      try {
        $schema = $this->connection->schema();
        if (!$schema->tableExists($this->table)) {
          $schema->createTable($this->table, $this->schemaDefinition());
          $this->schemaExists = TRUE;
        }
      }
      // If another process has already created the cache table, attempting to
      // recreate it will throw an exception. In this case just catch the
      // exception and do nothing.
      catch (SchemaObjectExistsException $e) {
        return TRUE;
      }
    }
    return $this->schemaExists;
  }
}
