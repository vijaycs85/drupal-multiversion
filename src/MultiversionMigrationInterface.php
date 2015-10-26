<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface MultiversionMigrationInterface {

  /**
   * Factory method.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @return \Drupal\multiversion\MultiversionMigrationInterface
   */
  public static function create(ContainerInterface $container, EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager);

  /**
   * @return \Drupal\multiversion\MultiversionMigrationInterface
   */
  public function installDependencies();

  /**
   * @return \Drupal\multiversion\MultiversionMigrationInterface
   */
  public function migrateContentToTemp();

  /**
   * @return \Drupal\multiversion\MultiversionMigrationInterface
   */
  public function emptyOldStorage();

  /**
   * @return \Drupal\multiversion\MultiversionMigrationInterface
   */
  public function applyNewStorage();

  /**
   * @return \Drupal\multiversion\MultiversionMigrationInterface
   */
  public function migrateContentFromTemp();

  /**
   * @return \Drupal\multiversion\MultiversionMigrationInterface
   */
  public function uninstallDependencies();

}
