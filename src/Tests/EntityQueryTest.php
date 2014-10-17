<?php

namespace Drupal\multiversion\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the altered entity query functionality.
 *
 * @group multiversion
 */
class EntityQueryTest extends MultiversionWebTestBase {

  /**
   * The entity types to test.
   *
   * @var array
   */
  protected $entityTypes = array(
    'entity_test' => NULL,
    'entity_test_rev' => NULL,
    'entity_test_mul' => NULL,
    'entity_test_mulrev' => NULL,
  );

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $factory;

  public function setUp() {
    parent::setUp();

    $this->factory = \Drupal::service('entity.query');
  }

  public function testQuery() {
    foreach ($this->entityTypes as $entity_type_id => $info) {
      $entity = entity_create($entity_type_id);
      $entity->save();

      $results = $this->factory->get($entity_type_id)
        ->execute();
      $this->assertIdentical($results, array(1 => '1'), "Query without isNotDeleted on existing $entity_type_id returned expected result.");

      $results = $this->factory->get($entity_type_id)
        ->isNotDeleted()
        ->execute();
      $this->assertIdentical($results, array(1 => '1'), "Query with isNotDeleted on existing $entity_type_id returned expected result.");

      $results = $this->factory->get($entity_type_id)
        ->isDeleted()
        ->execute();
      $this->assertIdentical($results, array(), "Query with isDeleted on existing $entity_type_id returned expected result.");

      // Now delete the entity.
      $entity->delete();

      $results = $this->factory->get($entity_type_id)
        ->execute();
      $this->assertIdentical($results, array(), "Query without isNotDeleted on deleted $entity_type_id returned expected result.");

      $results = $this->factory->get($entity_type_id)
        ->isNotDeleted()
        ->execute();
      $this->assertIdentical($results, array(), "Query with isNotDeleted on deleted $entity_type_id returned expected result.");

      $results = $this->factory->get($entity_type_id)
        ->isDeleted()
        ->execute();
      $this->assertIdentical($results, array(2 => '1'), "Query with isDeleted on deleted $entity_type_id returned expected result.");
    }
  }
}
