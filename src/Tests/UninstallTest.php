<?php

/**
 * @file
 * Contains \Drupal\multiversion\Tests\UninstallTest.
 */

namespace Drupal\multiversion\Tests;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Test the UninstallTest class.
 *
 * @group multiversion
 */
class UninstallTest extends WebTestBase {

  protected $strictConfigSchema = FALSE;

  /**
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $entityDefinitionUpdateManager;

  /**
   * @var array
   */
  protected $entityTypes = [
//    'entity_test' => [],
//    'entity_test_rev' => [],
//    'entity_test_mul' => [],
//    'entity_test_mulrev' => [],
    'user' => [],
    'node' => ['type' => 'article', 'title' => 'foo'],
    'taxonomy_term' => ['name' => 'A term', 'vid' => 123],
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    //'entity_test',
    'node',
    'comment',
    'menu_link_content',
    'block_content',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->moduleInstaller = \Drupal::service('module_installer');

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    $this->drupalLogin($this->rootUser);
  }

  public function testDisableWithExistingContent() {
    // Install Multiversion.
    $this->moduleInstaller->install(['multiversion']);

    // Check if all updates have been applied.
    $this->assertFalse(\Drupal::service('entity.definition_update_manager')->needsUpdates(), 'All compatible entity types have been updated.');

    foreach ($this->entityTypes as $entity_type_id => $values) {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);

      if ($entity_type_id == 'user') {
        $this->createUser(['administer nodes']);
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

    drupal_flush_all_caches();

    // Now uninstall Multiversion.
    $this->drupalPostAjaxForm('/admin/config/multiversion-uninstall', [], ['op' => t('Uninstall')]);
    $this->assertFalse(\Drupal::service('entity.definition_update_manager')->needsUpdates(), 'There are not new updates to apply.');

    $manager = \Drupal::entityTypeManager();
    $manager->clearCachedDefinitions();

    $ids_after = [];
    // Now check that the previously created entities still exist, have the
    // right IDs and are multiversion enabled. That means profit. Big profit.
    foreach ($this->entityTypes as $entity_type_id => $values) {
      $storage = $manager->getStorage($entity_type_id);

      $this->assertTrue($storage instanceof ContentEntityStorageInterface, "$entity_type_id got the correct storage handler assigned.");
      $this->assertTrue($storage->getQuery() instanceof QueryInterface, "$entity_type_id got the correct query handler assigned.");

      $ids_after[$entity_type_id] = $storage->getQuery()->execute();
      $this->assertEqual($count_before[$entity_type_id], count($ids_after[$entity_type_id]), "All ${entity_type_id}s were migrated.");
    }
  }

}
