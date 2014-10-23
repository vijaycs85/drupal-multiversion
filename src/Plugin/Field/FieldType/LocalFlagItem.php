<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem;

/**
 * @FieldType(
 *   id = "local_flag",
 *   label = @Translation("Local flag"),
 *   description = @Translation("This field indicates if the entity is local and not intended to be replicated to other environments."),
 *   list_class = "\Drupal\multiversion\Plugin\Field\FieldType\LocalFlagItemList",
 *   no_ui = TRUE
 * )
 */
class LocalFlagItem extends BooleanItem { }
