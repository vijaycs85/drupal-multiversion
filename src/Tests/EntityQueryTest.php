<?php

namespace Drupal\multiversion\Tests;

use Drupal\simpletest\WebTestBase;

class EntityQueryTest extends WebTestBase {

  public static $modules = array('entity_test', 'multiversion');

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $factory;

  public static function getInfo() {
    return array(
      'name'  => 'Entity query',
      'description'  => "Test the altered entity query functionality.",
      'group' => 'Multiversion'
    );
  }

  public function setUp() {
    parent::setUp();

    // @todo: Remove once multiversion_install() is implemented.
    \Drupal::service('multiversion.manager')
      ->attachRequiredFields('entity_test_mulrev', 'entity_test_mulrev');

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
