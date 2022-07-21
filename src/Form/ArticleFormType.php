<?php

namespace App\Form;

use App\Entity\Article;
use App\Service\ThemeContentProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ArticleFormType extends AbstractType
{
    private static $MAX_KEYWORD_SIZE = 6;

    private $contentProvider;

    public function __construct(ThemeContentProvider $themeContentProvider)
    {
        $this->contentProvider = $themeContentProvider;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $themes = $this->contentProvider->getThemes();
        
        $builder
            ->add('theme', ChoiceType::class, [
                'choices' => $themes,
                'mapped' => false,
                'placeholder' => 'Выберите тематику',
                'required'   => false,
            ])
            ->add('title', TextType::class, [
                'label' => 'Заголовок статьи',
                'required'   => false,
            ])
            ->add('sizeFrom', NumberType::class, [
                'label' => 'Размер статьи от',
                'required'   => false,
            ])
            ->add('sizeTo', NumberType::class, [
                'label' => 'до',
                'required'   => false,
            ])
            ->add('words', CollectionType::class, [
                'entry_type' => WordsType::class,
                'allow_add' => true,
                'by_reference' => false,
                'label' => ' ',
            ])
            
            ->add('image', FileType::class, [
                'label' => 'Изображения',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
            ])
        ;

        for($i = 0; $i <= self::$MAX_KEYWORD_SIZE; $i++) {
            $builder->add('keyword_' . $i, TextType::class, [
                'mapped' => false,
                'required'   => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
