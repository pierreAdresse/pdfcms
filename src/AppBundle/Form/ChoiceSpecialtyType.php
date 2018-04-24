<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\Specialty;

class ChoiceSpecialtyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $specialties = $options['data'];

        $builder
            ->add('specialties', ChoiceType::class, [
                'choices' => $specialties,
                'choice_label' => function($specialty, $key, $index) {
                    return $specialty->getName();
                },
                'expanded' => true,
                'multiple' => true,
                'data' => $options['defaultSpecialties'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'defaultSpecialties' => '',
        ]);
    }
}
