<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;

interface WorkspaceInterface extends EntityInterface {

  /**
   * Returns the workspace name.
   *
   * @return string
   *   The name of the workspace.
   */
  public function name();

}
