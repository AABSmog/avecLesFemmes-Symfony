<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Dto\GroupeSearchDto;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;


class GroupeSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', SearchType::class, [
                'required' => false,
                'label' => 'Rechercher un groupement',
                'label_attr' => [
                    'class' => 'visuellement-cache',
                ],
                'attr' => [
                    'placeholder' => 'Nom ou ville',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => [
                    'class' => 'bouton',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GroupeSearchDto::class,
            // GET : l'URL porte le filtre, elle est donc partageable et rechargeable.
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        // Sans préfixe, l'URL reste /groupement?query=mbour plutôt que groupe_search[query].
        return '';
    }
}
