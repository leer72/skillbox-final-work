<?php

namespace App\Service;

use Faker\Factory;
use App\Entity\Keyword;
use App\Twig\StringLoader;

class ArticleContentProvider
{
    private $loader;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    private static $placeholderParagraph = '{{ paragraph }}';

    private static $placeholderParagraphs = '{{ paragraphs }}';
    
    public function __construct(StringLoader $loader)
    {
        $this->loader = $loader;
        $this->faker = Factory::create();
    }
    
    public function getBody(Keyword $keyword, array $words = [], int $modules = 3): string
    {
        // Пока не реализован класс модулей - делаем их статичными
        $baseModules[] = <<<EOF
        <h>{{ keyword }}</h>
        <p>{{ paragraph }}</p> 

        EOF;
        $baseModules[] = <<<EOF
        <p class="text-right">{{ paragraph }} {{ keyword }} </p>.  

        EOF;
        $baseModules[] = <<<EOF
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
            for ($i = 1; $i <= $modules; $i++) {
                $modulesPool[] = $baseModules[rand(0, count($baseModules) - 1)];
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
        foreach($words as $word => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $wordsPool[] = $word;
                $wordsCount++;
            }
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
            $insertWordsCount = ($paragraphCount + $paragraphsCount > $i) ? rand(0, count($wordsPool)) : count($wordsPool); 
            
            $textAsArray = explode(' ', $text);
            
            for($j = 0; $j < $insertWordsCount; $j++) {
                $deletedPos = rand(0, count($wordsPool) - 1);
                if($deletedPos < 0) {
                    $deletedPos = 0;
                }
                array_splice($textAsArray, rand(1, count($textAsArray) - 1), 0, $wordsPool[$deletedPos]); //вставляем в текст(массив)
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

        $twig = new \Twig\Environment($this->loader);
        
        return $twig->render($modulesAsText, [  // Для обработки словоформ бует реализовано расширение твиг
            'keyword' => $keyword,
        ]);
    }

    public function getTitle(string $title, Keyword $keyword): string
    {
        $twig = new \Twig\Environment($this->loader);
        
        return $twig->render('<h1> ' . $title . ' </h1>', [
            'keyword' => $keyword,
        ]);
    }
}
