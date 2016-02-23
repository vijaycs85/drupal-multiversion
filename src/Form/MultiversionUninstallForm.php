<?php

/**
 * @file
 * Contains \Drupal\multiversion\Form\MultiversionUninstallForm.
 */

namespace Drupal\multiversion\Form;

use Behat\Mink\Exception\Exception;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MultiversionUninstallForm.
 *
 * @package Drupal\deploy\Form
 */
class MultiversionUninstallForm extends FormBase {

  /**
   * @var RendererInterface
   */
  protected $renderer;

  /**
   * @param RendererInterface $renderer
   */
  function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
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
    drupal_set_message('Click the button below before uninstalling Multiversion.', 'warning');

    $form['uninstall'] = [
      '#type' => 'submit',
      '#value' => t('Uninstall'),
      '#ajax' => [
        'callback' => [$this, 'submitFormAjax'],
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
   * @param array $form
   * @param FormStateInterface $form_state
   * @return AjaxResponse
   */
  public function submitFormAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    try {
      $response->addCommand(new CloseModalDialogCommand());
      drupal_set_message('Successfully uninstalled Multiversion.');
      $response->addCommand(new RedirectCommand(Url::fromRoute('system.modules_list')->toString()));
    }
    catch (Exception $e) {
      drupal_set_message('An error occurred while uninstalling Multiversion: ' . $e->getMessage(), 'error');
    }

    return $response;
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
      /** @var \Drupal\multiversion\MultiversionManagerInterface $manager */
      $manager = \Drupal::getContainer()->get('multiversion.manager');
      $manager->disableEntityTypes();
      \Drupal::service('module_installer')->uninstall(['multiversion']);
      drupal_set_message('Successfully uninstalled Multiversion.');
      $form_state->setRedirect('system.modules_list');
    }
    catch (Exception $e) {
      drupal_set_message('An error occurred while uninstalling Multiversion: ' . $e->getMessage(), 'error');
    }
  }
}
