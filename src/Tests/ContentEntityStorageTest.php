<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the content entity storage controller.
 *
 * @group multiversion
 */
class ContentEntityStorageTest extends MultiversionWebTestBase {

  public function testSave() {
    $entity = entity_create('entity_test_rev');
    $return = $entity->save();
    $this->assertEqual($return, SAVED_NEW, 'Entity was saved.');
  }

  public function testLoad() {
    $ids = array();
    $entity = entity_create('entity_test_rev');
    $entity->save();
    $ids[] = $entity->id();

    $entity = entity_load('entity_test_rev', $ids[0]);
    $this->assertEqual($ids[0], $entity->id(), 'Single entity was loaded.');

    $entity = entity_create('entity_test_rev');
    $entity->save();
    $ids[] = $entity->id();

    $entities = entity_load_multiple('entity_test_rev', $ids);
    $this->assertEqual(count($entities), 2, 'Multiple entities was loaded.');
  }

  public function testDelete() {
    $entity = entity_create('entity_test_rev');
    $entity->save();
    $id = $entity->id();
    entity_delete_multiple('entity_test_rev', array($id));
 
    $entity = entity_load('entity_test_rev', $id);
    $this->assertTrue(empty($entity), 'Deleted entity did not load with entity_load() function.');
  }

  public function testLoadDeleted() {
    $entity = entity_create('entity_test_rev');
    $entity->save();
    $id = $entity->id();
    $revision_id = $entity->getRevisionId();
    entity_delete_multiple('entity_test_rev', array($id));
 
    $entity = entity_load_deleted('entity_test_rev', $id);
    $this->assertTrue(!empty($entity), 'Deleted entity loaded with entity_load_deleted() function.');
    $this->assertNotEqual($revision_id, $entity->getRevisionId(), 'New revision was generated when deleting entity.');
 
    $entities = entity_load_multiple_deleted('entity_test_rev', array($id));
    $this->assertTrue(!empty($entities), 'Deleted entity loaded with entity_load_multiple_deleted() function.'); 
  }
}
