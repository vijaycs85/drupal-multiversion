<?php

namespace Drupal\multiversion\Tests;

abstract class FieldTestBase extends MultiversionWebTestBase {

  /**
   * The entity types to test.
   *
   * @var array
   */
  protected $entityTypes = array(
    'entity_test' => array(),
    'entity_test_rev' => array(),
    'entity_test_mul' => array(),
    'entity_test_mulrev' => array(),
    'node' => array('type' => 'article'),
    'taxonomy_term' => array(
      'name' => 'A term',
      'vid' => 123,
    ),
    'comment' => array(
      'entity_type' => 'node',
      'field_name' => 'comment',
      'subject' => 'How much wood would a woodchuck chuck',
      'mail' => 'someone@example.com',
    ),
  );

  /**
   * @var string
   */
  protected $fieldName;

  /**
   * @var mixed
   */
  protected $defaultValue;

  /**
   * @var string
   */
  protected $itemListClass = '\Drupal\Core\Field\FieldItemList';

  /**
   * @var string
   */
  protected $itemClass;

  public function testFieldBasics() {
    foreach ($this->entityTypes as $entity_type_id => $info) {
      $entity = entity_create($entity_type_id, $info);
      $this->assertTrue(is_a($entity->{$this->fieldName}, $this->itemListClass), "Field item list implements correct interface on created $entity_type_id.");
      $this->assertEqual($entity->{$this->fieldName}->count(), 0, "Field is created with no field items for $entity_type_id.");
      $this->assertEqual($entity->{$this->fieldName}->first(), NULL, "Field item list's isEmpty is correct on created $entity_type_id.");

      $entity->save();
      $entity_id = $entity->id();
      $entity = entity_load($entity_type_id, $entity_id);

      $this->assertFalse($entity->{$this->fieldName}->isEmpty(), "Field was attached on loaded $entity_type_id.");

      entity_delete_multiple($entity_type_id, array($entity_id));
      $entity = entity_load_deleted($entity_type_id, $entity_id);

      $this->assertFalse($entity->{$this->fieldName}->isEmpty(), "Field was attached on deleted $entity_type_id.");
    }
  }

}
