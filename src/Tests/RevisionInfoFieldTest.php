<?php

namespace Drupal\multiversion\Tests;

use Drupal\multiversion\Plugin\Field\FieldType\RevisionInfoItem;
use Drupal\multiversion\Plugin\Field\FieldType\RevisionInfoItemList;

class RevisionInfoFieldTest extends MultiversionWebTestBase {

  public static function getInfo() {
    return array(
      'name'  => 'Revision Info field',
      'description'  => "Test the creation and operation of the Revision Info field.",
      'group' => 'Multiversion'
    );
  }

  public function testFieldCreation() {
    $entity = entity_create('entity_test_rev');
    $this->assertTrue($entity->_revs_info instanceof RevisionInfoItemList, 'Field was attached to content entity.');
    $this->assertTrue($entity->_revs_info->get(0) instanceof RevisionInfoItem, 'Field item implements correct interface.');
    $this->assertTrue($entity->_revs_info->isEmpty(), 'Field is created empty.');
    $this->assertTrue($entity->_revs_info->get(0)->isEmpty(), 'First field item is created empty.');
    $this->assertEqual($entity->_revs_info->count(), 1, 'Field is created with one field item.');
  }

  public function testFieldOperations() {
    $entity = entity_create('entity_test_rev');

    $entity->save();
    $this->assertEqual($entity->_revs_info->count(), 1, 'One value after first save.');
    $first_rev = $entity->_revs_info->get(0)->rev;
    $this->assertTrue(!empty($first_rev), 'First revision value was generated.');

    $entity->save();
    $this->assertEqual($entity->_revs_info->count(), 2, 'Two values after second save.');
    $this->assertTrue(!empty($entity->_revs_info->get(0)->rev), 'Second value was generated.');
    $this->assertEqual($first_rev, $entity->_revs_info->get(1)->rev, 'First value was pushed to last delta.');
  }
}
