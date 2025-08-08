<?php

namespace Drupal\chargebee_portal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\user\Entity\User;
use GuzzleHttp\Exception\RequestException;

/**
 * Class ChargebeePortalController.
 */
class ChargebeePortalController extends ControllerBase {

  /**
   * Redirects to the payment portal page on Chargebee or fallback URL.
   */
  public function paymentPortalRedirect() {
    \Drupal::logger('chargebee_portal')->info('paymentPortalRedirect method called.');

    $current_user = \Drupal::currentUser();
    \Drupal::logger('chargebee_portal')->info('Current user ID: ' . $current_user->id());

    $account = User::load($current_user->id());
    $chargebee_id = $account->get('field_user_chargebee_id')->value;

    // Simple check for the existence of the Chargebee ID
    if (empty($chargebee_id)) {
      \Drupal::logger('chargebee_portal')->warning('No Chargebee ID found for user.');
      $url = \Drupal::config('chargebee_portal.settings')->get('fallback_payment_redirect_url');

      return new TrustedRedirectResponse($url);
    }

    \Drupal::logger('chargebee_portal')->info('Chargebee ID found: ' . $chargebee_id);

    // Retrieve the API key and portal URL from the configuration
    $api_key = \Drupal::config('chargebee_portal.settings')->get('live_api_key');
    $url = \Drupal::config('chargebee_portal.settings')->get('live_portal_url');

    // Create the payload for the API request to Chargebee
    global $base_url;
    $payload = [
      'redirectUrl' => $base_url . '/user',
      'customer' => [
        'id' => $chargebee_id,
      ],
    ];

    // Set up the request options for the HTTP POST request
    $options = [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Authorization' => 'Basic ' . base64_encode($api_key . ':'),
      ],
      'form_params' => $payload,
    ];

    try {
      \Drupal::logger('chargebee_portal')->info('Sending POST request to Chargebee.');
      
      // Send the POST request to Chargebee
      $response = \Drupal::httpClient()->post($url, $options);
      
      // Log the HTTP status code returned by Chargebee
      \Drupal::logger('chargebee_portal')->info('Chargebee response status code: ' . $response->getStatusCode());

      if ($response->getStatusCode() == 200) {
        \Drupal::logger('chargebee_portal')->info('Portal session created successfully for Chargebee ID: ' . $chargebee_id);
        $response_body = json_decode($response->getBody(), TRUE);
        return new TrustedRedirectResponse($response_body['portal_session']['access_url']);
      } else {
        \Drupal::logger('chargebee_portal')->error('Failed to create portal session for Chargebee ID: ' . $chargebee_id . '. Response code: ' . $response->getStatusCode() . '. Message: ' . $response->getReasonPhrase());
        return [
          '#markup' => $this->t('Failed to create portal session.'),
        ];
      }
    } catch (RequestException $e) {
      \Drupal::logger('chargebee_portal')->error('Exception occurred while creating portal session for Chargebee ID: ' . $chargebee_id . '. Exception message: ' . $e->getMessage());
      return [
        '#markup' => $this->t('Exception occurred while creating portal session: @message', ['@message' => $e->getMessage()]),
      ];
    }
  }
}


