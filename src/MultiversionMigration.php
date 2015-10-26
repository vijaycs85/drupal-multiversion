<?php

namespace Drupal\multiversion;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MultiversionMigration implements MultiversionMigrationInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $updateManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager) {
    return new static(
      $entity_type,
      $entity_manager,
      $container->get('entity.definition_update_manager'),
      $container->get('module_handler'),
      $container->get('module_installer')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $update_manager
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   */
  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, EntityDefinitionUpdateManagerInterface $update_manager, ModuleHandlerInterface $module_handler, ModuleInstallerInterface $module_installer) {
    $this->entityType = $entity_type;
    $this->entityManager = $entity_manager;
    $this->updateManager = $update_manager;
    $this->moduleHandler = $module_handler;
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public function installDependencies() {
    $dependencies = array('migrate', 'migrate_drupal');
    foreach ($dependencies as $i => $module_name) {
      if ($this->moduleHandler->moduleExists($module_name)) {
        unset($dependencies[$i]);
      }
    }
    $this->moduleInstaller->install($dependencies, TRUE);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function migrateContentToTemp() {
    $this->executeMigration($this->entityType->id() . '__to_tmp');
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function emptyOldStorage() {
    $storage = $this->entityManager->getStorage($this->entityType->id());
    $entities = $storage->loadMultiple();
    if ($entities) {
      $storage->delete($entities);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function applyNewStorage() {
    $this->updateManager->applyUpdates();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function migrateContentFromTemp() {
    $this->executeMigration($this->entityType->id() . '__from_tmp');
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function uninstallDependencies() {
    return $this;
  }

  /**
   * Helper method for running a migration.
   *
   * @param string $migration_id
   * @return \Drupal\migrate\MigrateExecutableInterface
   */
  protected function executeMigration($migration_id) {
    // Add necessary database connection that the Migrate API needs during
    // a migration like this.
    $connection_info = Database::getConnectionInfo('default');
    foreach ($connection_info as $target => $value) {
      $connection_info[$target]['prefix'] = array(
        'default' => $value['prefix']['default'],
      );
    }
    Database::addConnectionInfo('migrate', 'default', $connection_info['default']);

    // Load the migration config entity.
    //$migration = $this->entityManager->getStorage('migration')->load($migration_id);
    $migration = entity_load('migration', $migration_id);

    if ($migration) {
      // Execute the migration.
      $message = new MigrateMessage();
      $executable = new MigrateExecutable($migration, $message);
      $executable->import();
    }
    return $executable;
  }
}
