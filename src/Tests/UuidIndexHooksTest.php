<?php

namespace Drupal\multiversion\Tests;

class UuidIndexHooksTest extends UuidIndexTestBase {

  public static function getInfo() {
    return array(
      'name'  => 'UUID index hooks',
      'description'  => 'Test the hooks invoking the UuidIndex class.',
      'group' => 'Multiversion'
    );
  }

  public function testInstallHook() {
    $user = entity_load('user', 1);
    $entry = $this->uuidIndex->get($user->uuid());
    $this->assertIdentical($entry, array('entity_type' => 'user', 'entity_id' => $user->id()), 'Index entries was generated for entities created prior to installing the UUID module.');
  }

  public function testEntityHooks() {
    $keys = $this->uuidIndex->get('foo');
    $this->assertTrue(empty($keys), 'Empty array was returned when fetching non-existing UUID.');

    $entity = entity_create('entity_test');
    $entity->save();
    $keys = $this->uuidIndex->get($entity->uuid());
    $this->assertIdentical($keys, array('entity_type' => $entity->getEntityTypeId(), 'entity_id' => $entity->id()), 'Index entry was created by insert hook.');

    entity_delete_multiple('entity_test', array($entity->id()));
    $keys = $this->uuidIndex->get($entity->uuid());
    $this->assertTrue(empty($keys), 'Index entry was deleted by delete hook.');
  }
}
