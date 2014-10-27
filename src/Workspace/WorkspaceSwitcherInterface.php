<?php

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Routing\RouteMatchInterface;

interface WorkspaceSwitcherInterface {

  /**
   * @param RouteMatchInterface $route_match
   * @return array
   */
  public function getWorkspaceSwitchLinks(RouteMatchInterface $route_match);

}
