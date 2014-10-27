<?php

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DefaultWorkspaceNegotiator implements WorkspaceNegotiatorInterface {

  use ContainerAwareTrait;

  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  }

  public function determineActiveWorkspaceId(RouteMatchInterface $route_match) {
    return $this->container->getParameter('workspace.default');
  }

}
