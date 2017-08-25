<?php
namespace Craft;

class FormBuilder2_FormController extends BaseController
{

    private $form;

    /**
    * Get All Forms
    *
    */
    public function actionFormsIndex()
    { 

        $settings = craft()->plugins->getPlugin('FormBuilder2')->getSettings();
        $plugins = craft()->plugins->getPlugin('FormBuilder2');

        $variables['title']       = 'FormBuilder2';
        $variables['settings']    = $settings;
        $variables['plugin']      = $plugins;
        $variables['navigation']  = $this->navigation();

        return $this->renderTemplate('formbuilder2/forms/index', $variables);
    }


    /**
    * Edit Form
    *
    */
    public function actionEditForm(array $variables = array())
    {
        $variables['brandNewForm'] = false;
        $variables['navigation'] = $this->navigation();
        $variables['fullPageForm'] = true;
        // $variables['saveShortcutRedirect'] = 'formbuilder2/forms/edit/{id}';
        // $variables['continueEditingUrl'] = 'formbuilder2/forms/edit/{id}';

        if (!empty($variables['formId'])) {
            $variables['form'] = fb()->forms->getFormById($variables['formId']);
            if (!$variables['form']) { 
                throw new HttpException(404);
            }

            $variables['title'] = $variables['form']->name;
        } else {
            if (empty($variables['form'])) {
                $variables['form'] = $this->_prepareNewFormModel();
                $variables['brandNewForm'] = true;
            }

            $variables['title'] = Craft::t('Create a new form');
        }

        // Load Redactor Scripts for Rich Text fields
        craft()->templates->includeCssResource('/lib/redactor/redactor.css');
        craft()->templates->includeJsResource('/lib/redactor/redactor.min.js');
        craft()->templates->includeCssResource('formbuilder2/css/libs/alignment.css');
        craft()->templates->includeJsResource('formbuilder2/js/libs/alignment.js');
        craft()->templates->includeJsResource('formbuilder2/js/libs/fontfamily.js');
        craft()->templates->includeJsResource('formbuilder2/js/libs/fontsize.js');
        craft()->templates->includeJsResource('formbuilder2/js/libs/fontcolor.js');

        $this->renderTemplate('formbuilder2/forms/_edit', $variables);

    }

    /**
    * Save New Form
    *
    */
    public function actionSaveForm()
    {
        $this->requirePostRequest();
        $this->form = new FormBuilder2_FormModel();
        $this->form->id                           = craft()->request->getPost('formId');
        $this->form->name                         = craft()->request->getPost('name');
        $this->form->handle                       = craft()->request->getPost('handle');
        $this->form->fieldLayoutId                = craft()->request->getPost('fieldLayoutId');
        Craft::dd($_POST);
        if (craft()->request->getPost('options')) {
            $this->_populateFormOptions(craft()->request->getPost('options'));
        }

        if (craft()->request->getPost('spam')) {
            $this->_populateSpamProtection(craft()->request->getPost('spam'));
        }

        if (craft()->request->getPost('notify')) {
            $this->_populateNotifications(craft()->request->getPost('notify'));
        }

        if (craft()->request->getPost('messages')) {
            $this->_populateMessages(craft()->request->getPost('messages'));
        }

        if (craft()->request->getPost('settings')) {
            $this->_populateSettings(craft()->request->getPost('settings'));
        }

        // Craft::dd($_POST);

        $fieldLayout = craft()->fields->assembleLayoutFromPost();
        $fieldLayout->type = ElementType::Asset;
        $this->form->setFieldLayout($fieldLayout);

        if (fb()->forms->saveForm($this->form)) {
            craft()->userSession->setNotice(Craft::t('Form saved.'));
            $this->redirectToPostedUrl($this->form);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save form.'));
        }

        craft()->urlManager->setRouteVariables(array(
            'form' => $this->form
        ));
    }

    private function _populateFormOptions($option)
    {
        $this->form->options = $option;
    }

    private function _populateSpamProtection($option)
    {
        $this->form->spam = $option;
    }

    private function _populateNotifications($option)
    {
        $this->form->notify = $option;
    }

