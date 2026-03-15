<?php

namespace App\Form;

use App\Entity\InfoPage;
use App\Entity\MenuItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemChildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Libellé',
            ])
            ->add('systemRoute', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Lien système',
                'required' => false,
                'placeholder' => '-- Aucun --',
                'choices' => [
                    'Connexion'               => 'app_login',
                    'Inscription'             => 'app_register',
                    'Mon compte'              => 'user_dashboard',
                    'Administration'          => 'admin',
                    'Exercices de respiration' => 'breathing_index',
                ],
            ])
            ->add('infoPage', EntityType::class, [
                'class' => InfoPage::class,
                'choice_label' => 'title',
                'label' => 'Page du site',
                'required' => false,
                'placeholder' => '-- URL personnalisée --',
            ])
            ->add('url', TextType::class, [
                'label' => 'URL personnalisée',
                'required' => false,
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'data' => 0,
            ])
            ->add('isActive', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'data' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuItem::class,
        ]);
    }
}
