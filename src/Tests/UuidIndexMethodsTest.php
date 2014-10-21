<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the methods on the UuidIndex class.
 *
 * @group multiversion
 */
class UuidIndexMethodsTest extends MultiversionWebTestBase {

  public function testMethods() {
    $entity = entity_create('entity_test');
    $this->uuidIndex->add($entity);
    $entry = $this->uuidIndex->get($entity->uuid());
    $expected = array(
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'revision_id' => $entity->getRevisionId(),
      'local_seq' => $entity->_local_seq->value,
      'rev' => $entity->_revs_info->rev,
    );
    $this->assertIdentical($entry, $expected, 'Index entry was added and fetched.');

    $entities = array();
    $entities[] = entity_create('entity_test');
    $entities[] = entity_create('entity_test');
    $this->uuidIndex->addMultiple($entities);
    $expected = array(
      $entities[0]->uuid() => array(
        'entity_type' => $entities[0]->getEntityTypeId(),
        'entity_id' => $entities[0]->id(),
        'revision_id' => $entities[0]->getRevisionId(),
        'local_seq' => $entities[0]->_local_seq->value,
        'rev' => $entities[0]->_revs_info->rev,
      ),
      $entities[1]->uuid() => array(
        'entity_type' => $entities[1]->getEntityTypeId(),
        'entity_id' => $entities[1]->id(),
        'revision_id' => $entities[1]->getRevisionId(),
        'local_seq' => $entities[1]->_local_seq->value,
        'rev' => $entities[1]->_revs_info->rev,
      ),
    );
    $entries = $this->uuidIndex->getMultiple(array($entities[0]->uuid(), $entities[1]->uuid()));
    $this->assertIdentical($entries, $expected, 'Multiple index entries was added and fetched.');

    $this->uuidIndex->delete($entities[0]->uuid());
    $entry = $this->uuidIndex->get($entities[0]->uuid());
    $this->assertTrue(empty($entry), 'Index entry was deleted.');

    $this->uuidIndex->deleteAll();
    $entry = $this->uuidIndex->get($entities[1]->uuid());
    $this->assertTrue(empty($entry), 'All index entries was deleted.');
  }
}
