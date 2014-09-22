<?php

namespace Drupal\multiversion\Entity\Sequence;

use Drupal\Core\Entity\ContentEntityInterface;

abstract class SequenceStorageBase implements SequenceStorageInterface {

  /**
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param type $deleted
   */
  public function record(ContentEntityInterface $entity, $conflict = FALSE) {
    $try_again = FALSE;
    $parameters = array($this->uuidService->generate(), $entity->getEntityTypeId(), $entity->id(), $entity->getRevisionId(), $entity->_deleted->value, $conflict);
    try {
      call_user_func_array(array($this, 'doRecord'), $parameters);
    }
    catch (\Exception $e) {
      // If there was an exception, try to create the schema.
      if (!$try_again = $this->ensureSchemaExists()) {
        // If the exception happened for other reason than the missing schema
        // we propagate the exception.
        throw $e;
      }
    }
    if ($try_again) {
      call_user_func_array(array($this, 'doRecord'), $parameters);
    }
  }

  abstract public function doRecord($uuid, $entity_type, $entity_id, $entity_revision_id, $deleted, $conflict);

  public function schemaDefinition() {
    return array(
      'description' => 'Stores all entity sequences.',
      'fields' => array(
        'id' => array(
          'description' => 'The primary identifier of the sequence.',
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'uuid' => array(
          'description' => 'The UUID of the sequence.',
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
        ),
        // @todo: Change to parent_id
        'next_id' => array(
          'description' => 'The next sequence identifier for the entity.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => FALSE,
          'default' => 0,
        ),
        'entity_type' => array(
          'type' => 'varchar',
          'not null' => TRUE,
          'default' => 'node',
          'length' => 255,
          'description' => 'The entity_type of the entity this sequence concerns.',
        ),
        'entity_id' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The entity_id of the entity this sequence concerns.',
        ),
        'entity_revision_id' => array(
          'description' => 'The revision_id of the entity this sequence concerns.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'deleted' => array(
          'description' => 'Boolean indicating whether the revision is deleted or not.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
        'conflict' => array(
          'description' => 'Boolean indicating whether the revision introduced a conflict or not.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
      'primary key' => array('id'),
      'indexes' => array(
        'next_id' => array('next_id'),
        'entity_type' => array('entity_type'),
        'entity_id' => array('entity_id'),
        'entity_revision_id' => array('entity_revision_id'),
      ),
    );
  }
}
