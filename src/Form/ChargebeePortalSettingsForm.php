<?php

namespace Drupal\chargebee_portal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ChargebeePortalSettingsForm.
 */
class ChargebeePortalSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['chargebee_portal.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'chargebee_portal_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('chargebee_portal.settings');

    $form['live_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Live API Key'),
      '#description' => $this->t('Enter the live API key for Chargebee.'),
      '#default_value' => $config->get('live_api_key'),
      '#required' => TRUE,
    ];

    $form['live_portal_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Live Portal URL'),
      '#description' => $this->t('Enter the URL for the live Chargebee portal.'),
      '#default_value' => $config->get('live_portal_url'),
      '#required' => TRUE,
    ];

    $form['test_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test API Key'),
      '#description' => $this->t('Enter the test API key for Chargebee.'),
      '#default_value' => $config->get('test_api_key'),
      '#required' => TRUE,
    ];

    $form['test_portal_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Test Portal URL'),
      '#description' => $this->t('Enter the URL for the test Chargebee portal.'),
      '#default_value' => $config->get('test_portal_url'),
      '#required' => TRUE,
    ];

    $form['live_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Live Base URL'),
      '#description' => $this->t('Enter the base URL of the live site.'),
      '#default_value' => $config->get('live_base_url'),
      '#required' => TRUE,
    ];

    $form['fallback_payment_redirect_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Fallback Payment Redirect URL'),
      '#description' => $this->t('Enter the default URL where users can be redirected if the portal login fails.'),
      '#default_value' => $config->get('fallback_payment_redirect_url'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chargebee_portal.settings')
      ->set('live_api_key', $form_state->getValue('live_api_key'))
      ->set('live_portal_url', $form_state->getValue('live_portal_url'))
      ->set('test_api_key', $form_state->getValue('test_api_key'))
      ->set('test_portal_url', $form_state->getValue('test_portal_url'))
      ->set('live_base_url', $form_state->getValue('live_base_url'))
      ->set('fallback_payment_redirect_url', $form_state->getValue('fallback_payment_redirect_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
