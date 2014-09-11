<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface WorkspaceInterface extends ConfigEntityInterface {

  /**
   * Returns the workspace name.
   *
   * @return string
   *   The name of the workspace.
   */
  public function name();

}
