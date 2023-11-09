<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Vehiculo;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class VehiculoTest extends ApiTestCase
{
		use ReloadDatabaseTrait;
		
		// use RefreshDatabaseTrait;
		const URL = 'http://localhost:8000';
		
		public static function setUpBeforeClass(): void
		{
				//		self::$append = true;
				self::$purgeWithTruncate = true;
		}
		
		public function testIndex(): void
		{
				$response = static::createClient()
				                  ->request('GET', self::URL.'/');
				$this->assertResponseIsSuccessful();
				$this->assertJson('[{"tipo":"Moto","marca":"Yamaha","modelo":"cross","color":"blanca","matricula":"123A","venta":null,"id":6},{"tipo":"Auto","marca":"Ford","modelo":"Focus","color":"negro","matricula":"AA1234","venta":null,"id":8}]',
					$response->getContent()
				);
		}
		
		public function testNew(): void
		{
				$vehiculo = [
					'marca'     => 'Renault',
					'modelo'    => '12',
					'color'     => 'gris',
					'matricula' => 'VI1986',
				];
				$response = static::createClient()
				                  ->request('GET', self::URL.'/new/a', [
					                  'json' => $vehiculo,
				                  ]);
				$this->assertResponseIsSuccessful();
				$resultado = $response->toArray();
				$this->assertEquals('ok', $resultado['resultado']);
				$response = static::createClient()
				                  ->request('GET', self::URL.'/');
				$this->assertResponseIsSuccessful();
				$this->assertEquals(count($response->toArray()), 2);
		}
		
		public function testVer()
		{
				$response = static::createClient()
				                  ->request('GET', self::URL.'/3/ver', [
				                  ]);
				$this->assertResponseIsSuccessful();
				$resultado = $response->toArray();
				//\var_dump($resultado);
				$this->assertEquals('Ford', $resultado['marca']);
				$this->assertEquals('Auto', $resultado['tipo']);
		}
		
		public function testEdit()
		{
				$id = 3;
				// Chequeo Original
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Ford', $vehiculo->getMarca());
				$this->assertEquals('Focus', $vehiculo->getModelo());
				
				//// Cambio los datos
				$vehiculo = [
					'marca'     => 'Renault',
					'modelo'    => '12',
					'color'     => 'gris',
					'matricula' => 'VI1986',
					'venta'     => null,
				];
				$response = static::createClient()
				                  ->request('GET', self::URL."/$id/edit", [
					                  'json' => $vehiculo,
				                  ]);
				
				$resultado = $response->toArray();
				\var_dump($resultado);
				
				$this->assertEquals('ok', $resultado['resultado']);
				$vehiculo = $resultado['vehiculo'];
				$this->assertEquals('Renault', $vehiculo['marca']);
				$this->assertEquals('12', $vehiculo['modelo']);
				
				///// Chequeooo en la DB
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Renault', $vehiculo->getMarca());
				$this->assertEquals('12', $vehiculo->getModelo());
		}
		
		
		public function testEditNoDisponible()
		{
				// Chequeo Original
				$id            = 4;
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Ford', $vehiculo->getMarca());
				$this->assertEquals('Falco', $vehiculo->getModelo());
				
				//// Cambio los datos
				$vehiculo = [
					'marca'     => 'Renault',
					'modelo'    => '12',
					'color'     => 'gris',
					'matricula' => 'VI1986',
				];
				$response = static::createClient()
				                  ->request('GET', self::URL."/$id/edit", [
					                  'json' => $vehiculo,
				                  ]);
				
				$resultado = $response->toArray();
				//\var_dump($resultado);
				$this->assertEquals('ERROR', $resultado['resultado']);
				$vehiculo = $resultado['vehiculo'];
				$this->assertEquals('Renault', $vehiculo['marca']);
				$this->assertEquals('12', $vehiculo['modelo']);
				
				///// Chequeooo en la DB
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Ford', $vehiculo->getMarca());
				$this->assertEquals('Falco', $vehiculo->getModelo());
		}
		
		public function testRematricularMoto()
		{
				// Chequeo Original
				$id            = 1;
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Yamaha', $vehiculo->getMarca());
				$this->assertEquals('cross', $vehiculo->getModelo());
				$this->assertEquals('123A', $vehiculo->getMatricula());
				//// Cambio los datos
				
				$response = static::createClient()
				                  ->request('GET', self::URL."/$id/rematricular", [
				                  ]);
				
				$resultado = $response->toArray();
				//\var_dump($resultado);
				$this->assertEquals('ok', $resultado['resultado']);
				$vehiculo = $resultado['vehiculo'];
				$this->assertEquals('Yamaha', $vehiculo['marca']);
				$this->assertEquals('cross', $vehiculo['modelo']);
				$this->assertNotEquals('123A', $vehiculo['matricula']);
				///// Chequeooo en la DB
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Yamaha', $vehiculo->getMarca());
				$this->assertEquals('cross', $vehiculo->getModelo());
				$this->assertNotEquals('123A', $vehiculo->getMatricula());
		}
		
		public function testRematricularAuto()
		{
				// Chequeo Original
				$id            = 3;
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Ford', $vehiculo->getMarca());
				$this->assertEquals('Focus', $vehiculo->getModelo());
				$this->assertEquals('AA1234', $vehiculo->getMatricula());
				//// Cambio los datos
				
				$response = static::createClient()
				                  ->request('GET', self::URL."/$id/rematricular", [
				                  ]);
				
				$resultado = $response->toArray();
				//\var_dump($resultado);
				$this->assertEquals('ok', $resultado['resultado']);
				$vehiculo = $resultado['vehiculo'];
				$this->assertEquals('Ford', $vehiculo['marca']);
				$this->assertEquals('Focus', $vehiculo['modelo']);
				$this->assertNotEquals('AA1234', $vehiculo['matricula']);
				///// Chequeooo en la DB
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Ford', $vehiculo->getMarca());
				$this->assertEquals('Focus', $vehiculo->getModelo());
				$this->assertNotEquals('AA1234', $vehiculo->getMatricula());
				
		}
		
		/**
		 * Debe pasarlo a no Disponible
		 * @return void
		 * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
		 * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
		 * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
		 * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
		 * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
		 */
		public function testVender()
		{
				// Chequeo Original
				$id            = 1;
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Yamaha', $vehiculo->getMarca());
				$this->assertEquals('cross', $vehiculo->getModelo());
				$this->assertTrue($vehiculo->isDisponible());
				
				//// Cambio los datos
				
				$response = static::createClient()
				                  ->request('GET', self::URL."/$id/vender", [
					                  'json' => $vehiculo,
				                  ]);
				
				$resultado = $response->toArray();
				//\var_dump($resultado);
				$this->assertEquals('ok', $resultado['resultado']);
				$vehiculo = $resultado['vehiculo'];
				$this->assertEquals('Yamaha', $vehiculo['marca']);
				$this->assertEquals('cross', $vehiculo['modelo']);
				$this->assertNotNull($vehiculo['venta']);
				///// Chequeooo en la DB
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Yamaha', $vehiculo->getMarca());
				$this->assertEquals('cross', $vehiculo->getModelo());
				$this->assertFalse($vehiculo->isDisponible());
		}
		
		/**
		 * No se puede vender 2 veces, lo vendido y no se puede volver a vender
		 * @return void
		 * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
		 * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
		 * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
		 * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
		 * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
		 */
		public function testVender2VecesError()
		{
				// Chequeo Original
				$id            = 1;
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Yamaha', $vehiculo->getMarca());
				$this->assertEquals('cross', $vehiculo->getModelo());
				$this->assertFalse($vehiculo->isDisponible());
				
				//// Cambio los datos
				
				$response = static::createClient()
				                  ->request('GET', self::URL."/$id/vender", [
					                  'json' => $vehiculo,
				                  ]);
				
				$resultado = $response->toArray();
				//\var_dump($resultado);
				$this->assertEquals('error', $resultado['resultado']);
				$vehiculo = $resultado['vehiculo'];
				$this->assertEquals('Yamaha', $vehiculo['marca']);
				$this->assertEquals('cross', $vehiculo['modelo']);
				$this->assertNotNull($vehiculo['venta']);
				///// Chequeooo en la DB
				$container     = static::getContainer();
				$entityManager = $container->get('doctrine')
				                           ->getManager();
				$vehiculo      = $entityManager->getRepository(Vehiculo::class)
				                               ->find($id);
				$this->assertEquals('Yamaha', $vehiculo->getMarca());
				$this->assertEquals('cross', $vehiculo->getModelo());
				$this->assertFalse($vehiculo->isDisponible());
		}
		
		
}


