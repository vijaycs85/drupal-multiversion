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
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $factory;

  public function setUp() {
    parent::setUp();

    $this->factory = \Drupal::service('entity.query');
  }

  public function testQuery() {
    $entity = entity_create('entity_test_mulrev');
    $entity->save();

    $results = $this->factory->get('entity_test_mulrev')
      ->execute();
    $this->assertIdentical($results, array(1 => '1'));

    $entity->delete();

    $results = $this->factory->get('entity_test_mulrev')
      ->execute();
    $this->assertIdentical($results, array());

    $results = $this->factory->get('entity_test_mulrev')
      ->isDeleted()
      ->execute();
    $this->assertIdentical($results, array(2 => '1'));
  }
}
