<?php

namespace App\Modelo;

interface VehiculoInterface
{
		/**
		 * Devuelve el nombre del tipo de objeto
		 * @return string
		 */
		public static function tipoNombre(): string;
		
		/**
		 * Valida la matricula
		 * @param string|null $matricula
		 * @return bool
		 */
		public   function validadorMatricula( ?string $matricula) : bool;
		
		/**
		 * Genera una nueva matricula de forma aleatoria de acuerdo al tipo
		 * @return string
		 */
		public function generarMatricula():string;
}