<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the creation and operation of the Revision field.
 *
 * @group multiversion
 */
class RevisionFieldTest extends FieldTestBase {

  /**
   * {@inheritdoc}
   */
  protected $fieldName = '_rev';

  /**
   * {@inheritdoc}
   */
  protected $createdEmpty = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $itemClass = '\Drupal\multiversion\Plugin\Field\FieldType\RevisionItem';

  public function testFieldOperations() {
    foreach ($this->entityTypes as $entity_type_id => $info) {
      $entity = entity_create($entity_type_id, $info);

      // Test normal save operations.

      $this->assertTrue($entity->{$this->fieldName}->new_edit, 'New edit flag is TRUE after creation.');

      $revisions = $entity->{$this->fieldName}->revisions;
      $this->assertTrue((is_array($revisions) && empty($revisions)), 'Revisions property is empty after creation.');

      $token = $entity->{$this->fieldName}->value;
      list($i, $hash) = explode('-', $token);
      $this->assertTrue($i == 0, 'Revision index was 0 after creation.');
      $entity->save();
      $token = $entity->{$this->fieldName}->value;
      list($i, $hash) = explode('-', $token);
      $this->assertTrue($i == 0, 'Revision index was 1 after first save.');

      // Simulate the input from a replication.

      $entity->{$this->fieldName}->new_edit = FALSE;
      $entity->{$this->fieldName}->revisions = array($hash);
    }
  }
}
