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

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'standard';

  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->rootUser);
  }

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
    entity_create('node', array('type' => 'article', 'title' => 'Test article'))->save();
    drupal_flush_all_caches();
    $this->drupalGet('');
    $this->assertText('Test article', 'The title of the test article was displayed on the front page.');
    $url = $front . "?workspace=$id";
    $this->assertRaw('href="'. $url .'"', 'The id of the new workspace was displayed in the Workspace switcher block as a link.');
    $this->drupalGet($front, array('query' => array('workspace' => $id)));
    $this->drupalGet('node/add/article');
    $this->assertText('Create Article');
    $this->drupalGet($front, array('query' => array('workspace' => $id)));
    $this->assertNoText('Test article', 'The title of the test article was not displayed on the front page after switching the workspace.');
    $entity->delete();
    drupal_flush_all_caches();
    $this->drupalGet('');
    $this->assertNoText($id, 'The id of the deleted workspace was not displayed in the Workspace switcher block.');
  }
}
