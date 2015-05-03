<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "revision_token",
 *   label = @Translation("Revision token"),
 *   description = @Translation("Entity revision token."),
 *   no_ui = TRUE
 * )
 */
class RevisionItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Revision token'))
      ->setRequired(TRUE);

    $properties['new_edit'] = DataDefinition::create('boolean')
      ->setLabel(t('New edit flag'))
      ->setDescription(t('During replication this will be set to FALSE to ensure that the revision is saved as-is without generating a new token.'))
      ->setRequired(FALSE)
      ->setComputed(TRUE);

    $properties['revisions'] = DataDefinition::create('string')
      ->setLabel(t('A list of all known revisions of the entity.'))
      ->setDescription(t('During replication this will be populated with hashes (i.e. without the index prefix) from all known revisions of the entity.'))
      ->setRequired(FALSE)
      ->setComputed(TRUE);

    $properties['revs_info'] = DataDefinition::create('string')
      ->setLabel(t('A list of detailed information of all known revisions of the entity.'))
      ->setRequired(FALSE)
      ->setComputed(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
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
  public function applyDefaultValue($notify = TRUE) {
    $this->setValue(
      array(
        'value' => '0-00000000000000000000000000000000',
        'new_edit' => TRUE,
        'revisions' => array(),
      ),
      $notify);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $i = rand(0, 99);
    $hash = md5(rand());
    $token = "$i-$hash";

    return array(
      'value' => $token,
      'new_edit' => TRUE,
      'revisions' => array($hash, md5(rand()), md5(rand())),
      'revs_info' => NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    // The revision tree that will be updated.
    $tree = \Drupal::service('entity.index.rev.tree');

    // The branch that we will update the tree with.
    $branch = array();

    // We always force a new revision.
    $entity = $this->getEntity();

    $token = $this->get('value')->getValue();
    list($i, $hash) = explode('-', $token);

    // This is a regular local save operation and a new revision token should be
    // generated. The new_edit property will be set to FALSE during replication
    // to ensure the revision token is saved as-is.
    if ($this->get('new_edit')->getValue()) {
      // If this is the first revision it means that there's no parent.
      // By definition the existing revision value is the parent revision.
      $parent_token = $i == 0 ? 0 : $token;
      $token = \Drupal::service('multiversion.manager')
        ->newRevisionId($entity, $i);
      $this->set('value', $token);
      $branch[] = array($token => $parent_token);
    }
    else {
      // @todo: Lookup $token and throw conflict exception if revision exists.
    }

    // A list of all known revisions can be passed in to let the current host
    // know about the revision history, for conflict handling etc. A list of
    // revisions are always passed in during replication.
    if ($ancestor_hashes = $this->get('revisions')->getValue()) {
      // The first hash is always the current one and should not be included
      // in the ancestor array.
      array_shift($ancestor_hashes);

      // Build the remaining ancestors into the tree.
      foreach ($ancestor_hashes as $parent_hash) {
        $parent_token = --$i . '-' . $parent_hash;
        $branch[] = array($token => $parent_token);
        $token = $parent_token;
      }
    }

    // If nothing has been added to the branch yet it means that it's the first
    // revision without a parent. So add it to the branch.
    if (empty($branch)) {
      $branch[] = array($token => 0);
    }

    $tree->update($entity->uuid(), $branch);

    // Decide whether ot not this is the default revision.
    $winning = $tree->getDefaultRevision($entity->uuid());
    if ($winning == $this->get('value')->getValue()) {
      $entity->isDefaultRevision(TRUE);
    }
  }
}
