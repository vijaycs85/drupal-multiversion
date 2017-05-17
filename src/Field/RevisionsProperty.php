<?php

namespace Drupal\multiversion\Field;

use Drupal\Core\TypedData\TypedData;

/**
 * The 'revisions' property for revision token fields.
 */
class RevisionsProperty extends TypedData {

  /**
   * @var array
   */
  protected $value = [];

  /**
   * {@inheritdoc}
   */
  public function getValue($langcode = NULL) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getRoot()->getValue();

    $workspace = isset($entity->workspace) ? $entity->workspace->entity : null;
    $branch = \Drupal::service('multiversion.entity_index.factory')
      ->get('multiversion.entity_index.rev.tree', $workspace)
      ->getDefaultBranch($entity->uuid());

    $values = [];
    if (empty($branch) && !$entity->_rev->is_stub && !$entity->isNew()) {
      list($i, $hash) = explode('-', $entity->_rev->value);
      $values = [$hash];
    }
    else {
      // We want children first and parent last.
      foreach (array_reverse($branch) as $rev => $status) {
        list($i, $hash) = explode('-', $rev);
        $values[] = $hash;
      }
    }

    if (empty($this->value)) {
      $this->value = [];
    }

    if (!empty($values)) {
      $this->value = array_values(array_unique(array_merge($this->value, $values), SORT_REGULAR));
    }

    return $this->value;
  }

}
