<?php
namespace Craft;

class FormBuilder2_ChartsController extends ElementIndexController
{
    public function actionGetEntriesData()
    {
        $formId = craft()->request->getPost('formId');

        $startDateParam = craft()->request->getRequiredPost('startDate');
        $endDateParam = craft()->request->getRequiredPost('endDate');

        $startDate = DateTime::createFromString($startDateParam, craft()->timezone);
        $endDate = DateTime::createFromString($endDateParam, craft()->timezone);
        $endDate->modify('+1 day');

        $intervalUnit = ChartHelper::getRunChartIntervalUnit($startDate, $endDate);

        $criteria = $this->getElementCriteria();
        $criteria->limit = null;

        $criteria->search = null;

        $query = craft()->elements->buildElementsQuery($criteria)
            ->select('COUNT(*) as value');

        if ($formId != 0) {
            $query->andWhere('forms.id = :formId',
                [':formId' => $formId]
            );
        }

        $dataTable = ChartHelper::getRunChartDataFromQuery($query, $startDate, $endDate,
            'formbuilder2_entries.dateCreated',
            [
                'intervalUnit' => $intervalUnit,
                'valueLabel' => Craft::t('Submissions'),
                'valueType' => 'number',
            ]
        );

        $total = 0;

        foreach($dataTable['rows'] as $row) {
            $total = $total + $row[1];
        }

        $this->returnJson([
            'dataTable' => $dataTable,
            'total' => $total,
            'totalHtml' => $total,

            'formats' => ChartHelper::getFormats(),
            'orientation' => craft()->locale->getOrientation(),
            'scale' => $intervalUnit,
            'localeDefinition' => [],
        ]);
    }
}