<?php

namespace Drupal\custom_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Database\Database;

/**
 * Implements the CustomModuleForm form controller.
 *
 * @see \Drupal\Core\Form\FormBase
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
    // Add form elements for file upload.
    $form['file_upload'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload CSV File'),
      '#required' => TRUE,
      '#description' => $this->t('Please upload a CSV file.'),
    ];

    // Add submit button.
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
    // Validate the uploaded file.
    $file = $form_state->getValue('file_upload');
    if (!empty($file[0])) {
      $fileObject = File::load($file[0]);
      if (!$fileObject instanceof File) {
        $form_state->setErrorByName('file_upload', $this->t('Invalid file.'));
      }
    }
    else {
      $form_state->setErrorByName('file_upload', $this->t('Please upload a file.'));
    }
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

      // Delete existing data from the custom table.
      $connection = Database::getConnection();
      $connection->truncate('custom_table')->execute();

      // Process the CSV file and insert new data into the custom table.
      if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Skip the header row.
        $header = fgetcsv($handle);

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
        $query = $connection->select('custom_table', 'ct');
        $query->fields('ct', ['msl_email', 'backup_email']);
        // Rest of the query conditions or filters as per your requirement.
        $results = $query->execute()->fetchAll();

        // Trigger email sending to the retrieved email addresses.
        // Rest of the code to send emails...
      }
    }
  }

}
