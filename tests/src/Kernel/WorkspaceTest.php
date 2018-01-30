<?php

namespace Drupal\Tests\multiversion\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\multiversion\Entity\Workspace;
use Drupal\multiversion\Entity\WorkspaceType;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * @group multiversion
 */
class WorkspaceTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['multiversion', 'key_value', 'serialization', 'user', 'system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('workspace');
    $this->installEntitySchema('user');
    $this->installConfig('multiversion');
    $this->installSchema('key_value', 'key_value_sorted');
    $this->installSchema('system', ['sequences', 'key_value_expire']);
    $multiversion_manager = $this->container->get('multiversion.manager');
    $multiversion_manager->enableEntityTypes();
  }

  /**
   * Tests unpublishing workspaces.
   */
  public function testUnpublishingWorkspaces() {
    $permissions = [
      'administer workspaces',
    ];
    $this->setCurrentUser($this->createUser($permissions));

    // Create a test workspace type.
    WorkspaceType::create([
      'id' => 'test',
      'label' => 'Test',
    ])->save();

    // Create a live (default) and stage workspace.
    $live = Workspace::create([
      'type' => 'test',
      'machine_name' => 'live',
      'label' => 'Live',
    ]);
    $live->save();
    $stage = Workspace::create([
      'type' => 'test',
      'machine_name' => 'stage',
      'label' => 'Stage',
    ]);
    $stage->save();

    // Set stage as the active workspace.
    \Drupal::service('workspace.manager')->setActiveWorkspace($stage);

    // Check both workspaces are published by default.
    $this->assertTrue($live->isPublished());
    $this->assertTrue($stage->isPublished());

    // Unpublish the stage workspace.
    $stage->setUnpublished();
    $violations = $live->validate();
    $this->assertEquals(0, $violations->count());
    $stage->save();

    // After unpublishing stage, live should be the active workspace.
    $active_workspace = \Drupal::service('workspace.manager')->getActiveWorkspace();
    $this->assertEquals(1, $active_workspace->id());

    // Check the stage workspace has been unpublished.
    $this->assertFalse($stage->isPublished());

    // Expect an exception if the default workspace is unpublished.
    $this->setExpectedException(\Exception::class);
    $live->setUnpublished();
    $violations = $live->validate();
    $this->assertEquals(1, $violations->count());
    $this->assertEquals('The default workspace cannot be unpublished or archived.', $violations[0]->getMessage());
    $live->save();
  }

}
