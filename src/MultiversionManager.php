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
