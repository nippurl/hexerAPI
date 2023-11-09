<?php

namespace App\Form;

use App\Entity\Vehiculo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class VehiculoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//	        ->add('tipo', Choice::class,[
//						'choices' => Vehiculo::VehiculoChoices(),
//	        ])
            ->add('marca')
            ->add('modelo')
            ->add('color')
            ->add('matricula')
	     //   ->add('venta')
	        ->addEventListener(
		        FormEvents::POST_SUBMIT,
		        [$this, 'validarMatricula']
	        )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehiculo::class,
						'csrf_protection' => false,
        ]);
    }
		
		/**
		 * Valida en cada caso la matricula utilizado la funcion de validacion
		 * @return void
		 */
		public function validarMatricula (FormEvent $event){
				/** @var Vehiculo $data */
				$data = $event->getData();
				/** @var \App\Form\MovimientoType $form */
				$form = $event->getForm();
				if (!$data->validadorMatricula()) {
						$form->get('matricula')->addError(new FormError('La matricula no es correcta'));
				}
				
				
				return;
		}
}
