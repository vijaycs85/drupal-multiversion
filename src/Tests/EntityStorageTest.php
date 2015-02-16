<?php

namespace Drupal\multiversion\Tests;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;

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
      'info' => array(),
      'data_table' => 'entity_test',
      'revision_table' => 'entity_test_revision',
      'id' => 'id',
    ),
    'entity_test_rev' => array(
      'info' => array(),
      'data_table' => 'entity_test_rev',
      'revision_table' => 'entity_test_rev_revision',
      'id' => 'id',
    ),
    'entity_test_mul' => array(
      'info' => array(),
      'data_table' => 'entity_test_mul_property_data',
      'revision_table' => 'entity_test_mul_field_revision',
      'id' => 'id',
    ),
    'entity_test_mulrev' => array(
      'info' => array(),
      'data_table' => 'entity_test_mulrev_property_data',
      'revision_table' => 'entity_test_mulrev_property_revision',
      'id' => 'id',
    ),
    'node' => array(
      'info' => array(
        'type' => 'article',
      ),
      'data_table' => 'node_field_data',
      'revision_table' => 'node_field_revision',
      'id' => 'nid',
    ),
    'taxonomy_term' => array(
      'info' => array(
        'name' => 'A term',
        'vid' => 123,
      ),
      'data_table' => 'taxonomy_term_field_data',
      'revision_table' => 'taxonomy_term_field_revision',
      'id' => 'tid',
    ),
    'comment' => array(
      'info' => array(
        'entity_type' => 'node',
        'field_name' => 'comment',
        'subject' => 'How much wood would a woodchuck chuck',
        'mail' => 'someone@example.com',
      ),
      'data_table' => 'comment_field_data',
      'revision_table' => 'comment_field_revision',
      'id' => 'cid',
    ),
    'block_content' =>  array(
      'info' => array(
        'info' => 'New block',
        'type' => 'basic',
      ),
      'data_table' => 'block_content_field_data',
      'revision_table' => 'block_content_field_revision',
      'id' => 'id',
    ),
  );

  public function setUp() {
    parent::setUp();

    foreach ($this->entityTypes as $entity_type_id => $info) {
      $this->entityTypes[$entity_type_id]['revision_id'] = $entity_type_id == 'node' ? 'vid' : 'revision_id';
      if ($entity_type_id == 'node') {
        $this->entityTypes[$entity_type_id]['name'] = 'title';
      }
      elseif ($entity_type_id == 'block_content') {
        $this->entityTypes[$entity_type_id]['name'] = 'info';
      }
      else {
        $this->entityTypes[$entity_type_id]['name'] = 'name';
      }
    }
  }

  public function testSaveAndLoad() {
    foreach ($this->entityTypes as $entity_type_id => $info) {
      $ids = array();
      $entity = entity_create($entity_type_id, $info['info']);
      $return = $entity->save();
      $this->assertEqual($return, SAVED_NEW, "$entity_type_id was saved.");

      $ids[] = $entity->id();
      $loaded = entity_load($entity_type_id, $ids[0]);
      $this->assertEqual($ids[0], $loaded->id(), "Single $entity_type_id was loaded.");

      // @todo Test loadEntityByUuid
      // Update and save a new revision.
      $entity->{$info['name']} = $this->randomMachineName();
      $entity->save();
      /** @var \Drupal\Core\Entity\ContentEntityInterface $revision */
      $revision = entity_revision_load($entity_type_id, 1);
      $this->assertTrue(($revision->getRevisionId() == 1 && !$revision->isDefaultRevision()), "Old revision of $entity_type_id was loaded.");

      if ($entity_type_id == 'block_content') {
        $info['info']['info'] = $this->randomMachineName();
      }
      $entity = entity_create($entity_type_id, $info['info']);
      $entity->save();
      $ids[] = $entity->id();

      $entities = entity_load_multiple($entity_type_id, $ids);
      $this->assertEqual(count($entities), 2, "Multiple $entity_type_id was loaded.");
    }
  }

  public function testDelete() {
    foreach ($this->entityTypes as $entity_type_id => $info) {
      $entity = entity_create($entity_type_id, $info['info']);
      $entity->save();
      $id = $entity->id();
      $revision_id = $entity->getRevisionId();
      entity_delete_multiple($entity_type_id, array($id));

      $record = db_select($info['revision_table'], 'e')
        ->fields('e')
        ->condition('e.' . $info['id'], $id)
        ->condition('e.' . $info['revision_id'], '2')
        ->execute()
        ->fetchObject();

      $this->assertEqual($record->_deleted, 1, "Deleted $entity_type_id is still stored but flagged as deleted");
      $entity = entity_load($entity_type_id, $id);
      $this->assertTrue(empty($entity), "Deleted $entity_type_id did not load with entity_load() function.");

      $entity = entity_load_deleted($entity_type_id, $id);
      $this->assertTrue(!empty($entity), "Deleted $entity_type_id loaded with entity_load_deleted() function.");
      $this->assertNotEqual($revision_id, $entity->getRevisionId(), "New revision was generated when deleting $entity_type_id.");

      $entities = entity_load_multiple_deleted($entity_type_id, array($id));
      $this->assertTrue(!empty($entities), "Deleted $entity_type_id loaded with entity_load_multiple_deleted() function.");
    }
  }

  public function testWorkspace() {
    foreach ($this->entityTypes as $entity_type_id => $info) {
      $entity = entity_create($entity_type_id, $info['info']);
      $entity->save();
      $entity_id = $entity->id();
      $this->assertEqual($entity->workspace->target_id, 'default', "The workspace reference was saved for $entity_type_id.");
      $record = db_select($info['data_table'], 'e')
        ->fields('e')
        ->condition('e.' . $info['id'], $entity->id())
        ->condition('e.' . $info['revision_id'], $entity->getRevisionId())
        ->execute()
        ->fetchObject();
      $this->assertEqual($record->workspace, 'default', "The workspace reference was stored for saved $entity_type_id.");

      $entity = entity_load($entity_type_id, $entity_id);
      $this->assertEqual($entity->workspace->target_id, 'default', "The workspace reference is retained for loaded $entity_type_id.");
      $record = db_select($info['data_table'], 'e')
        ->fields('e')
        ->condition('e.' . $info['id'], $entity->id())
        ->condition('e.' . $info['revision_id'], $entity->getRevisionId())
        ->execute()
        ->fetchObject();
      $this->assertEqual($record->workspace, 'default', "The workspace reference was stored for loaded $entity_type_id.");

      entity_delete_multiple($entity_type_id, array($entity_id));
      $entity = entity_load_deleted($entity_type_id, $entity_id);
      $this->assertEqual($entity->workspace->target_id, 'default', "The workspace reference is retained for deleted $entity_type_id.");
      $record = db_select($info['data_table'], 'e')
        ->fields('e')
        ->condition('e.' . $info['id'], $entity->id())
        ->condition('e.' . $info['revision_id'], $entity->getRevisionId())
        ->execute()
        ->fetchObject();
      $this->assertEqual($record->workspace, 'default', "The workspace reference was stored for deleted $entity_type_id.");
    }

    // Create a new workspace and switch to it.
    $workspace = entity_create('workspace', array('id' => $this->randomMachineName()));
    $this->workspaceManager->setActiveWorkspace($workspace);

    foreach ($this->entityTypes as $entity_type_id => $info) {
      if ($entity_type_id == 'block_content') {
        $info['info']['info'] = $this->randomMachineName();
      }
      $entity = entity_create($entity_type_id, $info['info']);
      $entity->save();
      $this->assertEqual($entity->workspace->target_id, $workspace->id(), "$entity_type_id was saved in new workspace.");
    }

    $uuids = array();
    $ids = array();
    foreach ($this->entityTypes as $entity_type_id => $info) {
      if ($entity_type_id == 'block_content') {
        $info['info']['info'] = $this->randomMachineName();
      }
      $entity = entity_create($entity_type_id, $info['info']);
      $entity->save();
      $uuids[$entity_type_id] = $entity->uuid();
      $ids[$entity_type_id] = $entity->id();

      $entity = entity_load($entity_type_id, $ids[$entity_type_id]);
      $this->assertTrue(!empty($entity), "$entity_type_id was loaded in the workspace it belongs to.");
      $entity = $this->entityManager->loadEntityByUuid($entity_type_id, $uuids[$entity_type_id]);
      $this->assertTrue(!empty($entity), "$entity_type_id was loaded by UUID in the workspace it belongs to.");
    }

    $this->multiversionManager->setActiveWorkspaceId('default');

    foreach ($this->entityTypes as $entity_type_id => $info) {
      $entity = entity_load($entity_type_id, $ids[$entity_type_id]);
      $this->assertTrue(empty($entity), "$entity_type_id was not loaded in a workspace it does not belongs to.");
      $entity = $this->entityManager->loadEntityByUuid($entity_type_id, $uuids[$entity_type_id]);
      $this->assertTrue(empty($entity), "$entity_type_id was not loaded by UUID in a workspace it does not belong to.");
    }
  }

}
