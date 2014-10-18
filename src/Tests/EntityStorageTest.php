<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the content entity storage controller.
 *
 * @group multiversion
 */
class EntityStorageTest extends MultiversionWebTestBase {

  /**
   * The entity types to test.
   *
   * @var array
   */
  protected $entityTypes = array(
    'entity_test' => array(
      'revision_table' => 'entity_test_revision',
    ),
    /*'entity_test_rev' => array(
      'revision_table' => 'entity_test_rev_revision',
    ),
    'entity_test_mul' => array(
      'revision_table' => 'entity_test_mul_field_revision',
    ),
    'entity_test_mulrev' => array(
      'revision_table' => 'entity_test_mulrev_property_revision',
    ),*/
  );

  public function testSaveAndLoad() {
    foreach ($this->entityTypes as $entity_type_id => $info) {
      $ids = array();
      $entity = entity_create($entity_type_id);
      $return = $entity->save();
      $this->assertEqual($return, SAVED_NEW, "$entity_type_id was saved.");

      $ids[] = $entity->id();
      $loaded = entity_load($entity_type_id, $ids[0]);
      $this->assertEqual($ids[0], $loaded->id(), "Single $entity_type_id was loaded.");

      // Update and save a new revision.
      $entity->name->value = $this->randomMachineName();
      $entity->save();
      /** @var \Drupal\Core\Entity\ContentEntityInterface $revision */
      $revision = entity_revision_load($entity_type_id, 1);
      $this->assertTrue(($revision->getRevisionId() == 1 && !$revision->isDefaultRevision()), "Old revision of $entity_type_id was loaded.");

      $entity = entity_create($entity_type_id);
      $entity->save();
      $ids[] = $entity->id();

      $entities = entity_load_multiple($entity_type_id, $ids);
      $this->assertEqual(count($entities), 2, "Multiple $entity_type_id was loaded.");
    }
  }

  public function testDelete() {
    foreach ($this->entityTypes as $entity_type_id => $info) {
      $entity = entity_create($entity_type_id);
      $entity->save();
      $id = $entity->id();
      $revision_id = $entity->getRevisionId();
      entity_delete_multiple($entity_type_id, array($id));

      $record = db_select($info['revision_table'], 'e')
        ->fields('e')
        ->condition('e.id', $id)
        ->condition('e.revision_id', '2')
        ->execute()
        ->fetchObject();

      $this->assertEqual($record->_deleted, '1', "Deleted $entity_type_id is still stored but flagged as deleted");
      $entity = entity_load($entity_type_id, $id);
      $this->assertTrue(empty($entity), "Deleted $entity_type_id did not load with entity_load() function.");

      $entity = entity_load_deleted($entity_type_id, $id);
      $this->assertTrue(!empty($entity), "Deleted $entity_type_id loaded with entity_load_deleted() function.");
      $this->assertNotEqual($revision_id, $entity->getRevisionId(), "New revision was generated when deleting $entity_type_id.");

      $entities = entity_load_multiple_deleted($entity_type_id, array($id));
      $this->assertTrue(!empty($entities), "Deleted $entity_type_id loaded with entity_load_multiple_deleted() function.");
    }
  }

}
