<?php

namespace Drupal\multiversion\Tests;

use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;

/**
 * Test the creation of the Storage Status Flag field.
 *
 * @group multiversion
 */
class StorageStatusFlagFieldTest extends MultiversionWebTestBase {

  public function testField() {
    $entity = entity_create('entity_test_rev');
    $this->assertTrue($entity->_status->get(0) instanceof IntegerItem, 'Field item implements correct interface.');
    $this->assertEqual($entity->_status->count(), 1, 'Field is created with one field item.');
    $this->assertFalse($entity->_status->isEmpty(), 'Field is not created empty.');
    $this->assertFalse($entity->_status->get(0)->isEmpty(), 'First field item is not created empty.');
    $this->assertIdentical($entity->_status->get(0)->value, ContentEntityStorageInterface::STATUS_AVAILABLE, 'Field item had correct default value.');

    $entity->save();
    $entity_type = $entity->getEntityTypeId();
    $entity_id = $entity->id();

    entity_delete_multiple($entity_type, array($entity_id));

    $deleted = entity_load_deleted($entity_type, $entity_id);
    $this->assertEqual($deleted->_status->get(0)->value, ContentEntityStorageInterface::STATUS_DELETED, 'Storage field was set to deleted when entity was deleted.');
  }
}
