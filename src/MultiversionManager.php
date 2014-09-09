<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Symfony\Component\Serializer\Serializer;

class MultiversionManager implements MultiversionManagerInterface {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * @var string
   */
  protected $activeWorkspace = 'default';

  public function __construct(EntityManagerInterface $entity_manager, Serializer $serializer) {
    $this->entityManager = $entity_manager;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function requiredWorkspaceDefinitions() {
    $definitions['default'] = array();
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function requiredFieldDefinitions() {
    $definitions['_revs_info'] = array(
      'label' => 'Revision info',
      'type' => 'revision_info',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'revisionable' => TRUE,
      'locked' => TRUE,
    );
    $definitions['_deleted'] = array(
      'label' => 'Deleted flag',
      'type' => 'deleted_flag',
      'cardinality' => 1,
      'revisionable' => TRUE,
      'locked' => TRUE,
    );
    $definitions['_local_seq'] = array(
      'label' => 'Local sequence ID',
      'type' => 'local_sequence',
      'cardinality' => 1,
      'revisionable' => TRUE,
      'locked' => TRUE,
    );
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function ensureRequiredRepositories() {
    $definitions = self::requiredWorkspaceDefinitions();
    $storage = $this->entityManager->getStorage('workspace');

    foreach ($definitions as $workspace_name => $values) {
      $values['name'] = $workspace_name;
      $workspace = $storage->create($values);
      $workspace->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function ensureRequiredFields() {
    // @todo
  }

  /**
   * {@inheritdoc}
   */
  public function attachRequiredFields($entity_type, $bundle) {
    $definitions = self::requiredFieldDefinitions();
    $field_storage = $this->entityManager->getStorage('field_storage_config');
    $instance_storage = $this->entityManager->getStorage('field_instance_config');

    foreach ($definitions as $field_name => $definition) {
      $field = $field_storage->load("$entity_type.$field_name");
      $instance = $instance_storage->load("$entity_type.$bundle.$field_name");

      if (empty($field)) {
        $values = array_merge(array('name' => $field_name, 'entity_type' => $entity_type), $definition);
        $field = $field_storage->create($values);
        $field->save();
      }
      if (empty($instance)) {
        $values = array(
          'field_name' => $field_name,
          'entity_type' => $entity_type,
          'bundle' => $bundle,
          'label' => $definition['label'],
        );
        $instance = $instance_storage->create($values);
        $instance->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveWorkspaceName() {
    return $this->activeWorkspace;
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveWorkspaceName($workspace_name) {
    return $this->activeWorkspace = $workspace_name;
  }

  public function newRevisionId(ContentEntityInterface $entity, $index = 0) {
    $deleted = $entity->_deleted->value;
    $old_rev = $entity->_revs_info->rev;
    $normalized_entity = $this->serializer->normalize($entity);
    // Remove fields internal to the multiversion system.
    foreach ($normalized_entity as $key => $value) {
      if ($key{0} == '_') {
        unset($normalized_entity[$key]);
      }
    }
    // The terms being serialized are:
    // - deleted
    // - old sequence ID (TBD)
    // - old revision hash
    // - normalized entity (without revision info field)
    // - attachments (TBD)
    return ($index + 1) . '-' . md5($this->termToBinary(array($deleted, 0, $old_rev, $normalized_entity, array())));
  }

  protected function termToBinary(array $term) {
    // @todo: Switch to BERT serialization format instead of JSON.
    return $this->serializer->serialize($term, 'json');
  }
}
