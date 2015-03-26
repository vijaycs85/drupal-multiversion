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
    'entity_test' => array(),
    'entity_test_rev' => array(),
    'entity_test_mul' => array(),
    'entity_test_mulrev' => array(),
    'node' => array('type' => 'article'),
    'taxonomy_term' => array(
      'name' => 'A term',
      'vid' => 123,
    ),
    'comment' => array(
      'entity_type' => 'node',
      'field_name' => 'comment',
      'subject' => 'How much wood would a woodchuck chuck',
      'mail' => 'someone@example.com',
    ),
    'user' => array(
      'name' => 'User',
      'mail' => 'user@example.com',
      'status' => 1,
    ),
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
      $entity_type = $this->entityManager->getDefinition($entity_type_id);

      $entity = entity_create($entity_type_id, $info);
      $entity->save();

      $expected_results = $entity_type_id == 'user' ? array(1 => '0', 2 => '1', 3 => '2') : array(1 => '1');
      $results = $this->factory->get($entity_type_id)
        ->execute();
      $this->assertIdentical($results, $expected_results, "Query without isNotDeleted on existing $entity_type_id returned expected result.");

      $results = $this->factory->get($entity_type_id)
        ->isNotDeleted()
        ->execute();
      $this->assertIdentical($results, $expected_results, "Query with isNotDeleted on existing $entity_type_id returned expected result.");

      $results = $this->factory->get($entity_type_id)
        ->isDeleted()
        ->execute();
      $this->assertIdentical($results, array(), "Query with isDeleted on existing $entity_type_id returned expected result.");

      $revision = $entity_type_id == 'user' ? 3 : 1;
      $expected_results = $entity_type_id == 'user' ? array( 3 => '2') : array(1 => '1');
      $results = $this->factory->get($entity_type_id)
        ->condition($entity_type->getKey('revision'), $revision)
        ->execute();
      $this->assertIdentical($results, $expected_results, "Revision query on existing $entity_type_id returned expected result.");

      // Now delete the entity.
      $entity->delete();

      $expected_results = $entity_type_id == 'user' ? array(1 => '0', 2 => '1') : array();
      $results = $this->factory->get($entity_type_id)
        ->execute();
      $this->assertIdentical($results, $expected_results, "Query without isNotDeleted on deleted $entity_type_id returned expected result.");

      $results = $this->factory->get($entity_type_id)
        ->isNotDeleted()
        ->execute();
      $this->assertIdentical($results, $expected_results, "Query with isNotDeleted on deleted $entity_type_id returned expected result.");

      $expected_results = $entity_type_id == 'user' ? array(4 => '2') : array(2 => '1');
      $results = $this->factory->get($entity_type_id)
        ->isDeleted()
        ->execute();
      $this->assertIdentical($results, $expected_results, "Query with isDeleted on deleted $entity_type_id returned expected result.");

      $results = $this->factory->get($entity_type_id)
        ->condition($entity_type->getKey('revision'), 2)
        ->execute();
      $this->assertIdentical($results, array(2 => '1'), "Revision query on deleted $entity_type_id returned expected result.");
    }
  }
}
