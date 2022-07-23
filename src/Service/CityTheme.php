<?php

namespace App\Service;

use App\Entity\Keyword;
use App\Service\ThemeInterface;

class CityTheme implements ThemeInterface
{
    private static $text = '<h3 class="tourism-city-stacked-block__heading">О городе {{ value }}</h3>
    <h4 class="tourism-city-stacked-block__subheading">Посмотрите рекомендации, когда лучше побывать в {{ value }}</h4>
    <div class="tourism-city-stacked-block__content-container" data-et-view="IZMXbFRURURYeIbVSEdLRSUPRALRe:4">
    <div class="bui-grid">
    <div class="bui-grid__column-8">
    <div class="tourism-lp-description__content-container">
    <p>{{ value }} — один из самых очаровательных городов на берегу Атлантического океана. Отправляйтесь на прогулку по португальской столице, чьи мощеные улочки заполняют звуки фаду и аромат традиционных сладостей. Или прокатитесь на винтажном трамвае №28 — одном из последних действующих трамваев в своем поколении. Это отличный вариант, чтобы посмотреть популярные районы Граса, Алфама, Байша и Эстрела.</p>
    <p>
    Центральный район Байрру-Алту — прекрасное место для дегустации местной кухни, которой славится столица. Попробуйте бакалао абраш (блюдо из трески с луком, яйцом и картофелем) или традиционное тушеное мясо по-португальски.</p>
    <p>
    Если вас интересует история и живописные виды, посетите замок Святого Георгия или воспользуйтесь подъемником Санта-Жушта. В {{ value }} также находится мост имени Васко да Гамы, самый длинный в Европе, а также Мост 25 апреля, напоминающий «Золотые ворота» Сан-Франциско.</p>
    <p>
    {{ value }} пользуется популярностью у серферов, особенно полюбивших районы Каркавелуш и Кошта да Капарика за длинные песчаные пляжи.</p>
    </div>
    <div class="tourism-lp-price-deals__container">
    <div class="bui-grid">
    <div class="bui-grid__column-4">
    ';

    private static $title = '<h1> {{ value }} - лучший город для путешествия </h1>';

    private Keyword $value;

    private static $slug = 'city_theme';

    private static $name = 'Про города';

    public function __construct()
    {
        $keyword = new Keyword();
        $keyword->setKeyword('Улан-Удэ');
        $this->value = $keyword;
    }

    public function getParagraphs(Keyword $value)
    {
        $text = str_replace('{{ value }}', ($value) ? $value->getKeywordForm() : $this->value->getKeywordForm(), self::$text);
        
        return $text;;
    }

    public function getTitle(Keyword $value)
    {
        $title = str_replace('{{ value }}', ($value) ? $value->getKeywordForm() : $this->value->getKeywordForm(), self::$title);
        
        return $title;
    }

    public function getSlug(): string
    {
        return self::$slug;
    }

    public function getName()
    {
        return self::$name;
    }
}