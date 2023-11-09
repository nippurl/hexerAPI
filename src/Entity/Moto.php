<?php

namespace App\Entity;

use App\Repository\MotoRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Modelo\VehiculoInterface;

#[ORM\Entity(repositoryClass: MotoRepository::class)]
class Moto extends Vehiculo
{
		protected static string  $tipoStr = 'Moto';
		
	public static function tipoNombre ():string{
				return 'Moto';
		}
		
		/**
		 * @inheritDoc
		 * Las matriculas de una moto deben ser 3 numeros y una letra
		 * @param $matricula
		 * @return bool
		 */
		public  function validadorMatricula($matricula =null): bool
		{
				if (!$matricula) {
					$matricula = $this->getMatricula();
				}
				return preg_match("/^[0-9]{3}[A-Z]{1}$/", \strtoupper($matricula));
		}
		
		/**
		 * @inheritDoc
		 */
		public function generarMatricula(): string
		{
				$string = '';
				$string .= substr(str_shuffle(self::NUMEROS), 0, 3);
				$string .= substr(str_shuffle(self::LETRAS), 0, 1);
				
				// Comprobamos si el string cumple con la expresión regular
				if ($this->validadorMatricula($string)) {
						return $string;
				} else {
						throw new \Exception('No es Valida la matricula de moto: '. $string);
				}
		
		}
}
