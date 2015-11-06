<?php

/**
 * @file
 * Contains \Drupal\multiversion\Tests\MigrationTest.
 */

namespace Drupal\multiversion\Tests;

use Drupal\multiversion\Entity\Query\QueryInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
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
      $count_before[$entity_type_id] = $count;
    }

    // Installing Multiversion will trigger the migration of existing content.
    \Drupal::service('module_installer')->install(array('multiversion'));

    $ids_after = array();
    // Now check that the previosuly created entities still exist, have the
    // right IDs and are multiversion enabled. That means profit. Big profit.
    foreach ($this->entityTypes as $entity_type_id => $values) {
      $storage = \Drupal::entityManager()->getStorage($entity_type_id);
      $query = $storage->getQuery();

      $this->assertTrue($storage instanceof ContentEntityStorageInterface, "$entity_type_id got the correct storage handler assigned.");
      $this->assertTrue($query instanceof QueryInterface, "$entity_type_id got the correct query handler assigned.");

      $ids_after[$entity_type_id] = $query->execute();
      $this->assertEqual($count_before[$entity_type_id], count($ids_after[$entity_type_id]), "All ${entity_type_id}s were migrated.");

      foreach ($ids_after[$entity_type_id] as $revision_id => $entity_id) {
        $entity = $storage->loadRevision($revision_id);
        $this->assertTrue(!empty($entity->_rev->value), "$entity_type_id got a revision hash");
        $this->assertEqual($entity->workspace->target_id, 'default', "$entity_type_id was created in the correct workspace.");
      }
    }
  }

}
