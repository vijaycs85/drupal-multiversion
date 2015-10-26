<?php

/**
 * @file
 * Contains \Drupal\multiversion\Plugin\Migrate\source\TempStore.
 */

namespace Drupal\multiversion\Plugin\Migrate\source;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * User source from json file.
 *
 * @MigrateSource(
 *   id = "tempstore"
 * )
 */
class TempStore extends SourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var KeyValueStoreExpirableInterface
   */
  protected $tempStore;

  /**
   * @var string
   */
  protected $entityTypeId;

  /**
   * @var string
   */
  protected $entityIdKey;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager'),
      $container->get('keyvalue.expirable')
    );
  }

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param MigrationInterface $migration
   *   The migration.
   * @param EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param KeyValueExpirableFactoryInterface $temp_store_factory
   *   The temp store factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityManagerInterface $entity_manager, KeyValueExpirableFactoryInterface $temp_store_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->entityManager = $entity_manager;

    $this->entityTypeId = $migration->entity_type_id;
    $entity_type = $entity_manager->getDefinition($this->entityTypeId);
    $this->entityIdKey = $entity_type->getKey('id');

    $this->tempStore = $temp_store_factory->get('multiversion_migration_' . $this->entityTypeId);
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $values = $this->tempStore->getAll();
    return new \ArrayIterator($values);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      $this->entityIdKey => array(
        'type' => 'integer',
        'alias' => 'base',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return $this->entityTypeId;
  }

  public function __toString() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

}
