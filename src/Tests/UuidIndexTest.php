<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the methods on the UuidIndex class.
 *
 * @group multiversion
 */
class UuidIndexTest extends MultiversionWebTestBase {

  public function testMethods() {
    $entity = entity_create('entity_test');
    $this->uuidIndex->add($entity);
    $entry = $this->uuidIndex->get($entity->uuid());
    $expected = array(
      'entity_type_id' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'revision_id' => $entity->getRevisionId(),
      'rev' => $entity->_revs_info->rev,
      'uuid' => $entity->uuid(),
      'status' => $entity->_deleted->value ? 'deleted' : 'available',
    );
    $this->assertIdentical($entry, $expected, 'Index entry was added and fetched.');

    $entities = array();
    $entities[] = entity_create('entity_test');
    $entities[] = entity_create('entity_test');
    $this->uuidIndex->addMultiple($entities);
    $expected = array(
      $entities[0]->uuid() => array(
        'entity_type_id' => $entities[0]->getEntityTypeId(),
        'entity_id' => $entities[0]->id(),
        'revision_id' => $entities[0]->getRevisionId(),
        'rev' => $entities[0]->_revs_info->rev,
        'uuid' => $entities[0]->uuid(),
        'status' => $entities[0]->_deleted->value ? 'deleted' : 'available',
      ),
      $entities[1]->uuid() => array(
        'entity_type_id' => $entities[1]->getEntityTypeId(),
        'entity_id' => $entities[1]->id(),
        'revision_id' => $entities[1]->getRevisionId(),
        'rev' => $entities[1]->_revs_info->rev,
        'uuid' => $entities[1]->uuid(),
        'status' => $entities[1]->_deleted->value ? 'deleted' : 'available',
      ),
    );
    $entries = $this->uuidIndex->getMultiple(array($entities[0]->uuid(), $entities[1]->uuid()));
    $this->assertIdentical($entries, $expected, 'Multiple index entries was added and fetched.');

    // Create a new workspaces and query those.
    $ws1 = $this->randomMachineName();
    entity_create('workspace', array('id' => $ws1));
    $ws2 = $this->randomMachineName();
    entity_create('workspace', array('id' => $ws2));

    $entity = entity_create('entity_test');

    $this->uuidIndex->useWorkspace($ws1)->add($entity);
    $entry = $this->uuidIndex
      ->useWorkspace($ws2)
      ->get($entity->uuid());
    $this->assertTrue(empty($entry), 'New workspace is empty');

    $this->uuidIndex
      ->useWorkspace($ws2)
      ->add($entity);

    $entry = $this->uuidIndex
      ->useWorkspace($ws2)
      ->get($entity->uuid());
    $expected = array(
      'entity_type_id' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'revision_id' => $entity->getRevisionId(),
      'rev' => $entity->_revs_info->rev,
      'uuid' => $entity->uuid(),
      'status' => $entity->_deleted->value ? 'deleted' : 'available',
    );
    $this->assertIdentical($entry, $expected, 'Entry was added and fetched from new workspace.');
  }

}
