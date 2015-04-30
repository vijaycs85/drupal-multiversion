<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "revision_info",
 *   label = @Translation("Revision info"),
 *   description = @Translation("Revision history information about the entity."),
 *   list_class = "\Drupal\multiversion\Plugin\Field\FieldType\RevisionInfoItemList",
 *   no_ui = TRUE
 * )
 */
class RevisionInfoItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'rev';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['rev'] = DataDefinition::create('string')
      ->setLabel(t('Revision hash'))
      ->setRequired(TRUE);
    $properties['status'] = DataDefinition::create('string')
      ->setLabel(t('Revision status'))
      ->setReadOnly(TRUE)
      ->setComputed(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'rev' => array(
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    // @todo Implement logic for the 'status' property.
    return parent::__get($name);
  }
}
