<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the entity sequence functionality.
 *
 * @group multiversion
 */
class SequenceIndexTest extends MultiversionWebTestBase {

  /**
   * @var \Drupal\multiversion\Entity\Index\SequenceIndexInterface
   */
  protected $sequenceIndex;

  protected function setUp() {
    parent::setUp();
    $this->sequenceIndex = \Drupal::service('entity.sequence_index');
  }

  public function testRecord() {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = entity_create('entity_test_rev');
    // We don't want to save the entity and trigger the hooks in the storage
    // controller. We just want to test the sequence storage here, so we mock
    // entity IDs here.
    $expected = array(
      'local_seq' => $this->multiversionManager->newSequenceId(),
      'entity_type' => 'entity_test_rev',
      'entity_id' => 1,
      'entity_uuid' => $entity->uuid(),
      'revision_id' => 1,
      'parent_revision_id' => 0,
      'deleted' => FALSE,
      'conflict' => FALSE,
      'local' => FALSE,
      'rev' => FALSE,
    );
    $entity->id->value = $expected['entity_id'];
    $entity->revision_id->value = $expected['revision_id'];
    $entity->_deleted->value = $expected['deleted'];
    $entity->_local_seq->value = $expected['local_seq'];
    $entity->_local->value = $expected['local'];
    $entity->_revs_info->rev = $expected['rev'];

    $this->sequenceIndex->add($entity);

    $values = $this->sequenceIndex->getRange(0);
    $this->assertEqual(count($values), 1, 'One index entry was added.');

    foreach ($expected as $key => $value) {
      $this->assertIdentical($values[0][$key], $value, "Index entry key $key have value $value");
    }

    $entity = entity_create('entity_test_rev');
    $workspace_name = $this->randomMachineName();
    entity_create('workspace', array('name' => $workspace_name));
    // Generate a new sequence ID.
    $entity->_local_seq->value = $this->multiversionManager->newSequenceId();
    $this->sequenceIndex->useWorkspace($workspace_name)->add($entity);

    $values = $this->sequenceIndex->getRange(0);
    $this->assertEqual(count($values), 1, 'One index entry was added to the new workspace.');
  }
}
