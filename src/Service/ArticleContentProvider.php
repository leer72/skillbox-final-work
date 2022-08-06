<?php

namespace App\Service;

use Faker\Factory;
use App\Entity\User;
use Twig\Environment;
use App\Entity\Article;
use App\Entity\Keyword;
use Doctrine\Common\Collections\Collection;

class ArticleContentProvider
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    private static $placeholderParagraph = '{{ paragraph }}';

    private static $placeholderParagraphs = '{{ paragraphs }}';

    private static $placeholderImages = '{{ imageSrc }}';

    private $twig;
    
    public function __construct(Environment $twig)
    {
        $this->faker = Factory::create();
        $this->twig = $twig;
    }
    
    public function getBody(Article $article, User $user = null, Collection $words = null, int $modules = 3): string
    {
        // Пока не реализован класс модулей - делаем их статичными
        $baseModules[] = <<<EOF
        <h>{{ keyword | morph(0) }}
        {{ keyword | morph(1) }}
        {{ keyword | morph(2) }}
        {{ keyword | morph(3) }}
        {{ keyword | morph(4) }}
        {{ keyword | morph(5) }}
        {{ keyword | morph(6) }}</h>
        <p>{{ paragraph }}</p> 

        EOF;
        $baseModules[] = <<<EOF
        <p class="text-right">{{ paragraph }} {{ keyword }} </p>.  

        EOF;
        $baseModules[] = <<<EOF
        <img src="{{ imageSrc }}" alt="изображение">
        <div class="row">
        <div class="col-sm-6">
            {{ paragraphs }}
            {{ keyword }}
        </div>
        <div class="col-sm-6">
            {{ paragraphs }}
        </div>
        </div>  

        EOF;

        if($modules == 0) {
            
            return '';
        }
        
        $modulesAsText = '';

        $modulesPool = []; //Соберем сюда пул модулей для генерации статьи 

        if ($modules > 0) {
            $moduleTemplates = ($user ?? null) ? ($user->getModules()->toArray() ?? $baseModules) : $baseModules;
            
            for ($i = 1; $i <= $modules; $i++) {
                $modulesPool[] = $moduleTemplates[array_rand($moduleTemplates)];
            }
        } else {
            
            return '';
        }
        
        // Генерируем текст из пула модулей
        foreach($modulesPool as $module) { 
            $modulesAsText .= $module;
        }
        
        $wordsPool = [];
        $wordsCount = 0;

        // Создаем пул вставляемых слов
        if(null != $words) {
            foreach($words as $word) {
                for ($i = 1; $i <= $word->getCount(); $i++) {
                    $wordsPool[] = $word->getWord();
                    $wordsCount++;
                }
            }
        }

        // создаем пул изображений
        $imagesNumberPool = [];
        $imagesCount = substr_count($modulesAsText, self::$placeholderImages);
        $images = $article->getImagesFilename();
        // изображения не повторяются если их количество <= плейсхолдерам
        if(count($images)) {
            if($imagesCount > count($images)) {
                $imagesNumberPool = array_keys($images);
                for($i = 0; $i <= $imagesCount - count($images); $i++) {
                    $imagesNumberPool[] = array_rand($images);
                }
            } elseif($imagesCount == count($images)) {
                $imagesNumberPool = array_keys($images);
            } else {
                for($i = 0; $i <= $imagesCount; $i++) {
                    $deletedPos = array_rand($images);
                    $imagesNumberPool[] = $deletedPos;
                    array_splice($images, $deletedPos, 1);
                } 
            }
        }
        
        // меняем плейсхолдер изображения
        for($i = 0; $i < $imagesCount; $i++) {
            $pos = strpos($modulesAsText, self::$placeholderImages);
            $modulesAsText = substr_replace(
                $modulesAsText, 
                (count($images) > 0) ? '{{ article.ImageFilename(' . $imagesNumberPool[$i] . ") | imagine_filter('articles') }}" : '', 
                $pos, 
                strlen(self::$placeholderImages)
            );
        }
        
        $paragraphCount = substr_count($modulesAsText, self::$placeholderParagraph);
        $paragraphsCount = substr_count($modulesAsText, self::$placeholderParagraphs);

        // при каждой замене плейсхолдера {{ paragraph }} или {{ paragraphs }} добавляем слова из пула
        for ($i = 1; $i <= $paragraphCount + $paragraphsCount; $i++) {
            $text ='';
            if($i > $paragraphCount) { //Если заменяем {{ paragraphs }}
                $pos = strpos($modulesAsText, self::$placeholderParagraphs);
                for($j = 1; $j <= rand(1, 3); $j++) {
                    $text .= '<p> ' . $this->faker->text(200) . ' </p>'; //то ставим тэг <p> в каждый параграф
                }
            } else {
                $pos = strpos($modulesAsText, self::$placeholderParagraph);
                $text = $this->faker->text(200);
            }

            //на каждой итерации берем случайное количество слов
            //если эта итерация последняя - учитываем все оставшиеся слова
            if(count($wordsPool)) {
                $insertWordsCount = ($paragraphCount + $paragraphsCount > $i) ? array_rand($wordsPool) : count($wordsPool);
            } else {
                $insertWordsCount = 0;
            }
             
            $textAsArray = explode(' ', $text);
            
            for($j = 0; $j < $insertWordsCount; $j++) {
                $deletedPos = array_rand($wordsPool);
                
                array_splice($textAsArray, array_rand($textAsArray), 0, $wordsPool[$deletedPos]); //вставляем в текст(массив)
                array_splice($wordsPool, $deletedPos, 1); //удаляем из пула
            }
            
            $text = implode(' ', $textAsArray); //собираем обратно в текст

            //и меняем плейсхолдер на текст
            $modulesAsText = substr_replace(
                $modulesAsText, 
                $text, 
                $pos, 
                ($i > $paragraphCount) ? strlen(self::$placeholderParagraphs) : strlen(self::$placeholderParagraph)
            );
        }

        return (
            $article->getKeyword()) ? 
            $this->twig->render($modulesAsText, [
                'keyword' => $article->getKeyword(),
                'article' => $article,
                ]) : 
            $this->twig->render($modulesAsText, [
                'keyword' => (new Keyword)->setKeyword(''),
                'article' => $article,
            ])
        ;
    }

    public function getTitle(string $title, Keyword $keyword): string
    {
        return (count($keyword->getKeyword())) ? $this->twig->render($title, ['keyword' => $keyword,]) : $this->twig->render($title);
    }
}
