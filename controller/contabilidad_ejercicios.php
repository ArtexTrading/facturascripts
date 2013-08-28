<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2012  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'model/ejercicio.php';

class contabilidad_ejercicios extends fs_controller
{
   public $ejercicio;
   
   public function __construct()
   {
      parent::__construct('contabilidad_ejercicios', 'Ejercicios', 'contabilidad', FALSE, TRUE);
   }
   
   protected function process()
   {
      $this->ejercicio = new ejercicio();
      $this->buttons[] = new fs_button_img('b_nuevo_ejercicio', 'nuevo');
      
      if( isset($_POST['codejercicio']) )
      {
         $eje0 = new ejercicio();
         $eje0->codejercicio = $_POST['codejercicio'];
         $eje0->nombre = $_POST['nombre'];
         $eje0->fechainicio = $_POST['fechainicio'];
         $eje0->fechafin = $_POST['fechafin'];
         $eje0->estado = $_POST['estado'];
         if( $eje0->save() )
         {
            $this->new_message("Ejercicio ".$eje0->codejercicio." guardado correctamente.");
            header('location: '.$eje0->url());
         }
         else
            $this->new_error_msg("¡Imposible guardar el ejercicio!");
      }
   }
   
   public function version()
   {
      return parent::version().'-4';
   }
}

?>
