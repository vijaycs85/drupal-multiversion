<?php

namespace Drupal\multiversion\Tests;

use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Test the workspace entity.
 *
 * @group multiversion
 */
class WorkspaceTest extends WebTestBase {

  public static $modules = array('multiversion');

  protected function setUp() {
    parent::setUp();
    //$this->assertTrue(db_table_exists('workspace'), 'Workspace storage table was created during install.');
  }

  public function testOperations() {
    $name = $this->randomMachineName();
    $entity = entity_create('workspace', array('id' => drupal_strtolower($name), 'name' => $name));

    $this->assertTrue($entity instanceof WorkspaceInterface, 'Workspace entity was created.');

    $entity->save();
    $this->assertEqual($entity->name(), $name, 'Workspace entity was saved.');
 
    $entity = entity_load('workspace', $entity->id());
    $this->assertEqual($entity->name(), $name, 'Workspace entity was loaded by ID.');

    $entity = \Drupal::entityManager()->loadEntityByUuid('workspace', $name);
    $this->assertEqual($entity->name(), $name, 'Workspace entity was loaded by UUID.');

    $this->assertEqual($entity->label(), $name, 'Label method returns the workspace name.');
    $this->assertEqual($entity->uuid(), $name, 'UUID method returns the workspace name.');
  }
}
