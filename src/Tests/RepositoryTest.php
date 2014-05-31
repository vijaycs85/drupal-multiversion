<?php

namespace Drupal\multiversion\Tests;

use Drupal\multiversion\Entity\RepositoryInterface;
use Drupal\simpletest\WebTestBase;

class RepositoryTest extends WebTestBase {

  public static $modules = array('multiversion');

  public static function getInfo() {
    return array(
      'name'  => 'Content repository',
      'description'  => "Test the repository entity.",
      'group' => 'Multiversion'
    );
  }

  protected function setUp() {
    parent::setUp();
    $this->assertTrue(db_table_exists('repository'), 'Repository storage table was created during install.');
  }

  public function testOperations() {
    $name = $this->randomName();
    $entity = entity_create('repository', array('name' => $name));
    $this->assertTrue($entity instanceof RepositoryInterface, 'Repository entity was created.');

    $entity->save();
    $this->assertEqual($entity->name(), $name, 'Repository entity was saved.');
 
    $entity = entity_load('repository', $entity->id());
    $this->assertEqual($entity->name(), $name, 'Repository entity was loaded by ID.');

    $entity = entity_load_by_uuid('repository', $name);
    $this->assertEqual($entity->name(), $name, 'Repository entity was loaded by UUID.');

    $this->assertEqual($entity->label(), $name, 'Label method returns the repository name.');
    $this->assertEqual($entity->uuid(), $name, 'UUID method returns the repository name.');
  }
}
