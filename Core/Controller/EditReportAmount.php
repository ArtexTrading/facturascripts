<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditReportAccounting;
use FacturaScripts\Core\Model\ReportAmount;
use FacturaScripts\Dinamic\Lib\Accounting\BalanceAmounts;

/**
 * Description of EditReportAmount
 *
 * @author Jose Antonio Cuello <jcuello@artextrading.com>
 */
class EditReportAmount extends EditReportAccounting
{

    /**
     * Returns the class name of the model to use in the editView.
     *
     * @return string
     */
    public function getModelClassName()
    {
        return 'ReportAmount';
    }

    /**
     * Return the basic data for this page.
     *
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'reports';
        $data['title'] = 'balance-amounts';
        $data['icon'] = 'fas fa-calculator';
        return $data;
    }

    /**
     * Generate Balance Amounts data for report
     *
     * @param ReportAmount $model
     * @return array
     */
    protected function generateReport($model)
    {
        $params = [
            'idcompany' => $model->idcompany,
            'subaccount-from' => $model->startcodsubaccount,
            'subaccount-to' => $model->endcodsubaccount,
            'channel' => $model->channel,
            'level' => $model->level,
            'ignoreregularization' => $model->ignoreregularization,
            'ignoreclosure' => $model->ignoreclosure
        ];

        $balanceAmount = new BalanceAmounts();
        $balanceAmount->setExerciseFromDate($model->idcompany, $model->startdate);
        return $balanceAmount->generate($model->startdate, $model->enddate, $params);
    }
}
