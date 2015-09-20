<?php

/**
 * @file
 * Contains functions used for content migration.
 */

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;

/**
 * Implements hook_modules_installed().
 */
function multiversion_modules_installed($modules) {
  if (in_array('multiversion', $modules)) {
    // Apply updates. We need to run this before user migration.
    \Drupal::service('entity.definition_update_manager')->applyUpdates();

    $batch = array(
      'title' => t('Migrates content between the old and new storage.'),
      'operations' => array(
        array('_multiversion_migrate_install_dependencies', array()),
        array('_multiversion_migrate_content_to_tmp', array()),
        array('_multiversion_migrate_empty_old_storage', array()),
        array('_multiversion_migrate_apply_new_storage', array()),
        array('_multiversion_migrate_content_from_tmp', array()),
      ),
      'init_message' => t('Executing content migration.'),
      'finished' => '_multiversion_migrate_finished',
    );
    batch_set($batch);

    // @todo: See if there's a cleaner way to solve this.
    $system_path = \Drupal::service('path.current')->getPath();
    if ($system_path != '/admin/modules/list/confirm' && $system_path != '/admin/modules') {
      $batch =& batch_get();
      $batch['progressive'] = FALSE;
      batch_process();
    }
  }
}

/**
 * Batch operation callback to install dependencies.
 *
 * @param array $context
 *   An array of contextual key/value information for rebuild batch process.
 */
function _multiversion_migrate_install_dependencies(&$context) {
  $context['message'] = t('Installing module dependencies for the content migration.');

  $dependencies = array('migrate', 'migrate_drupal', 'file');
  foreach ($dependencies as $module) {
    if (\Drupal::moduleHandler()->moduleExists($module)) {
      unset($dependencies['$module']);
    }
  }
  \Drupal::service('module_installer')->install($dependencies, TRUE);
}

/**
 * Batch operation to empty the old storage.
 *
 * @param array $context
 *   An array of contextual key/value information for rebuild batch process.
 */
function _multiversion_migrate_content_to_tmp(&$context) {
  _multiversion_migrate_add_database_connection();
  $context['message'] = t('Migrating content to temporary content storage.');

  $migration = entity_load('migration', 'user_to_tmp');
  $message = new MigrateMessage();
  $executable = new MigrateExecutable($migration, $message);
  $executable->import();
}

/**
 * Batch operation to migrate content from temporary storage.
 *
 * @param array $context
 *   An array of contextual key/value information for rebuild batch process.
 */
function _multiversion_migrate_content_from_tmp(&$context) {
  _multiversion_migrate_add_database_connection();
  $context['message'] = t('Migrating content back from temporary content storage.');

  $migration = entity_load('migration', 'user_from_tmp');
  $executable = new MigrateExecutable($migration, $message);
  $executable->import();
}

/**
 * Batch operation callback to empty old storage.
 *
 * @param array $context
 *   An array of contextual key/value information for rebuild batch process.
 */
function _multiversion_migrate_empty_old_storage(&$context) {
  $context['message'] = t('Emptying the old content storage.');

  $entity_manager = \Drupal::entityManager();
  $entity_type = $entity_manager->getDefinition('user');
  $uids = db_select('users_field_data', 'u')
    ->fields('u', array('uid'))
    ->execute()
    ->fetchCol('uid');
  $entities = entity_load_multiple('user', array_values($uids));
  // Delete all users from the database.
  $entity_manager->getStorage($entity_type->id())->delete($entities);

  // This configuration is used when checking supported entity types. After user
  // migration to the json file we set 'user' as a supported entity type.
  \Drupal::state()->set('multiversion_migration_done', TRUE);
}

/**
 * Batch operation to apply the new storage handler.
 *
 * @param array $context
 *   An array of contextual key/value information for rebuild batch process.
 *
 * @todo Make this generic to work with all supported entity types.
 */
function _multiversion_migrate_apply_new_storage(&$context) {
  $context['message'] = t('Applying the new content storage.');

  $entity_manager = \Drupal::entityManager();
  $entity_type = $entity_manager->getDefinition('user');

  // Apply updates.
  if (!$entity_manager->getStorage($entity_type->id())->hasData()) {
    \Drupal::service('entity.definition_update_manager')->applyUpdates();
  }
}

/**
 * Batch finished callback.
 *
 * @param bool $success
 *   A boolean indicating whether the re-build process has completed.
 * @param array $results
 *   An array of results information.
 * @param array $operations
 *   An array of function calls (not used in this function).
 */
function _multiversion_migrate_finished($success, $results, $operations) {
  if ($success) {
    drupal_set_message(t('Successfully migrated users to the new storage.'));
    drupal_set_message(t('Consider uninstalling the migrate and drupal_migrate modules that were automatically enabled as part of the migration.'), 'warning');
  }
  else {
    drupal_set_message(t('User migration to the new storage failed.'), 'error');
  }
}

/**
 * Helper function to add database connection information for the migration.
 */
function _multiversion_migrate_add_database_connection() {
  $connection_info = Database::getConnectionInfo('default');
  foreach ($connection_info as $target => $value) {
    $connection_info[$target]['prefix'] = array(
      'default' => $value['prefix']['default'],
    );
  }
  Database::addConnectionInfo('migrate', 'default', $connection_info['default']);
}
