<?php

namespace App\Form;

use App\Entity\Article;
use App\Service\ThemeContentProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use App\Service\BlaBlaArticleSubscriptionProvider;
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

    private $subscriptionProvider;

    private $security;

    public function __construct(ThemeContentProvider $themeContentProvider, BlaBlaArticleSubscriptionProvider $subscriptionProvider, Security $security)
    {
        $this->contentProvider = $themeContentProvider;
        $this->subscriptionProvider = $subscriptionProvider;
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $avalibles = $this->subscriptionProvider->getSubscriptionByUser($this->security->getUser());
        
        $themes = $this->contentProvider->getThemes();

        $article = $options['data'] ?? null;
        
        $cannotEdit = $article && $article->getId();

        $builder
            ->add('theme', ChoiceType::class, [
                'choices' => $themes,
                'mapped' => false,
                'placeholder' => 'Выберите тематику',
                'required'   => false,
                'disabled' => $cannotEdit,
            ])
            ->add('title', TextType::class, [
                'label' => 'Заголовок статьи',
                'required'   => false,
                'disabled' => $cannotEdit,
            ])
            ->add('sizeFrom', NumberType::class, [
                'label' => 'Размер статьи от',
                'required'   => false,
                'disabled' => $cannotEdit,
            ])
            ->add('sizeTo', NumberType::class, [
                'label' => 'до',
                'required'   => false,
                'disabled' => $cannotEdit,
            ])
            ->add('words', CollectionType::class, [
                'entry_type' => WordsType::class,
                'allow_add' => true,
                'by_reference' => false,
                'label' => ' ',
                'disabled' => $cannotEdit,
            ])
            
            ->add('image', FileType::class, [
                'label' => 'Изображения',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'disabled' => $cannotEdit || ! $avalibles->getAvalibleImages(),
            ])
        ;

        $builder->add('keyword_0', TextType::class, [
            'mapped' => false,
            'required'   => false,
            'disabled' => $cannotEdit,
        ]);
        
        for($i = 1; $i <= self::$MAX_KEYWORD_SIZE; $i++) {
            $builder->add('keyword_' . $i, TextType::class, [
                'mapped' => false,
                'required'   => false,
                'disabled' => $cannotEdit || ! $avalibles->getAvalibleKeywordMorphs(),
            ]);
        }

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
