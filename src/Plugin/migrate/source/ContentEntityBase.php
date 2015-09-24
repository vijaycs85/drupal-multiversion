<?php

/**
 * @file
 * Contains \Drupal\multiversion\Plugin\Migrate\source\ContentEntityBase.
 */

namespace Drupal\multiversion\Plugin\Migrate\source;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Migration source class for content entities.
 *
 * @todo: Implement derivatives for all content entity types and bundles.
 *
 * @MigrateSource(
 *   id = "multiversion"
 * )
 */
class ContentEntityBase extends SourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var string
   */
  protected $entityTypeId;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->entityManager = $entity_manager;

    // @todo: Fetch entity type ID from the plugin ID once derivatives are implemented.
    $this->entityTypeId = $migration->entity_type_id;
    $entity_type = $entity_manager->getDefinition($this->entityTypeId);
    $this->entityIdKey = $entity_type->getKey('id');
  }

  /**
   * Initialize the iterator with the source data.
   *
   * @return array
   *   An array of the data for this source.
   *
   * @todo: Make this more generic.
   */
  protected function initializeIterator() {
    $entities = entity_load_multiple('user');
    $result = array();
    foreach ($entities as $entity) {
      foreach ($this->fields() as $field_name => $label) {
        if ($field_name == 'roles') {
          $result[$entity->id()][$field_name] = $entity->getRoles();
          continue;
        }
        if ($field_name == 'user_picture' && isset($entity->{$field_name}->target_id)) {
          $result[$entity->id()][$field_name]['target_id'] = $entity->{$field_name}->target_id;
          continue;
        }
        $result[$entity->id()][$field_name] = NULL;
        if (isset($entity->{$field_name}->value)) {
          $result[$entity->id()][$field_name] = $entity->{$field_name}->value;
        }
      }
    }

    return new \ArrayIterator(array_values($result));
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $return = array();

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $definitions */
    $definitions = $this->entityManager->getBaseFieldDefinitions($this->entityTypeId);
    foreach ($definitions as $definition) {
      $return[$definition->getName()] = $definition->getLabel();
    }
    return $return;
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
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return $this->entityTypeId;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // @todo: Implement
  }

}
