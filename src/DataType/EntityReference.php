<?php
/**
 * @file
 * Contains \Drupal\multiversion\DataType\EntityReference.
 */

namespace Drupal\multiversion\DataType;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityReference as CoreEntityReference;

/**
 * Alternative entity reference data type class.
 *
 * This class is being alter in place of the core entity reference data type
 * to allow references to deleted entities. This is needed as deleted entities
 * still exist in the database when using Multiversion module.
 *
 * @todo This class needs tests.
 */
class EntityReference extends CoreEntityReference {

  /**
   * {@inheritdoc}
   */
  public function getTarget() {
    // Try getting the target entity the default way. If no target was found
    // this might mean it was deleted. In that case, we try to load the deleted
    // entity instead.
    parent::getTarget();

    if (!isset($this->target) && isset($this->id)) {
      /** @var EntityTypeManagerInterface $entity_type_manager */
      $entity_type_manager = \Drupal::service('entity_type.manager');
      /** @var \Drupal\multiversion\MultiversionManagerInterface $multiversion_manager */
      $multiversion_manager = \Drupal::service('multiversion.manager');

      $entity_type_id = $this->getTargetDefinition()->getEntityTypeId();
      $entity_type = $entity_type_manager->getDefinition($entity_type_id);

      if ($multiversion_manager->isEnabledEntityType($entity_type)) {
        /** @var \Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface $storage */
        $storage = $entity_type_manager->getStorage($entity_type_id);

        $entity = $storage->loadDeleted($this->id);
        $this->target = isset($entity) ? $entity->getTypedData() : NULL;
      }
    }
    return $this->target;
  }
}
