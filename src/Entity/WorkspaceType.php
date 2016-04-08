<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\WorkspaceType.
 */

namespace Drupal\multiversion\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Workspace type entity.
 *
 * @ConfigEntityType(
 *   id = "workspace_type",
 *   label = @Translation("Workspace type"),
 *   config_prefix = "type",
 *   bundle_of = "workspace",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/workspace/types/{workspace_type}/edit",
 *     "delete-form" = "/admin/structure/workspace/types/{workspace_type}/delete",
 *     "collection" = "/admin/structure/workspace/types",
 *   }
 * )
 */
class WorkspaceType extends ConfigEntityBundleBase implements WorkspaceTypeInterface {
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
