<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\Skill;

class ChoiceCinescenieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cinescenies = $options['data'];

        $builder
            ->add('cinescenie', ChoiceType::class, [
                'choices' => $cinescenies,
                'choice_label' => function($cinescenie, $key, $index) {
                    $formatter = new \IntlDateFormatter(
                        'fr_FR',
                        \IntlDateFormatter::FULL,
                        \IntlDateFormatter::NONE,
                        'Europe/Paris',
                        \IntlDateFormatter::GREGORIAN,
                        'EEEE d MMMM yyyy à HH:mm'
                    );

                    return ucfirst($formatter->format($cinescenie->getDate()));
                },
            ]
        );
    }
}
