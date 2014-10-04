<?php

namespace Drupal\multiversion\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * @todo Change to extending DrupalUnitTestBase to increase performance. 
 */
abstract class MultiversionWebTestBase extends WebTestBase {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The multiversion manager.
   *
   * @var \Drupal\multiversion\MultiversionManagerInterface
   */
  protected $multiversionManager;

  /**
   * The entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $entityDefinitionUpdateManager;

  public static $modules = array('entity_test', 'multiversion');

  protected function setUp() {
    parent::setUp();

    $this->multiversionManager = $this->container->get('multiversion.manager');

    $this->entityDefinitionUpdateManager = $this->container->get('entity.definition_update_manager')
      ->applyUpdates();
  }
}
