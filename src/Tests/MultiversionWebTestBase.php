<?php

namespace Drupal\multiversion\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * @todo Change to extending DrupalUnitTestBase to increase performance. 
 */
abstract class MultiversionWebTestBase extends WebTestBase {

  public static $modules = array('entity_test', 'multiversion');

  protected function setUp() {
    parent::setUp();

    // @todo: Remove once multiversion_install() is implemented.
    \Drupal::service('multiversion.manager')
      ->attachRequiredFields('entity_test_rev', 'entity_test_rev');
  }
}
