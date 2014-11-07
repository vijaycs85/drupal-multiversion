<?php

namespace Drupal\multiversion\Workspace;

use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\HttpFoundation\Request;

class SessionWorkspaceNegotiator extends WorkspaceNegotiatorBase implements WorkspaceSwitcherInterface {

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
      // @todo Review from a security perspective.
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

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceSwitchLinks(Request $request, $path) {
    $links = array();
    $active_workspace_id = $this->workspaceManager->getActiveWorkspace($request)->id();
    $query = array();
    parse_str($request->getQueryString(), $query);

    $workspaces = $this->workspaceManager->loadMultiple();
    ksort($workspaces);
    foreach ($workspaces as $workspace) {
      $workspace_id = $workspace->id();
      $links[$workspace_id] = array(
        'href' => $path,
        'title' => $workspace_id,
        'query' => $query,
      );
      if ($workspace_id != $active_workspace_id) {
        $links[$workspace_id]['query']['workspace'] = $workspace_id;
      }
      else {
        $links[$workspace_id]['attributes']['class'][] = 'session-active';
      }
    }

    return $links;
  }

}
