<?php

/**
 * @file
 * Contains \Drupal\multiversion\Plugin\migrate\destination\ContentEntityBase.
 */

namespace Drupal\multiversion\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Migration destination class for content entities.
 *
 * @todo: Implement derivatives for all content entity types and bundles.
 *
 * @MigrateDestination(
 *   id = "multiversion"
 * )
 */
class ContentEntityBase extends EntityContentBase {

  /**
   * @var string
   */
  protected $entityTypeId;

  /**
   * The password service class.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $password;

  /**
   * Builds an user entity destination.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param MigrationInterface $migration
   *   The migration.
   * @param EntityStorageInterface $storage
   *   The storage for this entity type.
   * @param array $bundles
   *   The list of bundles this entity type has.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Password\PasswordInterface $password
   *   The password service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityStorageInterface $storage, array $bundles, EntityManagerInterface $entity_manager, PasswordInterface $password) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage, $bundles, $entity_manager);
    if (isset($configuration['has_password'])) {
      $this->password = $password;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    // @todo: Fetch entity type ID from the plugin ID once derivatives are implemented.
    $entity_type_id = $migration->entity_type_id;

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorage($entity_type_id),
      array_keys($container->get('entity.manager')->getBundleInfo($entity_type_id)),
      $container->get('entity.manager'),
      $container->get('password')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    if ($this->password) {
      $this->password->disablePasswordHashing();
    }
    return parent::import($row, $old_destination_id_values);
  }

}
