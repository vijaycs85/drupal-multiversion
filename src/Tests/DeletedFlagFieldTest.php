<?php

namespace Drupal\multiversion\Tests;

use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem;

/**
 * Test the creation of the Deleted Flag field.
 *
 * @group multiversion
 */
class DeletedFlagFieldTest extends MultiversionWebTestBase {

  public function testFieldCreation() {
    $entity = entity_create('entity_test_rev');
    $this->assertTrue($entity->_deleted->get(0) instanceof BooleanItem, 'Field item implements correct interface.');
    $this->assertEqual($entity->_deleted->count(), 1, 'Field is created with one field item.');
    $this->assertFalse($entity->_deleted->isEmpty(), 'Field is not created empty.');
    $this->assertFalse($entity->_deleted->get(0)->isEmpty(), 'First field item is not created empty.');
    $this->assertIdentical($entity->_deleted->get(0)->value, FALSE, 'Field item had correct default value.');
  }
}
