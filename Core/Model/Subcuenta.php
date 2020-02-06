<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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
namespace FacturaScripts\Core\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Detail level of an accounting plan. It is related to a single account.
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Artex Trading sa     <jcuello@artextrading.com>
 */
class Subcuenta extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * Account code.
     *
     * @var string
     */
    public $codcuenta;

    /**
     * Identifier of the special account.
     *
     * @var string
     */
    public $codcuentaesp;

    /**
     * Exercise code.
     *
     * @var string
     */
    public $codejercicio;

    /**
     * Sub-account code.
     *
     * @var string
     */
    public $codsubcuenta;

    /**
     * Amount of the debit.
     *
     * @var float|int
     */
    public $debe;

    /**
     * All exercises.
     *
     * @var Ejercicio[]
     */
    protected static $ejercicios;

    /**
     * Description of the subaccount.
     *
     * @var string
     */
    public $descripcion;

    /**
     * Configuration parameter.
     *
     * @var bool
     */
    private static $disableAditionTest = false;

    /**
     * Amount of credit.
     *
     * @var float|int
     */
    public $haber;

    /**
     * ID of the account to which it belongs.
     *
     * @var int
     */
    public $idcuenta;

    /**
     * Primary key.
     *
     * @var int
     */
    public $idsubcuenta;

    /**
     * Balance amount.
     *
     * @var float|int
     */
    public $saldo;

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->codejercicio = $this->getExercise()->codejercicio;
        $this->debe = 0.0;
        $this->haber = 0.0;
        $this->saldo = 0.0;
    }

    /**
     * 
     * @param bool $value
     */
    public function disableAditionalTest(bool $value = true)
    {
        self::$disableAditionTest = $value;
    }

    /**
     * Get account from subaccount data
     *
     * @return Cuenta
     */
    public function getAccount()
    {
        $where = [
            new DataBaseWhere('codejercicio', $this->codejercicio),
            new DataBaseWhere('codcuenta', $this->codcuenta),
        ];
        $account = new Cuenta();
        $account->loadFromCode('', $where);
        return $account;
    }

    /**
     * Returns the current exercise or the default one.
     * 
     * @return Ejercicio
     */
    public function getExercise()
    {
        /// loads all exercise to improve performance
        if (empty(self::$ejercicios)) {
            $exerciseModel = new Ejercicio();
            self::$ejercicios = $exerciseModel->all();
        }

        /// find exercise
        foreach (self::$ejercicios as $exe) {
            if ($exe->codejercicio == $this->codejercicio) {
                return $exe;
            } elseif (empty($this->codejercicio) && $exe->isOpened()) {
                /// return default exercise
                return $exe;
            }
        }

        /// exercise not found? try to get from database
        $exercise = new Ejercicio();
        if ($exercise->loadFromCode($this->codejercicio)) {
            /// add new exercise to cache
            self::$ejercicios[] = $exercise;
        }
        return $exercise;
    }

    /**
     * Get ID for account of subaccount
     *
     * @return int
     */
    public function getIdAccount()
    {
        return $this->getAccount()->idcuenta;
    }

    /**
     * Get ID for subAccount
     *
     * @return int
     */
    public function getIdSubaccount()
    {
        $where = [
            new DataBaseWhere('codejercicio', $this->codejercicio),
            new DataBaseWhere('codsubcuenta', $this->codsubcuenta),
        ];
        foreach ($this->all($where) as $subc) {
            return $subc->idsubcuenta;
        }

        return 0;
    }

    /**
     *
     * @return string
     */
    public function getSpecialAccountCode()
    {
        if (empty($this->codcuentaesp)) {
            $account = new Cuenta();
            if ($account->loadFromCode($this->idcuenta)) {
                return $account->codcuentaesp;
            }
        }

        return $this->codcuentaesp;
    }

    /**
     * This function is called when creating the model table. Returns the SQL
     * that will be executed after the creation of the table. Useful to insert values
     * default.
     *
     * @return string
     */
    public function install()
    {
        /// force the parents tables
        new CuentaEspecial();
        new Cuenta();

        return parent::install();
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'idsubcuenta';
    }

    /**
     *
     * @return string
     */
    public function primaryDescriptionColumn()
    {
        return 'codsubcuenta';
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'subcuentas';
    }

    /**
     * Returns True if there is no erros on properties values.
     *
     * @return bool
     */
    public function test()
    {
        $this->saldo = $this->debe - $this->haber;

        $this->codcuenta = trim($this->codcuenta);
        $this->codsubcuenta = trim($this->codsubcuenta);
        $this->descripcion = $this->toolBox()->utils()->noHtml($this->descripcion);
        if (\strlen($this->descripcion) < 1 || \strlen($this->descripcion) > 255) {
            $this->toolBox()->i18nLog()->warning(
                'invalid-column-lenght',
                ['%column%' => 'descripcion', '%min%' => '1', '%max%' => '255']
            );
            return false;
        }

        if (self::$disableAditionTest) {
            return parent::test();
        }

        if (!$this->testErrorInLengthSubAccount()) {
            $this->toolBox()->i18nLog()->warning('account-length-error', ['%code%' => $this->codsubcuenta]);
            return false;
        }

        $this->idcuenta = $this->getIdAccount();
        if (empty($this->idcuenta)) {
            $this->toolBox()->i18nLog()->warning('account-data-error');
            return false;
        }

        return parent::test();
    }

    /**
     * Returns the url where to see / modify the data.
     *
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'ListCuenta?activetab=List')
    {
        return parent::url($type, $list);
    }

    /**
     * 
     * @param array $values
     *
     * @return bool
     */
    protected function saveInsert(array $values = [])
    {
        if ($this->getExercise()->isOpened()) {
            return parent::saveInsert($values);
        }

        $this->toolBox()->i18nLog()->warning('closed-exercise', ['%exerciseName%' => $this->getExercise()->primaryDescription()]);
        return false;
    }

    /**
     * Check if exists error in long of subaccount. Returns FALSE if error.
     *
     * @return bool
     */
    private function testErrorInLengthSubAccount(): bool
    {
        $exercise = $this->getExercise();
        if ($exercise->exists()) {
            return \strlen($this->codsubcuenta) === $exercise->longsubcuenta;
        }

        return false;
    }
}
