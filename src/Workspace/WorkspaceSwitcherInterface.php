<?php

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

interface WorkspaceSwitcherInterface {

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Core\Url $url
   *
   * @internal param string $path
   * @return array
   */
  public function getWorkspaceSwitchLinks(Request $request, Url $url);

}
