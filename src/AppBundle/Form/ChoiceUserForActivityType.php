<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\Skill;

class ChoiceUserForActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $users = $options['data'];

        $builder
            ->add('users', ChoiceType::class, [
                'choices' => [
                    $options['activityName'] => $users,
                    'Autre(s)' => $options['secondaryUsers'],
                ],
                'choice_label' => function($user, $key, $index) {
                    return $user->getFirstname().' '.$user->getLastname();
                },
                'data' => $options['userSelected'],
                'empty_data' => null,
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'userSelected'   => null,
            'secondaryUsers' => [],
            'activityName'   => '',
        ]);
    }
}
