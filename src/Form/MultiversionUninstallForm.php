<?php

/**
 * @file
 * Contains \Drupal\multiversion\Form\MultiversionUninstallForm.
 */

namespace Drupal\multiversion\Form;

use Behat\Mink\Exception\Exception;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\multiversion\MultiversionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MultiversionUninstallForm.
 *
 * @package Drupal\deploy\Form
 */
class MultiversionUninstallForm extends FormBase {

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\multiversion\MultiversionManagerInterface
   */
  protected $multiversionManager;

  /**
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\multiversion\MultiversionManagerInterface $multiversion_manager
   */
  function __construct(RendererInterface $renderer, MultiversionManagerInterface $multiversion_manager) {
    $this->renderer = $renderer;
    $this->multiversionManager = $multiversion_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('multiversion.manager')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'uninstall_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['description'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Are you sure you want to uninstall Multiversion?'),
      '#suffix' => '</p>',
    ];

    $form['uninstall'] = [
      '#type' => 'submit',
      '#value' => t('Uninstall'),
      '#ajax' => [
        'callback' => [$this, 'uninstallAjax'],
        'event' => 'mousedown',
        'prevent' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => 'Uninstalling Multiversion...',
        ],
      ],
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $this->multiversionManager->disableEntityTypes();
      \Drupal::service('module_installer')->uninstall(['multiversion']);
      drupal_set_message('Successfully uninstalled Multiversion.');
      $form_state->setRedirect('system.modules_list');
    }
    catch (Exception $e) {
      drupal_set_message('An error occurred while uninstalling Multiversion: ' . $e->getMessage(), 'error');
    }
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return AjaxResponse
   */
  public function uninstallAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand(new RedirectCommand(Url::fromRoute('system.modules_list')->toString()));
    return $response;
  }

}
