<?php

namespace Drupal\share_selection\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;

/**
 * Provides settings for autologout module.
 */
class ShareSelectionSettingsForm extends ConfigFormBase {

  /**
   * The entity type Bundle Information.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;
  /**
   * @var ConfigFactoryInterface
   */
  private $config_factory;

  /**
   * ShareSelectionSettingsForm constructor.
   * @param ConfigFactoryInterface $config_factory
   * @param ModuleHandlerInterface $module_handler
   * @param EntityTypeBundleInfoInterface $entity_type_bundle_info
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->config_factory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['share_selection.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'share_selection_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    //$content_types = entity_get_info('node');
    $content_types = ['page' => 'page','article' => 'article'];
    $content_types_options = array();

    $content_types_options_default = array();
    $share_by = array(
      'paths' => t('Paths'),
      'content_types' => t('Content types'),
    );
    $exclude_paths = 'admin/*';
    //$content_types = $content_types->getBundleEntityType();
//    foreach ($content_types['bundles'] as $bundle_machine_name => $bundle) {
//      $content_types_options[$bundle_machine_name] = $bundle['label'];
//      $content_types_options_default[$bundle_machine_name] = $bundle_machine_name;
//    }
    $content_types_options_default = ['page' => 'page','article' => 'article'];
    $form['share_selection_paths_or_content'] = array(
      '#type' => 'radios',
      '#options' => $share_by,
      '#title' => t('Show by paths or content types'),
      '#description' => t('Select how you want to control the places to share selection'),
     '#default_value' => \Drupal::config('share_selection.settings')->get('share_selection_paths_or_content'),
    );
    // Share by content options.
    $form['by_content'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => t('By content options'),
      '#states' => array(
        'visible' => array(
          ':input[name="share_selection_paths_or_content"]' => array('value' => 'content_types'),
        ),
      ),
    );
    $form['by_content']['share_selection_content_types'] = array(
      '#type' => 'checkboxes',
      '#options' => $content_types_options_default,
      '#title' => t('Content types'),
      '#description' => t('Content types where links will be shown'),
      '#default_value' => \Drupal::config('share_selection.settings')->get('share_selection_content_types'),
    );
    // Share by paths options.
    $paths_behaviors = array(
      0 => t('All pages except those listed'),
      1 => t('Only the listed pages'),
    );
    $form['by_paths'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => t('By paths options'),
      '#states' => array(
        'visible' => array(
          ':input[name="share_selection_paths_or_content"]' => array('value' => 'paths'),
        ),
      ),
    );

    $form['by_paths']['share_selection_paths_behavior'] = array(
      '#type' => 'radios',
      '#options' => $paths_behaviors,
      '#title' => t('Show on specific pages'),
      '#default_value' => \Drupal::config('share_selection.settings')->get('share_selection_paths_behavior'),
    );

    $form['by_paths']['share_selection_paths'] = array(
      '#type' => 'textarea',
      '#description' => t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array('%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>')),
      '#default_value' => \Drupal::config('share_selection.settings')->get('share_selection_paths'),
    );
    // Exclude roles.

    $user_roles = array_keys(user_roles());
    $form['exclude_roles'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => t('Exclude Roles'),
    );

    $form['exclude_roles']['share_selection_exclude_roles'] = array(
      '#type' => 'checkboxes',
      '#options' => $user_roles,
      '#description' => t("The selected roles won't see the Share Selection buttons."),
    );

    // Display options.
    $display_options = array(
      'image' => t('Only image'),
      'text' => t('Only text'),
      'image_and_text' => t('Image and text'),
    );

    $form['display_options'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => t('Styling options'),
    );

    $form['display_options']['share_selection_images_replacement_path'] = array(
      '#type' => 'textfield',
      '#title' => t('Images replacement path'),
    '#default_value' => \Drupal::config('share_selection.settings')->get('share_selection_images_replacement_path'),
    );

    $form['display_options']['share_selection_display_style'] = array(
      '#type' => 'select',
      '#title' => t('Style'),
      '#options' => $display_options,
      '#default_value' => \Drupal::config('share_selection.settings')->get('share_selection_display_style'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state){

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $values = $form_state->getValues();
    $share_selection_settings = $this->config('share_selection.settings');   
    $share_selection_settings->set('share_selection_paths_or_content', $values['share_selection_paths_or_content'])
      ->set('share_selection_content_types', $values['share_selection_content_types'])
      ->set('share_selection_paths_behavior', $values['share_selection_paths_behavior'])
      ->set('share_selection_paths', $values['share_selection_paths'])
      ->set('redirect_url', $values['redirect_url'])
      ->set('no_dialog', $values['no_dialog'])
      ->set('share_selection_exclude_roles', $values['share_selection_exclude_roles'])
      ->set('share_selection_display_style', $values['share_selection_display_style'])
      ->set('share_selection_images_replacement_path', $values['share_selection_images_replacement_path'])
      ->set('use_watchdog', $values['use_watchdog'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  }