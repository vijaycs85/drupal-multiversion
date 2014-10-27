<?php

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DefaultWorkspaceNegotiator implements WorkspaceNegotiatorInterface {

  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceId(RouteMatchInterface $route_match) {
    return $this->container->getParameter('workspace.default');
  }

  /**
   * {@inheritdoc}
   */
  public function persist(WorkspaceInterface $workspace) {
    return TRUE;
  }

}
