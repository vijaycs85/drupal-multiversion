<?php

namespace Drupal\multiversion\Workspace;

use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\HttpFoundation\Request;

class SessionWorkspaceNegotiator implements WorkspaceNegotiatorInterface, WorkspaceSwitcherInterface {

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
    $workspace_id = $request->query->get('workspace') ? $request->query->get('workspace') : NULL;
    if (!$workspace_id && isset($_SESSION['workspace'])) {
      $workspace_id = $_SESSION['workspace'];
    }
    return $workspace_id;
  }

  /**
   * {@inheritdoc}
   */
  public function persist(WorkspaceInterface $workspace) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceSwitchLinks(Request $request) {

  }

}
