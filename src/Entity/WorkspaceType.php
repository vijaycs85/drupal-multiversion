<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\WorkspaceType.
 */

namespace Drupal\multiversion\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Workspace type entity.
 *
 * @ConfigEntityType(
 *   id = "workspace_type",
 *   label = @Translation("Workspace type"),
 *   handlers = {
 *     "list_builder" = "Drupal\multiversion\WorkspaceTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\multiversion\Form\WorkspaceTypeForm",
 *       "edit" = "Drupal\multiversion\Form\WorkspaceTypeForm",
 *       "delete" = "Drupal\multiversion\Form\WorkspaceTypeDeleteForm"
 *     }
 *   },
 *   config_prefix = "workspace_type",
 *   bundle_of = "workspace",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/workspace_type/{workspace_type}",
 *     "edit-form" = "/admin/structure/workspace_type/{workspace_type}/edit",
 *     "delete-form" = "/admin/structure/workspace_type/{workspace_type}/delete",
 *     "collection" = "/admin/structure/visibility_group"
 *   }
 * )
 */
class WorkspaceType extends ConfigEntityBase implements WorkspaceTypeInterface {
  /**
   * The Workspace type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Workspace type label.
   *
   * @var string
   */
  protected $label;

}
