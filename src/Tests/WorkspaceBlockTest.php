<?php


/**
 * @file
 * Contains \Drupal\multiversion\Tests\WorkspaceBlockTest.
 */

namespace Drupal\multiversion\Tests;

/**
 * Tests workspace block functionality.
 *
 * @group multiversion
 */
class WorkspaceBlockTest extends MultiversionWebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'multiversion',
    'block',
  );

  public function testBlock() {
    $this->drupalPlaceBlock('multiversion_workspace_block', array('region' => 'sidebar_first', 'label' => 'Workspace switcher'));
    $this->drupalGet('');

    // Confirm that the block is being displayed.
    $this->assertText('Workspace switcher', t('Block successfully being displayed on the page.'));
    $front = \Drupal::url('<front>');
    $this->assertRaw('href="'. $front .'"', 'The id of the default workspace was displayed in the Workspace switcher block as a link.');
    $id = $this->randomMachineName();
    $entity = entity_create('workspace', array('id' => $id));
    $entity->save();
    $this->drupalGet('');
    $url = $front . "/?workspace=$id";
    $this->assertRaw('href="'. $url .'"', 'The id of the new workspace was displayed in the Workspace switcher block as a link.');
    $entity->delete();
    $this->drupalGet('');
    $this->assertNoText($id, 'The id of the deleted workspace was not displayed in the Workspace switcher block.');
  }
}
