<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the compaction functionality.
 *
 * @group multiversion
 */
class CompactionTest extends MultiversionWebTestBase {

  public function testCompact() {
    $entity = entity_create('entity_test');
    $entity->save();
    entity_delete_multiple('entity_test', array($entity->id()));

    $loaded = entity_load_deleted('entity_test', $entity->id());
    $this->assertTrue(!empty($loaded), 'Deleted entity was loaded before purged.');

    /** @var \Drupal\multiversion\Entity\Compaction\CompactionManagerInterface $compaction */
    $compaction = \Drupal::service('entity.compaction.manager');
    $count = $compaction->compact();

    $this->assertEqual($count, 1, 'Correct count of entities was purged.');

    $loaded = entity_load_deleted('entity_test', $entity->id());
    $this->assertTrue(empty($loaded), 'Deleted entity was not loaded after purged.');

    $ids = array();
    $entity = entity_create('entity_test_rev');
    $entity->save();
    $ids[] = $entity->id();
    $entity = entity_create('entity_test_rev');
    $entity->save();
    $ids[] = $entity->id();
    entity_delete_multiple('entity_test_rev', $ids);

    $ids = array();
    $entity = entity_create('entity_test_mul');
    $entity->save();
    $ids[] = $entity->id();
    entity_delete_multiple('entity_test_mul', $ids);

    $count = $compaction->compact();
    $this->assertEqual($count, 3, 'Correct count of entities was purged when purging multiple entity types at the same time.');
  }
}
