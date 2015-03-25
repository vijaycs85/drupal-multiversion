<?php
/**
 * @file
 * Contains \Drupal\multiversion\Plugin\Migrate\source\UserSql.
 */

namespace Drupal\multiversion\Plugin\Migrate\source;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\SourceEntityInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * User source from json Drupal 8.
 *
 * @MigrateSource(
 *   id = "user_sql"
 * )
 */
class UserSql extends DrupalSqlBase implements SourceEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $fields = $this->baseFields();
    unset($fields['uuid']);
    $query = $this->select('users_field_data', 'ufd')
      ->fields('ufd', array_keys($fields))
      ->fields('u', array('uuid'));
    $query->innerJoin('users', 'u', 'u.uid = ufd.uid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();

    // Add roles field.
    $fields['roles'] = $this->t('Roles');

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      'uid' => array(
        'type' => 'integer',
        'alias' => 'u',
      ),
    );
  }

  /**
   * Returns the user base fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function baseFields() {
    $fields = array(
      'uid' => $this->t('User ID'),
      'uuid' => $this->t('UUID'),
      'langcode' => $this->t('Language code'),
      'preferred_langcode' => $this->t('Preferred language code'),
      'preferred_admin_langcode' => $this->t('Preferred admin language code'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email'),
      'timezone' => $this->t('Timezone'),
      'status' => $this->t('Status'),
      'created' => $this->t('Created'),
      'changed' => $this->t('Changed'),
      'access' => $this->t('Last access'),
      'login' => $this->t('Last login'),
      'init' => $this->t('Initial email'),
    );

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // User roles.
    $roles = $this->select('user__roles', 'ur')
      ->fields('ur', array('roles_target_id'))
      ->condition('ur.entity_id', $row->getSourceProperty('uid'))
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('roles', $roles);

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'user';
  }

}
