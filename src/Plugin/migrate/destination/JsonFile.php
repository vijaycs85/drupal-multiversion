<?php

/**
 * @file
 * Contains \Drupal\multiversion\Plugin\migrate\destination\JsonFile.
 */

namespace Drupal\multiversion\Plugin\migrate\destination;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "json_file"
 * )
 */
class JsonFile extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    $file_uri = 'public://multiversion_migrate_users.json';
    $dirname = \Drupal::service('file_system')->dirname($file_uri);
    if (!file_prepare_directory($dirname, FILE_CREATE_DIRECTORY)) {
      throw new MigrateException(t('Could not create directory %dirname', array('%dirname' => $dirname)));
    }

    $file = file_save_data('', $file_uri, FILE_EXISTS_REPLACE);
    if (!$file) {
      throw new MigrateException(t('Error on creating the JSON file.'));
    }

    $configuration += array(
      'file_uri' => $file_uri,
    );

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    $source = $row->getSource();
    if (file_put_contents($this->configuration['file_uri'], json_encode($source) . "\n", FILE_APPEND)) {
      return array('uid' => $source['uid']);
    }
    return FALSE;
  }

  /**
   * Get the destination ids.
   *
   * To support MigrateIdMap maps, derived destination classes should return
   * schema field definition(s) corresponding to the primary key of the
   * destination being implemented. These are used to construct the destination
   * key fields of the map table for a migration using this destination.
   *
   * @return array
   *   An array of ids.
   */
  public function getIds() {
    // TODO: Implement getIds() method.
  }

  /**
   * Returns an array of destination fields.
   *
   * Derived classes must implement fields(), returning a list of available
   * destination fields.
   *
   * @todo Review the cases where we need the Migration parameter,
   * can we avoid that?
   *
   * @param \Drupal\migrate\Entity\MigrationInterface $migration
   *   (optional) the migration containing this destination.
   *
   * @return array
   *   - Keys: machine names of the fields
   *   - Values: Human-friendly descriptions of the fields.
   */
  public function fields(MigrationInterface $migration = NULL) {
    // TODO: Implement fields() method.
  }

}
