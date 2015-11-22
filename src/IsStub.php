<?php

/**
 * @file
 * Contains \Drupal\multiversion\IsStub.
 */

namespace Drupal\multiversion;

use Drupal\Core\TypedData\TypedData;

/**
 * The 'is_stub' property for revision token fields.
 */
class IsStub extends TypedData {

  /**
   * {@inheritdoc}
   */
  public function getValue($langcode = NULL) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getRoot()->getValue();
    if (!$entity->isNew() && $entity->_rev->value == '0-00000000000000000000000000000000') {
      return TRUE;
    }
    if ($this->value !== NULL) {
      return $this->value;
    }
    // Fall back on FALSE as the default value.
    return FALSE;
  }

}
