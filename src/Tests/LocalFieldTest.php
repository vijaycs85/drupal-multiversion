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
  protected $itemClass = '\Drupal\multiversion\Plugin\Field\FieldType\LocalFlagItem';

  /**
   * {@inheritdoc}
   */
  protected $itemListClass = '\Drupal\multiversion\Plugin\Field\FieldType\LocalFlagItemList';

}
