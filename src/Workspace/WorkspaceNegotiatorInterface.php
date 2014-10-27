<?php

namespace Drupal\multiversion\Workspace;

use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\HttpFoundation\Request;

interface WorkspaceNegotiatorInterface {

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return boolean
   */
  public function applies(Request $request);

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return string
   */
  public function getWorkspaceId(Request $request);

  /**
   * @param \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   * @return boolean
   */
  public function persist(WorkspaceInterface $workspace);

}