    private function _populateMessages($option)
    {
        $this->form->messages = $option;
    }

    private function _populateSettings($option)
    {
        $this->form->settings = $option;
    }

    /**
    * Delete Form.
    *
    */
    public function actionDeleteForm()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $formId = craft()->request->getRequiredPost('id');

        fb()->forms->deleteFormById($formId);
        $this->returnJson(array('success' => true));
    }

  /**
   * Sidebar Navigation
   *
   */
  public function navigation()
  {
    $navigationSections = array(
      array(
        'heading' => Craft::t('Menu'),
        'nav'     => array(
          array(
            'label' => Craft::t('Dashboard'),
            'icon'  => 'tachometer',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2'),
          ),
          array(
            'label' => Craft::t('Forms'),
            'icon'  => 'list-alt',
            'extra' => fb()->forms->getTotalForms(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms'),
          ),
          array(
            'label' => Craft::t('Entries'),
            'icon'  => 'file-text-o',
            'extra' => craft()->formBuilder2_entry->getTotalEntries(),
            'url'   => UrlHelper::getCpUrl('formbuilder2/entries'),
          ),
        )
      ),
      array(
        'heading' => Craft::t('Quick Links'),
        'nav'     => array(
          array(
            'label' => Craft::t('Create New Form'),
            'icon'  => 'pencil-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms/new'),
          ),
        )
      ),
      array(
        'heading' => Craft::t('Tools'),
        'nav'     => array(
          array(
            'label' => Craft::t('Export'),
            'icon'  => 'share-square-o',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/export'),
          ),
          array(
            'label' => Craft::t('Configuration'),
            'icon'  => 'sliders',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/tools/configuration'),
          ),
          array(
            'label' => Craft::t('Create New Form'),
            'icon'  => 'plus',
            'extra' => '',
            'url'   => UrlHelper::getCpUrl('formbuilder2/forms/new'),
          ),
        )
      ),
    );
    return $navigationSections;
  }

    public function actionReorder()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $ids = JsonHelper::decode(craft()->request->getRequiredPost('ids'));

        if ($success = fb()->forms->reorderForms($ids)) {
            return $this->returnJson(array('success' => $success));
        }

        return $this->returnJson(array('error' => Craft::t("Couldn't reorder forms.")));
    }




    /**
     * Prepare Form Model
     * @return FormBuilder2_Model
     */
    private function _prepareNewFormModel()
    {   
        // $messages = [
        //     'success' => [
        //         'message' => null
        //     ],
        //     'error' => [
        //         'message' => null
        //     ],
        // ];

        // $options = [
        //     'submitButton' => [
        //         'enabled' => null,
        //         'text' => null
        //     ],
        //     'redirect' => [
        //         'enabled' => null,
        //         'url' => null
        //     ],
        //     'ajax' => [
        //         'enabled' => null
        //     ],
        //     'uploads' => [
        //         'enabled' => null
        //     ]
        // ];

        // $spam = [
        //     'honeypot' => [
        //         'enabled' => null,
        //         'message' => null
        //     ],
        //     'timed' => [
        //         'enabled' => null,
        //         'number' => null
        //     ]
        // ];


        $settings = [
            'sections' => [
                'fields' => null,
                'hideMessages' => true,
                'hideAdminNotification' => true,
                'hideSubmitterNotification' => true
            ]
        ];
        
        $notify = [
            'admin' => [
                'enabled' => false
            ],
            'submitter' => [
                'enabled' => false
            ]
        ];

        $model = new FormBuilder2_FormModel();
        $model->setAttribute('notify', $notify);
        $model->setAttribute('settings', $settings);
        // $model->setAttribute('messages', $messages);
        // $model->setAttribute('options', $options);
        // $model->setAttribute('spam', $spam);

        // Fire After Submission Complete Event
        // Craft::import('plugins.formBuilder2.events.FormBuilder2_OnPrepareFormModelEvent');
        // $event = new FormBuilder2_OnPrepareFormModelEvent(
        //     $this, array(
        //         'model' => $model
        //     )
        // );
        // fb()->onPrepareFormModelEvent($event);

        return $model;
    }

}
