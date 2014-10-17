<?php

namespace Drupal\multiversion\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * @todo Change to extending DrupalUnitTestBase to increase performance. 
 */
abstract class MultiversionWebTestBase extends WebTestBase {

  /**
   * @var \Drupal\multiversion\Entity\UuidIndex;
   */
  protected $uuidIndex;

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

    $this->uuidIndex = $this->container->get('entity.uuid_index');
    $this->multiversionManager = $this->container->get('multiversion.manager');
    $this->entityManager = $this->container->get('entity.manager');
    $this->entityDefinitionUpdateManager = $this->container->get('entity.definition_update_manager');

    $this->entityManager->clearCachedDefinitions();
    $this->entityDefinitionUpdateManager->applyUpdates();
  }
}
