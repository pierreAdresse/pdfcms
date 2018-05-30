<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\Skill;

class ChoiceMemberForActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $members = $options['data'];

        $builder
            ->add('members', ChoiceType::class, [
                'choices' => [
                    $options['activityName'] => $members,
                    'Autre(s)' => $options['secondaryMembers'],
                ],
                'choice_label' => function($member, $key, $index) {
                    if (is_null($member)) {
                        return '';
                    } else {
                        $new = '';
                        if ($member->getIsNew()) {
                            $new = ' #NOUVEAU#';
                        }
                        return $member->getFirstname().' '.$member->getLastname().$new;
                    }
                },
                'data' => null,
                'empty_data' => null,
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'secondaryMembers' => [],
            'activityName'   => '',
        ]);
    }
}
