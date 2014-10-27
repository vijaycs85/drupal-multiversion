<?php

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Routing\RouteMatchInterface;

interface WorkspaceNegotiatorInterface {

  public function applies(RouteMatchInterface $route_match);

  public function determineActiveWorkspaceId(RouteMatchInterface $route_match);

}
