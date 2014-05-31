<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "local_sequence",
 *   label = @Translation("Local sequence ID"),
 *   description = @Translation("Local sequence ID for this entity."),
 *   list_class = "\Drupal\multiversion\Plugin\Field\FieldType\LocalSequenceItemList",
 *   no_ui = TRUE
 * )
 */
class LocalSequenceItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'id';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['id'] = DataDefinition::create('string')
      ->setLabel(t('Local sequence ID'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'id' => array(
          'type' => 'float',
          'size' => 'big',
          'not null' => TRUE,
        ),
      ),
    );
  }
}
