<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Symfony\Component\Serializer\Serializer;

class MultiversionManager implements MultiversionManagerInterface {

  const DEFAULT_WORKSPACE_NAME = 'default';

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
  protected $activeWorkspaceName;

  /**
   * Entity types that Multiversion won't support.
   *
   * This list will mostly contain edge case entity test types that break
   * Multiversion's tests in really strange ways.
   *
   * @var array
   * @todo Fix these some day. Some contrib modules might behave the same way?
   */
  protected $entityTypeBlackList = array(
    'entity_test_no_id',
    'entity_test_base_field_display',
    'shortcut',
  );

  /**
   * Entity types that Multiversion should support but currently does not.
   *
   * @var array
   * @todo The 'user' entity type needs a migration of existing entities.
   */
  protected $entityTypeToDo = array(
    'user',
    'file',
  );

  public function __construct(EntityManagerInterface $entity_manager, Serializer $serializer) {
    $this->entityManager = $entity_manager;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveWorkspaceName() {
    return $this->activeWorkspaceName ?: self::DEFAULT_WORKSPACE_NAME;
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveWorkspaceName($workspace_name) {
    return $this->activeWorkspaceName = $workspace_name;
  }

  public function newSequenceId() {
    // Multiply the microtime by 1 million to ensure we get an accurate integer.
    // Credit goes to @letharion and @logaritmisk for this simple but genius
    // solution.
    return (int) (microtime(TRUE) * 1000000);
  }

  public function isSupportedEntityType(EntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    if (in_array($entity_type_id, $this->entityTypeBlackList)) {
      return FALSE;
    }
    // @todo Remove this when there are no entity types left to implement.
    if (in_array($entity_type_id, $this->entityTypeToDo)) {
      return FALSE;
    }
    return ($entity_type instanceof ContentEntityTypeInterface);
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
