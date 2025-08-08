 <?php

// Function for live environment settings
function chargebee_portal_admin_settings($form, &$form_state) {
  // Define the form for live environment settings
  $form['chargebee_live_api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Live API Key'),
    '#default_value' => variable_get('chargebee_live_api_key', ''),
  );
  $form['chargebee_live_portal_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Live Portal URL'),
    '#default_value' => variable_get('chargebee_live_portal_url', ''),
  );

$form['fallback_payment_redirect_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Fallback Payment Redirect URL'),
    '#description' => t('Enter the default URL for users who do not have a Chargebee ID or need to be redirected for other reasons.'),
    '#default_value' => variable_get('fallback_payment_redirect_url', 'https://yoursite.chargebeeportal.com/'), // Default value
    '#required' => TRUE,
);


  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Configuration'),
  );

  return $form;
}

// Submit handler for live environment settings
function chargebee_portal_admin_settings_submit($form, &$form_state) {
  \Drupal::configFactory()->getEditable('chargebee_portal.settings')
    ->set('chargebee_live_portal_url', $form_state['values']['chargebee_live_portal_url'])
    ->set('chargebee_live_api_key', $form_state['values']['chargebee_live_api_key'])
    ->set('fallback_payment_redirect_url', $form_state['values']['fallback_payment_redirect_url'])
    ->save();
  \Drupal::messenger()->addMessage(t('Configuration saved.'));
}

// New function for testing settings
function chargebee_portal_admin_testing_settings($form, &$form_state) {
  // If the form has been submitted, use the submitted values.
  $test_url = isset($form_state['input']['test_url']) ? $form_state['input']['test_url'] : variable_get('chargebee_portal_test_url', '');
  $test_api_key = isset($form_state['input']['test_api_key']) ? $form_state['input']['test_api_key'] : variable_get('chargebee_portal_test_api_key', '');
  $test_chargebee_user_id = isset($form_state['input']['test_chargebee_user_id']) ? $form_state['input']['test_chargebee_user_id'] : variable_get('chargebee_portal_test_chargebee_user_id', '');


  $form['testing_environment']['test_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Test URL'),
    '#description' => t('The endpoint URL for the Chargebee testing environment.'),
    '#default_value' => $test_url, // Use variable directly
  );

  $form['testing_environment']['test_api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Test API Key'),
    '#description' => t('The API key for the Chargebee testing environment.'),
    '#default_value' => $test_api_key, // Use variable directly
  );

  $form['testing_environment']['test_chargebee_user_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Test Chargebee User ID'),
    '#description' => t('The Chargebee User ID to use for testing.'),
    '#default_value' => $test_chargebee_user_id, // Use variable directly
  );

 $form['testing_environment']['test_button'] = array(
        '#type' => 'submit',
        '#value' => t('Run Test and Save'),
        '#submit' => array('chargebee_portal_run_test_submit'),
    );

 // Fieldset for Test Results
    $form['test_results'] = array(
        '#type' => 'fieldset',
        '#title' => t('Test Results'),
        '#collapsible' => TRUE,
        '#collapsed' => !variable_get('chargebee_portal_test_run', FALSE), // Uncollapse if a test has been run
    );
    $form['test_results']['output'] = array(
        '#markup' => variable_get('chargebee_portal_test_results', 'No tests run yet.'),
    );

    unset($form['actions']['submit']); // Remove the default "Save configuration" button

    return system_settings_form($form);
}


function chargebee_portal_run_test_submit($form, &$form_state) {
    $test_url = $form_state['values']['test_url'];
    $test_api_key = $form_state['values']['test_api_key'];
    $test_chargebee_user_id = $form_state['values']['test_chargebee_user_id'];

    // Save the values for future use.
    variable_set('chargebee_portal_test_url', $test_url);
    variable_set('chargebee_portal_test_api_key', $test_api_key);
    variable_set('chargebee_portal_test_chargebee_user_id', $test_chargebee_user_id);

    $result = chargebee_portal_create_session($test_chargebee_user_id, $test_api_key, $test_url);
    
    if ($result['success']) {
        $test_result = 'Test succeeded! Result: ' . print_r($result['data'], TRUE);
    } else {
        $error_details = $result['error_details'];
        $logs_link = l(t('Check the logs for details.'), 'admin/reports/dblog');
        $test_result = format_string('Test failed! Error details: @error_details. !logs_link', array('@error_details' => $error_details, '!logs_link' => $logs_link));
    }

    // Save the test result to display it the next time the form is loaded
    variable_set('chargebee_portal_test_results', $test_result);

    // Set a variable to indicate a test has been run
    variable_set('chargebee_portal_test_run', TRUE);

    // Redirect back to the form
    $form_state['redirect'] = 'admin/config/services/chargebee_portal/testing';
}



function chargebee_portal_exit($destination = NULL) {
    if ($destination == 'admin/config/services/chargebee_portal/testing') {
        variable_del('chargebee_portal_test_run');
    }
}

