<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class WalletType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('clientEmail', TextType::class, array(
                'required' => true,
                'label' => 'Email *',
            ))
            ->add('clientFirstname', TextType::class, array(
                'required' => true,
                'label' => 'Prénom *',
            ))
            ->add('clientLastname', TextType::class, array(
                'required' => true,
                'label' => 'Nom *',
            ))
            ->add('clientMobileNumber', TelType::class, array(
                'required' => true,
                'label' => 'Téléphone mobile *',
            ))
            ->add('clientBirthdate', BirthdayType::class, array(
                'required' => true,
                'label' => 'Date d\'anniversaire *',
                'placeholder' => array(
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                ),
                'format' => 'dd/MM/yyyy'
            ))
            ->add('clientCtry', ChoiceType::class, array(
                'choices' => array(
                    "France" => "FRA",
                ),
                'required' => true,
                'label' => 'Pays de résidence *',
                'multiple' => false,
            ))
        ;
    }
}
