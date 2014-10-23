<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the creation and operation of the Local flag field.
 *
 * @group multiversion
 */
class LocalFieldTest extends FieldTestBase {

  /**
   * {@inheritdoc}
   */
  protected $fieldName = '_local';

  /**
   * {@inheritdoc}
   */
  protected $defaultValue = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $createdEmpty = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $itemClass = '\Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem';

}
