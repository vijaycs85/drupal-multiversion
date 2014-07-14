<?php

namespace Drupal\multiversion\Tests;

use Drupal\multiversion\Plugin\Field\FieldType\LocalSequenceItem;
use Drupal\multiversion\Plugin\Field\FieldType\LocalSequenceItemList;

/**
 * Test the creation and operation of the Local Sequence field.
 *
 * @group multiversion
 */
class LocalSequenceFieldTest extends MultiversionWebTestBase {

  public function testFieldCreation() {
    $entity = entity_create('entity_test_rev');
    $this->assertTrue($entity->_local_seq instanceof LocalSequenceItemList, 'Field was attached to content entity.');
    $this->assertTrue($entity->_local_seq->get(0) instanceof LocalSequenceItem, 'Field item implements correct interface.');
    $this->assertTrue($entity->_local_seq->isEmpty(), 'Field is created empty.');
    $this->assertTrue($entity->_local_seq->get(0)->isEmpty(), 'First field item is created empty.');
    $this->assertEqual($entity->_local_seq->count(), 1, 'Field is created with one field item.');
  }

  public function testFieldOperations() {
    $entity = entity_create('entity_test_rev');

    $entity->save();
    $this->assertEqual($entity->_local_seq->count(), 1, 'One value after first save.');
    $first_seq = $entity->_local_seq->get(0)->id;
    $this->assertTrue(!empty($first_seq), 'First revision value was generated.');

    $entity->save();
    $second_seq = $entity->_local_seq->get(0)->id;
    $this->assertEqual($entity->_local_seq->count(), 1, 'One values after second save.');
    $this->assertTrue(!empty($second_seq), 'Second value was generated.');
    $this->assertNotEqual($first_seq, $second_seq, 'First and second value differ.');
  }
}
