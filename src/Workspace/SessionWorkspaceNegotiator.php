<?php

/**
 * @file
 * Contains \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator.
 */

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Url;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\HttpFoundation\Request;

class SessionWorkspaceNegotiator extends WorkspaceNegotiatorBase {

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceId(Request $request) {
    $workspace_id = $request->query->get('workspace') ?: NULL;
    if (!$workspace_id && isset($_SESSION['workspace'])) {
      // @todo: {@link https://www.drupal.org/node/2597464 Review from a
      // security perspective.}
      $workspace_id = $_SESSION['workspace'];
    }
    return $workspace_id ?: $this->container->getParameter('workspace.default');
  }

  /**
   * {@inheritdoc}
   */
  public function persist(WorkspaceInterface $workspace) {
    $_SESSION['workspace'] = $workspace->id();
    return TRUE;
  }

}
