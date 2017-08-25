<?php
namespace Craft;
class FormBuilder2ElementType extends BaseElementType
{
    /**
     * @inheritDoc IComponentType::getName()
     *
     * @return string
     */
    public function getName()
    { 
        return Craft::t('FormBuilder2');
    }

    /**
     * @inheritDoc IElementType::hasContent()
     *
     * @return bool
     */
    public function hasContent()
    {
        return true;
    }

    /**
     * @inheritDoc IElementType::hasTitles()
     *
     * @return bool
     */
    public function hasTitles()
    {
        return true;
    }

    /**
     * @inheritDoc IElementType::isLocalized()
     *
     * @return bool
     */
    public function isLocalized()
    {
        return false;
    }

    /**
     * @inheritDoc IElementType::hasStatuses()
     *
     * @return bool
     */
    public function hasStatuses()
    {
        return false;
    }


    /**
     * @inheritDoc IElementType::getSources()
     *
     * @param null $context
     *
     * @return array|bool|false
     */
    public function getSources($context = null)
    {
        $sources = array(
            '*' => array(
                'label' => Craft::t('All Submissons'),
            ),
        );

        foreach (fb()->forms->getAllForms() as $form) {
            $key = 'formId:' . $form->id;
            $sources[$key] = array(
                'label'    => $form->name,
                'criteria' => array('formId' => $form->id)
            );
        }

        return $sources;
    }

    /**
     * @inheritDoc IElementType::getAvailableActions()
     *
     * @param string|null $source
     *
     * @return array|null
     */
    public function getAvailableActions($source = null)
    {
        $deleteAction = craft()->elements->getAction('Delete');

        $deleteAction->setParams(
            array(
                'confirmationMessage'   => Craft::t('Are you sure you want to delete the selected entries?'),
                'successMessage'        => Craft::t('Entries deleted.'),
            )
        );

        return array($deleteAction);
    }

    /**
     * @inheritDoc IElementType::defineAvailableTableAttributes()
     *
     * @return array
     */
    public function defineAvailableTableAttributes()
    {
        $attributes = array(
            'title'       => Craft::t('Title'),
            'dateCreated' => Craft::t('Date')
        );

        return $attributes;
    }

    /**
     * @inheritDoc IElementType::getDefaultTableAttributes()
     *
     * @param string|null $source
     *
     * @return array
     */
    public function getDefaultTableAttributes($source = null)
    {
        $attributes = array();

        if ($source == '*') {
          $attributes[] = 'title';
        }

        return $attributes;
    }

    /**
     * @inheritDoc IElementType::getTableAttributeHtml()
     *
     * @param BaseElementModel $element
     * @param string           $attribute
     *
     * @return mixed|null|string
     */
    public function getTableAttributeHtml(BaseElementModel $element, $attribute)
    {
        switch ($attribute) {
            // case 'submission':
            //     $data = $element->entryUrl();
            //     return $element->submission;
            //     break;
            // case 'files':
            //     $files = $element->normalizeFilesForElementsTable();
            //     return $element->files;
            // break;
            default:
                return parent::getTableAttributeHtml($element, $attribute);
                break;
        }
    }

    /**
     * @inheritDoc IElementType::defineCriteriaAttributes()
     *
     * @return array
     */
    public function defineCriteriaAttributes()
    {
        return array(
            'formId' => AttributeType::Mixed,
            'order'  => array(AttributeType::String, 'default' => 'formbuilder2_entries.dateCreated desc')
        );
    }

    /**
     * @inheritDoc IElementType::modifyElementsQuery()
     *
     * @param DbCommand            $query
     * @param ElementCriteriaModel $criteria
     *
     * @return bool|false|null|void
     */
    public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
    {
        $select =
            'entries.id,
            entries.ipAddress,
            entries.userAgent,
            entries.dateCreated,
            entries.dateUpdated,
            forms.id as formId,
            forms.name as formName';

        $query->join('formbuilder2_entries entries', 'entries.id = elements.id');
        $query->join('formbuilder2_forms forms', 'forms.id = entries.formId');

        $query->addSelect($select);

        if ($criteria->id) {
            $query->andWhere(DbHelper::parseParam('entries.id', $criteria->id, $query->params));
        }

        if ($criteria->formId) {
            $query->andWhere(DbHelper::parseParam('entries.formId', $criteria->formId, $query->params));
        }

        if ($criteria->order)
        {
            if (stripos($criteria->order, 'elements.') === false) {
                $criteria->order = str_replace('dateCreated', 'entries.dateCreated', $criteria->order);
                $criteria->order = str_replace('dateUpdated', 'entries.dateUpdated', $criteria->order);
            }

            if (stripos($criteria->order, 'title') !== false && !$criteria->formId) {
                $criteria->order = null;
            }
        }


        // if ($criteria->formHandle) {
        //     $query->andWhere(DbHelper::parseParam('forms.handle', $criteria->formHandle, $query->params));
        // }

        // $query
        //   ->addSelect('formbuilder2_entries.formId, formbuilder2_entries.title, formbuilder2_entries.ipAddress')
        //   ->join('formbuilder2_entries formbuilder2_entries', 'formbuilder2_entries.id = elements.id');
        
        // if ($criteria->formId) {
        //     $query->andWhere(DbHelper::parseParam('formbuilder2_entries.formId', $criteria->formId, $query->params));
        // }
    }

    /**
     * @inheritDoc IElementType::populateElementModel()
     *
     * @param array $row
     *
     * @return BaseElementModel|BaseModel|void
     */
    public function populateElementModel($row)
    {
        $entry = FormBuilder2_EntryModel::populateModel($row);

        return $entry;
    }

    


}