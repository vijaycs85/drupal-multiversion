<?php

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;

interface WorkspaceNegotiatorInterface {

  /**
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @return boolean
   */
  public function applies(RouteMatchInterface $route_match);

  /**
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @return string
   */
  public function getWorkspaceId(RouteMatchInterface $route_match);

  /**
   * @param \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   * @return boolean
   */
  public function persist(WorkspaceInterface $workspace);

}
