<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the methods on the UuidIndex class.
 *
 * @group multiversion
 */
class UuidIndexMethodsTest extends UuidIndexTestBase {

  public function testMethods() {
    $entity = entity_create('entity_test');
    $this->uuidIndex->add($entity);
    $entry = $this->uuidIndex->get($entity->uuid());
    $expected = array(
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id()
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
      ),
      $entities[1]->uuid() => array(
        'entity_type' => $entities[1]->getEntityTypeId(),
        'entity_id' => $entities[1]->id(),
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
