<?php

namespace App\Entity;

use App\Repository\AutoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AutoRepository::class)]
class Auto extends Vehiculo
{
		
		/**
		 * Enel caso del auto son 2 letras seguidas de 4 numeros AA1234
		 * @return string
		 */
		public function generarMatricula(): string
		{
				$string = '';
				$string .= substr(str_shuffle(self::LETRAS), 0, 2);
				$string .= substr(str_shuffle(self::NUMEROS), 0, 4);
				// Comprobamos si el string cumple con la expresión regular
				if ($this->validadorMatricula($string)) {
						return $string;
				} else {
						throw new \Exception('No es valida la matricula de Auto:  '. $string);
				}
				
		}
		
		public static function tipoNombre(): string
		{
				return 'Auto';
		}
		
		/**
		 * Valida en cada caso la matricula utilizado la funcion de validacion
		 */
		public  function validadorMatricula(?string $matricula =null): bool
		{
				if (!$matricula) {
						$matricula = $this->getMatricula();
				}
				$matricula ??= '';
				return preg_match("/^[A-Z]{2}[0-9]{4}$/", \strtoupper($matricula));
		}
}
