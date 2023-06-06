<?php

namespace Drupal\custom_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\file\Entity\File;

/**
 * Form to upload a CSV file and process its data.
 */
class CustomModuleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_module_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['file_upload'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload CSV File'),
      '#description' => $this->t('Please upload a CSV file.'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the uploaded file as needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = $form_state->getValue('file_upload');

    // Get the uploaded file object.
    $fileObject = File::load($file[0]);
    if ($fileObject instanceof File) {
      $filePath = $fileObject->getFileUri();

      // Process the CSV file and insert data into the custom table.
      if (($handle = fopen($filePath, "r")) !== FALSE) {
        $connection = Database::getConnection();

        // Skip the header row.
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== FALSE) {
          $product = $data[0];
          $therapeuticArea = $data[1];
          $district = $data[2];
          $mslEmail = $data[3];
          $backupEmail = $data[4];

          // Insert the data into the custom table.
          $connection->insert('custom_table')
            ->fields([
              'product' => $product,
              'therapeutic_area' => $therapeuticArea,
              'district' => $district,
              'msl_email' => $mslEmail,
              'backup_email' => $backupEmail,
            ])
            ->execute();
        }

        fclose($handle);

        // Query the custom table to retrieve the email addresses.
        $query = $connection->select('custom_table', 'ct')
          ->fields('ct', ['msl_email', 'backup_email'])
          ->condition('product', 'selected_product', '=')
          ->condition('therapeutic_area', 'selected_therapeutic_area', '=')
          ->condition('district', 'selected_district', '=');
        $result = $query->execute();

        // Trigger email sending.
        foreach ($result as $row) {
          $mslEmail = $row->msl_email;
          $backupEmail = $row->backup_email;

          // Send emails to MSL and backup email addresses.
          // Use your preferred method for sending emails here.
          // Example using Drupal's Mail API:
          $params['message'] = 'Your email message.';
          $params['subject'] = 'Your email subject';
          $params['from'] = 'noreply@example.com';

          \Drupal::service('plugin.manager.mail')
            ->mail('custom_module', 'email_key', $mslEmail, \Drupal::languageManager()->getDefaultLanguage()->getId(), $params, NULL, TRUE);

          \Drupal::service('plugin.manager.mail')
            ->mail('custom_module', 'email_key', $backupEmail, \Drupal::languageManager()->getDefaultLanguage()->getId(), $params, NULL, TRUE);
        }

        // Optionally, redirect the user to a confirmation page.
        $form_state->setRedirect('custom_module.confirmation');
      }
    }
  }

}
