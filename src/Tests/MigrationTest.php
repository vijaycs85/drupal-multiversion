<?php

/**
 * @file
 * Contains \Drupal\multiversion\Tests\MigrationTest.
 */

namespace Drupal\multiversion\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the MigrationTest class.
 *
 * @group multiversion
 */
class MigrationTest extends WebTestBase {

  protected $strictConfigSchema = FALSE;

  /**
   * @var array
   */
  protected $entityTypes = array(
    'entity_test' => array(),
    'entity_test_rev' => array(),
    'entity_test_mul' => array(),
    'entity_test_mulrev' => array(),
    'user' => array(),
    'node' => array('type' => 'article', 'title' => 'foo')
  );

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'entity_test',
    'node'
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
  }

  public function testEnableWithExistingContent() {
    $before = array();

    foreach ($this->entityTypes as $entity_type_id => $values) {
      $storage = \Drupal::entityManager()->getStorage($entity_type_id);

      if ($entity_type_id == 'user') {
        $this->createUser(array('administer nodes'));
        // There should now be 3 users in total, including the initial anonymous
        // and admin users.
        $count = 3;
      }
      // Generic handling for the rest of the entity types.
      else {
        $count = 2;
        for ($i = 0; $i < $count; $i++) {
          $storage->create($values)->save();
        }
      }

      $before[$entity_type_id] = $storage->loadMultiple();
      $this->assertEqual($count, count($before[$entity_type_id]), "There are $count ${entity_type_id}s before migration.");
    }

    // Installing Multiversion will trigger the migration of existing content.
    \Drupal::service('module_installer')->install(array('multiversion'));

    $after = array();
    // Now check that the previosuly created entities still exist, have the
    // right IDs and are multiversion enabled. That means profit. Big profit.
    foreach ($this->entityTypes as $entity_type_id => $values) {
      // @todo: Seems impossible to get $this->entityManager to return an
      // up-to-date storage handler right after the installation to use here.
      $after[$entity_type_id] = \Drupal::entityManager()->getStorage($entity_type_id)->loadMultiple();
      $this->assertEqual(count($before[$entity_type_id]), count($after[$entity_type_id]), "All ${entity_type_id}s were migrated.");

      foreach ($after[$entity_type_id] as $entity_id => $entity) {
        $this->assertEqual($entity->uuid(), $before[$entity_type_id][$entity_id]->uuid(), "Entity ID mapped correctly for $entity_type_id $entity_id");
        $this->assertTrue(!empty($entity->_rev->value), "$entity_type_id $entity_id has a revision hash.");
      }
    }
  }

}
