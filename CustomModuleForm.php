<?php

namespace Drupal\pfe_med_connect\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\File\FileSystemInterface;

/**
 * Implements the Custom Form.
 */
class CustomForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pfe_med_connect_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv_file'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload CSV File'),
      '#description' => $this->t('Upload a CSV file containing data.'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the uploaded file.
    $validators = [
      'file_validate_extensions' => ['csv'],
    ];
    $file = file_save_upload('csv_file', $validators, FALSE, 0, FileSystemInterface::EXISTS_REPLACE);

    if ($file) {
      // Save the uploaded file for processing.
      $form_state->setValue('csv_file', $file);
    }
    else {
      $form_state->setErrorByName('csv_file', $this->t('Please upload a valid CSV file.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = $form_state->getValue('csv_file');

    if (!empty($file)) {
      $file_path = $file->getFileUri();

      // Delete previous data from the custom table.
      $this->deletePreviousData();

      // Process the CSV file and insert data into the custom table.
      $this->processCSVFile($file_path);

      // Retrieve email addresses based on the selected product and therapeutic area.
      $product = $form_state->getValue('product');
      $therapeuticArea = $form_state->getValue('therapeutic_area');
      $emailAddresses = $this->getEmailAddresses($product, $therapeuticArea);

      // Trigger email sending to the retrieved email addresses.
      $this->sendEmails($emailAddresses);

      // Display a success message.
      \Drupal::messenger()->addMessage($this->t('CSV file processed successfully.'));
    }
    else {
      \Drupal::messenger()->addError($this->t('No CSV file uploaded.'));
    }
  }

  /**
   * Deletes previous data from the custom table.
   */
  private function deletePreviousData() {
    $connection = Database::getConnection();
    $connection->delete('custom_table')->execute();
  }

  /**
   * Processes the CSV file and inserts data into the custom table.
   *
   * @param string $file_path
   *   The file path of the CSV file.
   */
  private function processCSVFile($file_path) {
    // Process the CSV file and insert data into the custom table.
    // Your code to parse the CSV file and insert data into the custom table.
  }

  /**
   * Retrieves email addresses based on the selected product and therapeutic area.
   *
   * @param string $product
   *   The selected product.
   * @param string $therapeuticArea
   *   The selected therapeutic area.
   *
   * @return array
   *   An array of retrieved email addresses.
   */
  private function getEmailAddresses($product, $therapeuticArea) {
    $emailAddresses = [];

    // Retrieve email addresses based on the selected product and therapeutic area.
    // Your code to query the custom table and retrieve email addresses.

    return $emailAddresses;
  }

  /**
   * Sends emails to the retrieved email addresses.
   *
   * @param array $emailAddresses
   *   An array of email addresses to send emails to.
   */
  private function sendEmails(array $emailAddresses) {
    // Send emails to the retrieved email addresses.
    // Your code to send emails.
  }

}
