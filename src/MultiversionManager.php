<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Serializer\Serializer;

class MultiversionManager implements MultiversionManagerInterface, ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var int
   */
  protected $lastSequenceId;

  /**
   * Entity types that Multiversion won't support.
   *
   * This list will mostly contain edge case entity test types that break
   * Multiversion's tests in really strange ways.
   *
   * @var array
   * @todo: {@link https://www.drupal.org/node/2597333 Fix these some day.
   * Some contrib modules might behave the same way?}
   */
  protected $entityTypeBlackList = array(
    'entity_test_no_id',
    'entity_test_base_field_display',
    'shortcut',
    'contact_message',
  );

  /**
   * Entity types that Multiversion should support but currently does not.
   *
   * @var array
   */
  protected $entityTypeToDo = array(
    'file',
  );

  /**
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface $workspace_manager
   * @param \Symfony\Component\Serializer\Serializer $serializer
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\Core\State\StateInterface $state
   */
  public function __construct(WorkspaceManagerInterface $workspace_manager, Serializer $serializer, EntityManagerInterface $entity_manager, StateInterface $state) {
    $this->workspaceManager = $workspace_manager;
    $this->serializer = $serializer;
    $this->entityManager = $entity_manager;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveWorkspaceId() {
    return $this->workspaceManager->getActiveWorkspace()->id();
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveWorkspaceId($id) {
    $workspace = $this->workspaceManager->load($id);
    return $this->workspaceManager->setActiveWorkspace($workspace);
  }

  /**
   * {@inheritdoc}
   *
   * @todo: {@link https://www.drupal.org/node/2597337 Consider using the
   * nextId API to generate more sequential IDs.}
   * @see \Drupal\Core\Database\Connection::nextId
   */
  public function newSequenceId() {
    // Multiply the microtime by 1 million to ensure we get an accurate integer.
    // Credit goes to @letharion and @logaritmisk for this simple but genius
    // solution.
    $this->lastSequenceId = (int) (microtime(TRUE) * 1000000);
    return $this->lastSequenceId;
  }

  /**
   * {@inheritdoc}
   */
  public function lastSequenceId() {
    return $this->lastSequenceId;
  }

  /**
   * {@inheritdoc}
   */
  public function isSupportedEntityType(EntityTypeInterface $entity_type, $ignore_status = FALSE) {
    $entity_type_id = $entity_type->id();

    if (in_array($entity_type_id, $this->entityTypeBlackList)) {
      return FALSE;
    }

    $enabled = $this->state->get('multiversion_enabled', array());
    if (!$ignore_status && !in_array($entity_type_id, $enabled)) {
      return FALSE;
    }

    // @todo: {@link https://www.drupal.org/node/2597339 Remove this when there
    // are no entity types left to implement.}
    if (in_array($entity_type_id, $this->entityTypeToDo)) {
      return FALSE;
    }
    return ($entity_type instanceof ContentEntityTypeInterface);
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedEntityTypes($ignore_status = FALSE) {
    $entity_types = [];
    foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($this->isSupportedEntityType($entity_type, $ignore_status)) {
        $entity_types[$entity_type->id()] = $entity_type;
      }
    }
    
    return $entity_types;
  }

  /**
   * {@inheritdoc}
   */
  public function enableEntityType(EntityTypeInterface $entity_type) {
    $migration = $this->createMigration($entity_type);

    $migration
      ->installDependencies()
      ->migrateContentToTemp()
      ->emptyOldStorage();

    $enabled = $this->state->get('multiversion_enabled', array());
    $enabled[] = $entity_type->id();
    $this->state->set('multiversion_enabled', $enabled);

    // @todo: State caching issue only fixed by reset on global container. Why??
    \Drupal::state()->resetCache();

    $migration
      ->applyNewStorage()
      ->migrateContentFromTemp()
      ->uninstallDependencies();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function enableEntityTypes() {
    foreach ($this->getSupportedEntityTypes(TRUE) as $entity_type) {
      $this->enableEntityType($entity_type);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function newRevisionId(ContentEntityInterface $entity, $index = 0) {
    $deleted = $entity->_deleted->value;
    $old_rev = $entity->_rev->value;
    $normalized_entity = $this->serializer->normalize($entity);
    // Remove fields internal to the multiversion system.
    foreach ($normalized_entity as $key => $value) {
      if ($key{0} == '_') {
        unset($normalized_entity[$key]);
      }
    }
    // The terms being serialized are:
    // - deleted
    // - old sequence ID (@todo: {@link https://www.drupal.org/node/2597341
    // Address this property.})
    // - old revision hash
    // - normalized entity (without revision info field)
    // - attachments (@todo: {@link https://www.drupal.org/node/2597341
    // Address this property.})
    return ($index + 1) . '-' . md5($this->termToBinary(array($deleted, 0, $old_rev, $normalized_entity, array())));
  }

  protected function termToBinary(array $term) {
    // @todo: {@link https://www.drupal.org/node/2597478 Switch to BERT
    // serialization format instead of JSON.}
    return $this->serializer->serialize($term, 'json');
  }

  /**
   * Factory method for a new Multiversion migration.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @return \Drupal\multiversion\MultiversionMigrationInterface
   */
  protected function createMigration(EntityTypeInterface $entity_type) {
    $migration = MultiversionMigration::create($this->container, $entity_type, $this->entityManager);
    return $migration;
  }
}
