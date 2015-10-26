<?php

/**
 * @file
 * Contains \Drupal\multiversion\Plugin\Migrate\source\ContentEntityBase.
 */

namespace Drupal\multiversion\Plugin\Migrate\source;

/**
 * Migration source class for content entities.
 *
 * @MigrateSource(
 *   id = "multiversion"
 * )
 */
class ContentEntityBase extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $entities = $this->entityManager->getStorage($this->entityTypeId)->loadMultiple();
    $result = array();
    foreach ($entities as $entity_id => $entity) {
      foreach ($entity as $field_name => $field) {
        $value = NULL;
        /** @var \Drupal\Core\Field\FieldItemListInterface $field */
        /** @var \Drupal\Core\Field\FieldItemInterface $item */
        if ($field->count() == 1) {
          $item = $field->first();
          $value = $item->get($item->mainPropertyName())->getValue();
        }
        elseif ($field->count() > 1) {
          $value = array();
          foreach ($field as $item) {
            $value[] = $item->get($item->mainPropertyName())->getValue();
          }
        }
        $result[$entity_id][$field_name] = $value;
      }
    }

    return new \ArrayIterator(array_values($result));
  }

}
