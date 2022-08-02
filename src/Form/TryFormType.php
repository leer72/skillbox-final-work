<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок статьи',
            ])
            ->add('keyword', TextType::class, [
              'mapped' => false,
            ])
        ;

        $builder
            ->get('title')
            ->addModelTransformer(new CallbackTransformer(
                function ($titleFromDatabase) {
                    $titleFromDatabase = substr_replace($titleFromDatabase, '', 0, 4);
                    return substr_replace($titleFromDatabase, '', -5);
                },
                function ($titleFromInput) {
                    return '<h1> ' . $titleFromInput . ' </h1>';
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
