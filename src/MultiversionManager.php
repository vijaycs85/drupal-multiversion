<?php

namespace Drupal\multiversion;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;
use Symfony\Component\Serializer\Serializer;

class MultiversionManager implements MultiversionManagerInterface {

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

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

  /**
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface $workspace_manager
   * @param \Symfony\Component\Serializer\Serializer $serializer
   */
  public function __construct(WorkspaceManagerInterface $workspace_manager, Serializer $serializer) {
    $this->workspaceManager = $workspace_manager;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveWorkspaceName() {
    return $this->workspaceManager->getActiveWorkspace()->id();
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveWorkspaceName($id) {
    $workspace = $this->workspaceManager->load($id);
    return $this->workspaceManager->setActiveWorkspace($workspace);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Consider using the nextId API to generate more sequential IDs.
   * @see \Drupal\Core\Database\Connection::nextId
   */
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
    // - old sequence ID (@todo)
    // - old revision hash
    // - normalized entity (without revision info field)
    // - attachments (@todo)
    return ($index + 1) . '-' . md5($this->termToBinary(array($deleted, 0, $old_rev, $normalized_entity, array())));
  }

  protected function termToBinary(array $term) {
    // @todo: Switch to BERT serialization format instead of JSON.
    return $this->serializer->serialize($term, 'json');
  }
}
