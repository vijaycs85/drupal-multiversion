<?php

namespace Drupal\multiversion\Tests;

use Drupal\simpletest\WebTestBase;

class UuidIndexTestBase extends WebTestBase {

  public static $modules = array('entity_test', 'multiversion');

  /**
   * @var \Drupal\multiversion\Entity\UuidIndex;
   */
  protected $uuidIndex;

  protected function setUp() {
    parent::setUp();
    $this->uuidIndex = \Drupal::service('entity.uuid_index');
  }
}
