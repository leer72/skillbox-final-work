<?php

namespace App\Service;

use App\Entity\Keyword;
use App\Service\ThemeInterface;

class CucumberTheme implements ThemeInterface
{
    private static $text = '    <div class="content_holder">
    <div class="maincontent">
        <div data-id="a1" class="box" id="1">
            <div class="text">
                <p>Если вы посадили на даче <strong>огурец {{ value }}</strong>, будьте готовы к небывалым урожаям. Это уже проверили миллионы дачников.</p>
<p>Огурец {{ value }} получил поистине народное признание не случайно. С одного растения вы получите 6-8 кг плодов. А первые огурчики созревают уже на 45-50 день после посадки. Плодоносит до самой осени в отличие от спринтерских гибридов, которые через 2 месяца уже заканчивают рост и образование плодов.</p>
<p>Не подвел и вкус: <strong>хрустящие, без горечи плоды</strong>, хороши и в свежем виде, и для засолки. Сорт отлично растет во всех климатических зонах нашей страны.</p>
<p>Вот уже 15 лет огурец {{ value }} держит <strong>первенство по урожайности</strong> среди других сортов. Ежегодно у нас покупают более 3 млн упаковок семян этого сорта.</p>
<p><strong>Выращивайте на даче только вкусные и полезные овощи!</strong></p>
            </div>
        <div data-id="a2" class="box">
            <div class="text">
                <p>&nbsp;</p>
<h2>Описание сорта {{ value }}</h2>
<h3>Вкусный:</h3>
<p>— <strong>Тонкая</strong> нежная кожица, ароматные плоды</p>
<p>— <strong>Отсутствие горечи и плодов-«крючков»</strong> заложены генетически и не зависят от стрессов</p>
<p>— <strong>Вкусен</strong> в свежем и консервированном виде<br />
— Сорванные огурцы не теряют своих свойств <strong>в течение 10 дней</strong></p>
<p><img class="alignleft wp-image-1035 size-full" src="https://semenagavrish.ru/oguretc-kurazh/wp-content/uploads/2019/03/pic-1.jpg" alt="Суперпучковый огурец сорта &quot;Кураж&quot;" width="200" height="295" /></p>
<h3>Скороспелый и урожайный:</h3>
<p>— От всходов до плодоношения <strong>45-50 дней</strong></p>
<p>— <strong>Суперпучковый:</strong> 5-10 завязей в узле</p>
<p>— Одновременный налив до 30 огурчиков</p>
<p>— Более 6 кг плодов с растения</p>
<p>— Масса одного огурца 120 &#8212; 130 г</p>
<h3>Не требует опыления:</h3>
<p>— Для рекордных урожаев в теплице</p>
<p>— Стабильно высокий урожай, независимо от погодных условий и присутствия насекомых-опылителей</p>
<p>— <strong>Нет пустоцветов</strong></p>
<h3>Выносливый:</h3>
<p>— <strong>Сорт устойчив</strong> к оливковой пятнистости, настоящей и ложной мучнистой росе, вирусу огуречной мозаики</p>
<p>— Отлично растет в различных климатических условиях</p>
            </div>
        </div>
    </div>
</div>';

    private static $title = 'Поговорим об огурцах сорта {{ value }}';

    private Keyword $value;

    private static $slug = 'cucumber_theme';

    private static $name = 'Про огурцы';

    public function __construct()
    {
        $keyword = new Keyword();
        $keyword->setKeyword('бенифис F1');
        $this->value = $keyword;
    }

    public function getParagraphs(Keyword $value = null)
    {
        $text = str_replace('{{ value }}', ($value) ? $value->getKeywordForm() : $this->value->getKeywordForm(), self::$text);
        
        return $text;
    }

    public function getTitle(Keyword $value = null)
    {
        $title = str_replace('{{ value }}', ($value) ? $value->getKeywordForm() : $this->value->getKeywordForm(), self::$title);
        
        return $title;
    }

    public function getSlug()
    {
        return self::$slug;
    }

    public function getName()
    {
        return self::$name;
    }
}