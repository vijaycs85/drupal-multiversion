<?php

namespace Drupal\multiversion\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * @FieldType(
 *   id = "workspace_reference",
 *   label = @Translation("Workspace reference"),
 *   description = @Translation("This field stores a reference to the workspace the entity belongs to."),
 *   list_class = "\Drupal\multiversion\Plugin\Field\FieldType\WorkspaceReferenceItemList",
 *   no_ui = TRUE
 * )
 */
class WorkspaceReferenceItem extends EntityReferenceItem { }
