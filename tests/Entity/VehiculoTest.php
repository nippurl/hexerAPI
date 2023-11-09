<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Vehiculo;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class VehiculoTest extends ApiTestCase
{
		use ReloadDatabaseTrait;
		
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
		
		public function testDisponible(): void{
				$container  = static::getContainer();
				$entityManager = $container->get('doctrine')->getManager();
				/** @var  $vehiculo Vehiculo */
				$vehiculo = $entityManager->getRepository(Vehiculo::class)->find(3);
				$this->assertTrue($vehiculo->isDisponible());
				$vehiculo = $entityManager->getRepository(Vehiculo::class)->find(2);
				$this->assertFalse($vehiculo->isDisponible());
		}
		
		public function testVender(): void{
				$container  = static::getContainer();
				$entityManager = $container->get('doctrine')->getManager();
				/** @var  $vehiculo Vehiculo */
				$vehiculo = $entityManager->getRepository(Vehiculo::class)->find(1);
				$this->assertTrue($vehiculo->isDisponible());
				$vehiculo->vendido();
				$entityManager->persist($vehiculo);
				$entityManager->flush();
				$vehiculo = $entityManager->getRepository(Vehiculo::class)->find(1);
				$this->assertFalse($vehiculo->isDisponible());
		}
}
