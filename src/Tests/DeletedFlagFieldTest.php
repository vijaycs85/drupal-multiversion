<?php

namespace Drupal\multiversion\Tests;

use Drupal\multiversion\Plugin\Field\FieldType\DeletedFlagItem;
use Drupal\multiversion\Plugin\Field\FieldType\DeletedFlagItemList;

/**
 * Test the creation of the Deleted Flag field.
 *
 * @group multiversion
 */
class DeletedFlagFieldTest extends MultiversionWebTestBase {

  public function testFieldCreation() {
    $entity = entity_create('entity_test_rev');
    $this->assertTrue($entity->_deleted instanceof DeletedFlagItemList, 'Field was attached to content entity.');
    $this->assertTrue($entity->_deleted->get(0) instanceof DeletedFlagItem, 'Field item implements correct interface.');
    $this->assertEqual($entity->_deleted->count(), 1, 'Field is created with one field item.');
    $this->assertTrue(!$entity->_deleted->isEmpty(), 'Field is not created empty.');
    $this->assertTrue(!$entity->_deleted->get(0)->isEmpty(), 'First field item is not created empty.');
    $this->assertIdentical($entity->_deleted->get(0)->value, '0', 'Field item had correct default value.');
  }
}
