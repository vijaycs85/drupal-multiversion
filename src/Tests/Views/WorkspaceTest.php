<?php

/**
 * @file
 * Contains \Drupal\multiversion\Tests\Views\WorkspaceTest.
 */

namespace Drupal\multiversion\Tests\Views;

use Drupal\multiversion\Entity\Workspace;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;

/**
 * Tests the workspace and current_workspace field handlers.
 *
 * @group multiversion
 * @see \Drupal\multiversion\Plugin\views\filter\CurrentWorkspace
 */
class WorkspaceTest extends MultiversionTestBase {

  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create Article node type.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    }
  }

  /**
   * Test being able to switch between active workspaces.
   */
  public function testWorkspace() {
    $admin_user = $this->drupalCreateUser(['bypass node access', 'administer workspaces']);
    $uid = $admin_user->id();
    $this->drupalLogin($admin_user);

    /** @var WorkspaceManagerInterface $workspace_manager */
    $workspace_manager = $this->container->get('workspace.manager');

    $initial_workspace = $workspace_manager->getActiveWorkspace();

    // Create a node on 'default' workspace.
    $this->drupalPostForm('node/add/article', [
      'title[0][value]' => 'Initial workspace article',
    ], 'Save');
    $node1_url = $this->drupalGetHeader('location', true);

    // Create a new workspace and switch to it.
    $new_workspace = Workspace::create(['machine_name' => 'new_workspace', 'label' => 'New Workspace', 'type' => 'basic']);
    $new_workspace->save();
    $this->drupalPostForm($new_workspace->url('activate-form'), [], 'Activate');

    // Create a node on 'new_workspace' workspace.
    $this->drupalPostForm('node/add/article', [
      'title[0][value]' => 'New workspace article',
    ], 'Save');
    $node2_url = $this->drupalGetHeader('location', true);

    // Ensure you have access to only active workspace.
    $this->drupalGet($node1_url);
    $this->assertResponse(404, 'User cannot access content in an inactive workspace.');
    $out = $this->drupalGet($node2_url);
    $this->assertResponse(200, 'User can access content in the active workspace.');

    // Switch back to the initial workspace.
    $this->drupalPostForm($initial_workspace->url('activate-form'), [], 'Activate');

    // Ensure you have access to only active workspace.
    $this->drupalGet($node1_url);
    $this->assertResponse(200, 'User can access content in the active workspace.');
    $out = $this->drupalGet($node2_url);
    $this->assertResponse(404, 'User cannot access content in an inactive workspace.');
  }

}
