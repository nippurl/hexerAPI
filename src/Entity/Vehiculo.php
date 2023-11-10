<?php

namespace App\Entity;

use App\Modelo\VehiculoInterface;
use App\Repository\VehiculoRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * PAra agregar un nuevo tipo de Vehiculo se necesita:
 * 1) agregarlo a la constante TIPOS
 * 2) Crear una clase que herede de Vehiculo
 */

#[ORM\Entity(repositoryClass: VehiculoRepository::class)]
#[ORM\HasLifecycleCallbacks]
# [ApiResource]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'tipo', type: 'string')]
#[ORM\DiscriminatorMap(self::TIPOS)]
abstract class Vehiculo implements \Serializable, VehiculoInterface, \JsonSerializable
{
		
		protected const LETRAS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		protected const NUMEROS = "0123456789";
		/**
		 *  Titpos de Vhiculos Soportados
		 * @var array
		 */
		public final const TIPOS = [
			'a' => Auto::class,
			'm' => Moto::class,
		];
		#[ORM\Column(length: 100, nullable: true)]
		protected ?string $marca = null;
		#[ORM\Column(length: 100, nullable: true)]
		protected ?string $modelo = null;
		#[ORM\Column(length: 100, nullable: true)]
		protected ?string $color = null;
		#[ORM\Column(length: 100, nullable: true)]
		#[Assert\Unique()]
		protected ?string $matricula = null;
		#[ORM\Column(type: 'datetime', nullable: true)]
		protected ?\DateTime $venta = null;
		#[ORM\Id]
		#[ORM\GeneratedValue]
		#[ORM\Column]
		private ?int $id = null;
		
		public static function create(string $tipo): Vehiculo
		{
				if (!isset(self::TIPOS[$tipo])) {
					throw new \Exception('Tipo de vehiculo no soportado: '.$tipo);
				}
				$class = self::TIPOS[$tipo];
				return new $class();
		}
		
		static public function VehiculoChoices()
		{
				$arr = [];
				foreach (self::TIPOS as $key => $value) {
						$arr[$key] = $value::tipoNombre();
				}
				return $arr;
		}
		
		public function getId(): ?int
		{
				return $this->id;
		}
		
		public function getMarca(): ?string
		{
				return $this->marca;
		}
		
		public function setMarca(?string $marca): static
		{
				$this->marca = $marca;
				
				return $this;
		}
		
		public function getModelo(): ?string
		{
				return $this->modelo;
		}
		
		public function setModelo(?string $modelo): static
		{
				$this->modelo = $modelo;
				
				return $this;
		}
		
		public function getColor(): ?string
		{
				return $this->color;
		}
		
		public function setColor(?string $color): static
		{
				$this->color = $color;
				
				return $this;
		}
		
		public function getMatricula(): ?string
		{
				return $this->matricula;
		}
		
		/**
		 * Las matriculas deben estar en mayuculas para que no existan 2 autos con aa1234 y Aa1234
		 * @param string|null $matricula
		 * @return $this
		 */
		public function setMatricula(?string $matricula): static
		{
				$matricula       ??= '';
				$this->matricula = \strtoupper($matricula);
				if (!$this->validadorMatricula($this->matricula)) {
						throw new \Exception('La matricula no es correcta');
				}
				return $this;
		}
		
		public function getTipo(): ?string
		{
				return $this->tipo;
		}
		
		public function setTipo(string $tipo): static
		{
				$this->tipo = $tipo;
				
				return $this;
		}
		
		public function serialize(): string
		{
				return \json_encode($this->__serialize(), JSON_THROW_ON_ERROR);
		}
		
		public function unserialize(string $data): void
		{
				$arr = \json_decode($data, true, 512, JSON_THROW_ON_ERROR);
				$this->__unserialize($arr);
		}
		
		public function __serialize(): array
		{
				return (array)$this;
		}
		
		public function __unserialize(array $data): void
		{
				foreach ($data as $key => $value) {
						$this->$key = $value;
				}
				return;
		}
		
		/**
		 * @return object
		 */
		public function jsonSerialize(): object
		{
				$vars['tipo'] = $this->getTipoStr();
				return (object)\array_merge($vars, get_object_vars($this));
		}
		
		public function getTipoStr(): string
		{
				
				return $this->tipoNombre();
		}
		
		public function rematricular(): string
		{
				$mat = $this->generarMatricula();
				$this->setMatricula($mat);
				return $mat;
		}
		
		/**
		 * @inheritDoc
		 * @return string
		 */
		abstract public function generarMatricula(): string;
		
		/**
		 * @inheritDoc
		 * @return string
		 */
		abstract public static function tipoNombre(): string;
		
		/**
		 * @inheritDoc
		 * @param string|null $matricula
		 * @return bool
		 */
		abstract public function validadorMatricula(?string $matricula = null): bool;
		
		/**
		 * Para pasar un auto a vendido solo debe poner la fecha de venta
		 * @return void
		 * @throws \Exception
		 */
		public function vendido()
		{
				if (!$this->isDisponible()) {
						throw new \Exception('El vehiculo ya ha sido vendido');
				}
				$this->setVenta(new \DateTime());
		}
		
		/**
		 * @return bool
		 */
		public function isDisponible(): bool
		{
		//		\var_dump("VENTA: ".$this->getVenta());
				return $this->getVenta() === null;
		}
		
		/**
		 * @inheritDoc
		 */
		public function getVenta(): ?\DateTime
		{
				return $this->venta;
		}
		
		/**
		 * @inheritDoc
		 */
		public function setVenta(?\DateTime $venta): static
		{
				$this->venta = $venta;
				
				return $this;
		}
		/**
		 * @param \Doctrine\Persistence\Event\LifecycleEventArgs $event
		 * @return false
		 * @throws \Exception
		 */
		
		#[ORM\PreUpdate]
		
				public function preUpdate(LifecycleEventArgs $event)
		{
				$em= $event->getObjectManager();
				//\var_dump($event);
				/** @var \App\Entity\Vehiculo $viejo */
				$uow = $em->getUnitOfWork();
				$changes_set = $uow->getEntityChangeSet($event->getObject());
				// Si no lo cambia entonces no esta $changes_set, por lo tanto la original vasta
				if (\array_key_exists('venta', $changes_set)) {
						$viejoVenta = $changes_set['venta'][0];
				}else{
						$viejoVenta = $this->venta;
				}
				
				//\var_dump($disp);
		//		$viejo = $event->getObject();
				//\var_dump($viejo);
				if ($viejoVenta  !== null) {
						$event->getObjectManager()
						      ->detach($this);
						throw new \Exception('No se puede guardar un vehiculo que ya no disponible');
				}
		}
		
}
