<?php

namespace Drupal\multiversion\Tests;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\Utility\String;

/**
 * Test the entity sequence functionality.
 *
 * @group multiversion
 */
class SequenceIndexTest extends MultiversionWebTestBase {

  /**
   * @var \Drupal\multiversion\Entity\SequenceIndexInterface
   */
  protected $sequenceIndex;

  protected function setUp() {
    parent::setUp();
    $this->sequenceIndex = \Drupal::service('entity.sequence_index');
  }

  public function testRecord() {
    $entity = entity_create('entity_test_rev');
    // We don't want to save the entity and trigger the hooks in the storage
    // controller. We just want to test the sequence storage here, so we mock
    // entity IDs here.
    $expected = array(
      'local_seq' => (string) microtime(TRUE),
      'entity_type' => 'entity_test_rev',
      'entity_id' => 1,
      'revision_id' => 1,
      'parent_revision_id' => 0,
      'deleted' => FALSE,
      'conflict' => FALSE,
    );
    $entity->id->value = $expected['entity_id'];
    $entity->revision_id->value = $expected['revision_id'];
    $entity->_deleted->value = $expected['deleted'];
    $entity->_local_seq->value = $expected['local_seq'];

    $this->sequenceIndex->add($entity,  $expected['parent_revision_id']);

    $values = $this->sequenceIndex->getRange(0);
    $this->assertEqual(count($values), 1, 'One index entry was added.');

    foreach ($expected as $key => $value) {
      $this->assertIdentical($values[0][$key], $value, String::format('Index entry key !key have value !value', array('!key' => $key, '!value' => $value)));
    }
  }
}
