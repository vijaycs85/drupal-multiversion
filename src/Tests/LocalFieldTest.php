<?php

namespace Drupal\multiversion\Tests;

use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem;

/**
 * Test the creation and operation of the Local flag field.
 *
 * @group multiversion
 */
class LocalFieldTest extends MultiversionWebTestBase {

  public function testFieldCreation() {
    $entity = entity_create('entity_test_rev');
    $this->assertTrue($entity->_local->get(0) instanceof BooleanItem, 'Field item implements correct interface.');
    $this->assertEqual($entity->_local->count(), 1, 'Field is created with one field item.');
    $this->assertFalse($entity->_local->isEmpty(), 'Field is not created empty.');
    $this->assertFalse($entity->_local->get(0)->isEmpty(), 'First field item is not created empty.');
    $this->assertIdentical($entity->_local->get(0)->value, FALSE, 'Field item had correct default value.');
  }

}
