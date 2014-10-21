<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the hooks invoking the UuidIndex class.
 *
 * @group multiversion
 */
class UuidIndexHooksTest extends MultiversionWebTestBase {

  public function testEntityHooks() {
    $keys = $this->uuidIndex->get('foo');
    $this->assertTrue(empty($keys), 'Empty array was returned when fetching non-existing UUID.');

    $entity = entity_create('entity_test');
    $entity->save();
    $keys = $this->uuidIndex->get($entity->uuid());
    $this->assertIdentical(
      $keys,
      array(
        'entity_type' => $entity->getEntityTypeId(),
        'entity_id' => $entity->id(),
        'revision_id' => $entity->getRevisionId(),
        'local_seq' => $entity->_local_seq->value,
        'rev' => $entity->_revs_info->rev,
      ),
      'Index entry was created by insert hook.'
    );

    entity_delete_multiple('entity_test', array($entity->id()));
    $keys = $this->uuidIndex->get($entity->uuid());
    $this->assertTrue(!empty($keys), 'Index entry should not be removed when an entity is deleted.');

    /** @var \Drupal\multiversion\Entity\Compaction\CompactionManagerInterface $compaction */
    $compaction = \Drupal::service('entity.compaction.manager');
    $compaction->compact();

    $keys = $this->uuidIndex->get($entity->uuid());
    $this->assertTrue(empty($keys), 'Index entry was removed when an entity was purged.');
  }
}
