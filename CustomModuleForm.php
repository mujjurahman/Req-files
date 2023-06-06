use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CustomModuleForm extends FormBase {

  public function getFormId() {
    return 'custom_module_form';
  }

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

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = $form_state->getValue('file_upload');
    // Process the CSV file and insert data into the custom table.
    // Retrieve the email addresses and trigger email sending.
  }

}
