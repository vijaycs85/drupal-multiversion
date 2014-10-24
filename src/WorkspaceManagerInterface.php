<?php

namespace Drupal\multiversion;

interface WorkspaceManagerInterface {

  /**
   * @return string
   */
  public function getActiveId();

  /**
   * @param string $id
   * @return \Drupal\multiversion\WorkspaceManagerInterface
   */
  public function setActiveId($id);

}
