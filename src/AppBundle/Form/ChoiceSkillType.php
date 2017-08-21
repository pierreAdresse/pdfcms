<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\Skill;

class ChoiceSkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $skills = $options['data'];

        $builder
            ->add('skills', ChoiceType::class, [
                'choices' => $skills,
                'choice_label' => function($skill, $key, $index) {
                    return $skill->getName();
                },
                'expanded' => true,
                'multiple' => true,
                'data' => $options['defaultSkills'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'defaultSkills' => '',
        ]);
    }
}
