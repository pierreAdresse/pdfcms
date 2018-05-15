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
                    $day   = $this->getDayFr($cinescenie->getDate());
                    $month = $this->getMonthFr($cinescenie->getDate());
                    $dayNum = $cinescenie->getDate()->format('j');
                    $then   = $cinescenie->getDate()->format('Y à H:i');
                    return "$day $dayNum $month $then";

                    /*
                    $formatter = new \IntlDateFormatter(
                        'fr_FR',
                        \IntlDateFormatter::FULL,
                        \IntlDateFormatter::NONE,
                        'Europe/Paris',
                        \IntlDateFormatter::GREGORIAN,
                        'EEEE d MMMM yyyy à HH:mm'
                    );

                    return ucfirst($formatter->format($cinescenie->getDate()));
                    */
                },
            ]
        );
    }

    private function getDayFr($date)
    {
        switch ($date->format('w')) {
            case 0:
                return 'Dimanche';
                break;
            case 1:
                return 'Lundi';
                break;
            case 2:
                return 'Mardi';
                break;
            case 3:
                return 'Mercredi';
                break;
            case 4:
                return 'Jeudi';
                break;
            case 5:
                return 'Vendredi';
                break;
            case 6:
                return 'Samedi';
                break;
        }
    }

    private function getMonthFr($date)
    {
        switch ($date->format('n')) {
            case 1:
                return 'janvier';
                break;
            case 2:
                return 'février';
                break;
            case 3:
                return 'mars';
                break;
            case 4:
                return 'avril';
                break;
            case 5:
                return 'mai';
                break;
            case 6:
                return 'juin';
                break;
            case 7:
                return 'juillet';
                break;
            case 8:
                return 'août';
                break;
            case 9:
                return 'septembre';
                break;
            case 10:
                return 'octobre';
                break;
            case 11:
                return 'novembre';
                break;
            case 12:
                return 'décembre';
                break;   
        }
    }
}
