<?php

namespace App\Controller;

use App\Entity\Vehiculo;
use App\Form\VehiculoType;
use App\Repository\VehiculoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class ApiController extends AbstractController
{
		
		#[Route('/', name: 'api_index', methods: ['GET'])]
		public function index(VehiculoRepository $vehiculoRepository, Request $request): Response
		{
				if ($request->query->has('q')) {
						$q         = $request->query->get('q');
						$vehiculos = $vehiculoRepository->findQuery($q);
				} else {
						$vehiculos = $vehiculoRepository->findDisponibles();
				}
				$respuesta = [];
				foreach ($vehiculos as $vehiculo) {
						$respuesta[] = $vehiculo->jsonSerialize();
				}
				$response = new Response(json_encode($respuesta), 200);
				$response->headers->set('Content-Type', 'application/json');
				
				return $response;
		}
		
		/**
		 * @param \Symfony\Component\HttpFoundation\Request $request
		 * @param \Doctrine\ORM\EntityManagerInterface      $entityManager
		 * @param string                                    $tipo El tipo creado
		 * @return \Symfony\Component\HttpFoundation\Response
		 * @throws \JsonException
		 */
		#[Route('/new/{tipo}', name: 'api_new', methods: ['GET', 'POST'])]
		public function new(Request $request, EntityManagerInterface $entityManager, string $tipo): Response
		{
				/** @var \App\Modelo\VehiculoInterface $vehiculo */
				$vehiculo = Vehiculo::create($tipo);
				$form     = $this->createForm(VehiculoType::class, $vehiculo);
				$form->submit($request->toArray());
				
				$entityManager->persist($vehiculo);
				$entityManager->flush();
				$resultado = ['resultado' => 'ok', 'vehiculo' => $vehiculo->jsonSerialize()];
				
				$response = new Response(json_encode($resultado, JSON_THROW_ON_ERROR), 201);
				$response->headers->set('Content-Type', 'application/json');
				
				return $response;
		}
		
		#[Route('/{id}/ver', name: 'api_show', methods: ['GET'])]
		public function show(Vehiculo $vehiculo): Response
		{
				$response = new Response(json_encode($vehiculo->jsonSerialize()), 200);
				$response->headers->set('Content-Type', 'application/json');
				return $response;
				
		}
		
		#[Route('/{id}/edit', name: 'api_edit', methods: ['GET', 'POST'])]
		public function edit(Request $request, Vehiculo $vehiculo, EntityManagerInterface $entityManager): Response
		{
				$form = $this->createForm(VehiculoType::class, $vehiculo);
				$form->submit($request->toArray());
				try {
						$entityManager->persist($vehiculo);
						$entityManager->flush();
						$resultado = ['resultado' => 'ok'];
						
				} catch (\Exception $e) {
						$resultado = [
							'resultado' => 'ERROR',
							'error'     => $e->getMessage(),
						];
				}
				$resultado['vehiculo'] = $vehiculo->jsonSerialize();
				
				$response = new Response(json_encode($resultado, JSON_THROW_ON_ERROR), 200);
				$response->headers->set('Content-Type', 'application/json');
				return $response;
		}
		
		
		#[Route('/{id}/rematricular', name: 'api_rematricular', methods: ['GET'])]
		public function rematricular(Vehiculo $vehiculo, EntityManagerInterface $entityManager): Response
		{
				$mat = $vehiculo->rematricular();
				$entityManager->persist($vehiculo);
				$entityManager->flush();
				$resultado = [
					'resultado' => 'ok',
					'vehiculo'  => $vehiculo->jsonSerialize(),
				];
				$response  = new Response(json_encode($resultado, JSON_THROW_ON_ERROR), 200);
				$response->headers->set('Content-Type', 'application/json');
				return $response;
		}
		
		#[Route('/{id}/vender', name: 'api_vender', methods: ['GET'])]
		public function Vehiculo(Vehiculo $vehiculo, EntityManagerInterface $entityManager): Response
		{
				if ($vehiculo->isDisponible()) {
						$vehiculo->vendido();
						$entityManager->persist($vehiculo);
						$entityManager->flush();
						$resultado = [
							'resultado' => 'ok',
							'vehiculo'  => $vehiculo->jsonSerialize(),
						];
				}else{
						$resultado = [
							'resultado' => 'ERROR',
							'error'     => 'NO esta Disponible',
							'vehiculo'  => $vehiculo->jsonSerialize(),
						];
				}
				$response = new Response(json_encode($resultado, JSON_THROW_ON_ERROR), 200);
				$response->headers->set('Content-Type', 'application/json');
				return $response;
		}
		
		
		#[Route('/verificar', name: 'api_verificar', methods: [ 'GET','POST'])]
		public function verificar(Request $request): Response
		{
				$arr = $request->toArray();
				try{
						// Buscar el Tipo y crear el objeto
						$tipo = $arr['tipo'];
						$vehiculo = Vehiculo::create($tipo);
						$form = $this->createForm(VehiculoType::class, $vehiculo);
						$form->submit($arr);
						$resultado = ['resultado' => 'ok'];
				}catch (\Exception $e){
						$resultado = [
							'resultado' => 'ERROR',
							'error'     => $e->getMessage(),
						];
				}
				$response = new Response(json_encode($resultado, JSON_THROW_ON_ERROR), 200);
				$response->headers->set('Content-Type', 'application/json');
				return $response;
		}
}
