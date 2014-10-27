<?php

namespace Drupal\multiversion\Workspace;

use Symfony\Component\HttpFoundation\Request;

interface WorkspaceSwitcherInterface {

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return array
   */
  public function getWorkspaceSwitchLinks(Request $request);

}
