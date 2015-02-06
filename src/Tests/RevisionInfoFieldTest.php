<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the creation and operation of the Revision Info field.
 *
 * @group multiversion
 */
class RevisionInfoFieldTest extends FieldTestBase {

  /**
   * {@inheritdoc}
   */
  protected $fieldName = '_revs_info';

  /**
   * {@inheritdoc}
   */
  protected $createdEmpty = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $itemListClass = '\Drupal\multiversion\Plugin\Field\FieldType\RevisionInfoItemList';

  /**
   * {@inheritdoc}
   */
  protected $itemClass = '\Drupal\multiversion\Plugin\Field\FieldType\RevisionInfoItem';

  public function testFieldOperations() {
    $entity = entity_create('entity_test_rev');

    $entity->save();
    $this->assertEqual($entity->{$this->fieldName}->count(), 1, 'One value after first save.');
    $first_rev = $entity->{$this->fieldName}[0]->rev;
    $this->assertTrue(!empty($first_rev), 'First revision value was generated.');

    $entity->save();
    $this->assertEqual($entity->{$this->fieldName}->count(), 2, 'Two values after second save.');
    $this->assertTrue(!empty($entity->{$this->fieldName}[0]->rev), 'Second value was generated.');
    $this->assertEqual($first_rev, $entity->{$this->fieldName}[1]->rev, 'First value was pushed to last delta.');
  }
}
