# Механизм реализации добавления товара в "избранное"

> Предложенный ниже вариант реализации не является окончательным и его следует рассматривать лишь как возможный пример решения поставленной задачи

### Содержание:

+ [Теоретические материалы](#RESOURCES_MAIN)
+ [(PHP) Wishlist Ajax Обработчик](#PHP_HANDLER)
+ [(PHP) Wishlist счетчик кнопка-иконка](#PHP_SALE_BASKET_BTN)
    + [(PHP) Wishlist модификация header.php](#PHP_TEMPLATE_HEADER)
    + [(PHP) Wishlist sale.basket.basket](#PHP_SALE_BASKET_BASKET)
        + [(PHP) Wishlist модификация template.php](#PHP_SALE_BASKET_BASKET_TEMPLATE)
        + [(PHP) Wishlist модификация basket_items.php](#PHP_SALE_BASKET_BASKET_ITEMS)
        + [(PHP) Wishlist модификация basket_items_delayed.php](#PHP_SALE_BASKET_BASKET_DELAYED)
+ [(PHP) Wishlist catalog.item (элементы раздела)](#PHP_CATALOG_ITEM)
    + [(PHP) Wishlist модификация шаблона](#PHP_CATALOG_ITEM_TEMPLATE)
    + [(JS) Wishlist модификация script.js](PHP_CATALOG_ITEM_SCRIPT)
+ [(PHP) Wishlist catalog.element (детальный просмотр элемента)](#PHP_CATALOG_ELEMENT)
    + [(PHP) Wishlist модификация шаблона](#PHP_CATALOG_ELEMENT_TEMPLATE)
    + [(JS) Wishlist модификация script.js](#PHP_CATALOG_ELEMENT_SCRIPT)
+ [Дополнительные материалы](#RESOURCES_ADDITIONAL)

### <a name="RESOURCES_MAIN"></a> Теоретические материалы

Используемые методы:
- Работа с приложением ядра 
    - [Application](https://dev.1c-bitrix.ru/api_d7/bitrix/main/application/index.php)
- Работа с JSON
    - [Json::encode](https://dev.1c-bitrix.ru/api_d7/bitrix/main/web/json/encode.php)
- Работа с корзиной 
    - [CSaleBasket::Add](https://dev.1c-bitrix.ru/api_help/sale/classes/csalebasket/csalebasket__add.php)
    - [CSaleBasket::Update](https://dev.1c-bitrix.ru/api_help/sale/classes/csalebasket/csalebasket__update.3dd628d0.php)
    - [CSaleBasket::GetList](https://dev.1c-bitrix.ru/api_help/sale/classes/csalebasket/csalebasket__getlist.4d82547a.php)
- Проверка каталога
    - [CCatalogProduct::IsExistProduct](https://dev.1c-bitrix.ru/api_help/catalog/classes/ccatalogproduct/isexistproduct.php)

Используемые модули:
 - [sale](https://dev.1c-bitrix.ru/api_help/sale/index.php) 

Теоретическая часть:

Штатные средства bitrix позволяют организовать хранение "избранных" товаров в отложенной корзине. Для добавления товара в список отложенных необходимо использовать bitrix API для работы с корзиной пользователя. В качестве примера скрипта приводится API старого ядра: `CSaleBasket::Add(МАССИВ_ПАРАМЕТРОВ)`. Согласно документации разработчика: "Метод добавляет товар в корзину, если его ещё нет, и обновляет параметры товара с увеличением количества, если он уже находится в корзине. В массиве МАССИВ_ПАРАМЕТРОВ перечисляются все параметры товара, которые нужны для работы модуля Интернет-магазина (т.е. этот модуль не зависит от других модулей и работает полностью самостоятельно)". Для того чтобы добавить товар в "избранное" необходимо чтобы соотвествующий массив параметров содержал ключ "DELAY" имеющий значение "Y" ('DELAY' => 'Y'). Также необходимо учитывать, что описанный выше метод в массиве параметров должен содержать обязательные для работы ключи, описанные в документации разработчика. Для получения информации о том находится ли товар в списке отложенных товаров можно использовать также API старого ядра, а именно метод: `CSaleBasket::GetList(ПАРАМЕТРЫ)`. Чтобы удалить товар из "избранного", используя старое ядро, существует метод `CSaleBasket::Update(ID_ТОВАРА, МАССИВ_ПАРАМЕТРОВ)`, где обязательно нужно в массиве параметров указать ключ "QUANTITY" со значением 0 ('QUANTITY' => 0). В связи с тем, что магазин может иметь множество торговых предложений рационально будет использование отдельного php файла, которой контроллирует все процессы, связанные с движением того или иного товара в "избранном" 

### <a name="PHP_HANDLER"></a> (PHP) Wishlist Ajax Обработчик

Рассмотрим наиболее простой способ реализации отдельного файла:
- в корне сайта (на том же уровне, на котором находится папка bitrix) создадим папку, например `ajax`
- внутри папки создадим php файл, содержаший скрипт для работы с отложенными товарами, например `check.php`

Содержимое файла `check.php`:

```php
<?
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

if (!Loader::includeModule('sale')) {
    $APPLICATION->RestartBuffer();
    header('Content-Type: application/json');
    echo (Json::encode(['OK' => false, 'ERROR' => 'Отсутвует модуль sale (loader)']));
    die();
}

/**
 * Function: get info about delayed products counter
 * @param $fUserID
 * @return mixed
 */
function getCounter($fUserID = null)
{
    if ($fUserID) {
        $counter = 0;

        $dbProducts = CSaleBasket::GetList(
            [],
            [
                'FUSER_ID' => $fUserID,
                'LID' => SITE_ID,
                'ORDER_ID' => 'NULL',
                'DELAY' => 'Y'
            ],
            false,
            false,
            ['PRODUCT_ID']
        );

        while ($dbProducts->Fetch()) {
            ++$counter;
        }

        return $counter;
    }

    return ['OK' => false, 'ERROR' => 'Отсутвует корзина для данного пользователя (getCounter)'];
}

/**
 * Function: get/set delayed info about product
 * @param $action
 * @param $productId
 * @return mixed
 */
function getResponce($action = null, $productId = null)
{
    if ($action === 'CHECK' || $action === 'TOGGLE') {
        if ($productId > 0) {
            if (CCatalogProduct::IsExistProduct($productId)) {
                $arFilter = [
                    'PRODUCT_ID' => $productId,
                    'FUSER_ID' => CSaleBasket::GetBasketUserID(),
                    'LID' => SITE_ID,
                    'ORDER_ID' => 'NULL',
                    'DELAY' => 'Y'
                ];

                $product = CSaleBasket::GetList([], $arFilter, false, false, ['ID'])->Fetch();

                if ($action === 'CHECK') {
                    return $product ? ['OK' => true, 'WISHLISTED' => true] : ['OK' => true, 'WISHLISTED' => false];
                } elseif ($action === 'TOGGLE') {

                    $fUserID = CSaleBasket::GetBasketUserID(true);
                    $fUserID = intval($fUserID);

                    if ($fUserID) {
                        if ($product) {
                            // remove from wishlist
                            $arFields = ['QUANTITY' => 0];

                            if (CSaleBasket::Update($product['ID'], $arFields)) {
                                $counter = getCounter($fUserID);

                                return $counter >= 0 ? ['OK' => true, 'WISHLISTED' => false, 'WISHLIST_COUNTER' => $counter] : ['OK' => true, 'WISHLISTED' => false];
                            }
                        } else {
                            // add to wishlist
                            $arElement = CIBlockElement::GetList(
                                ['SORT' => 'ASC'],
                                ['ID' => $productId],
                                false,
                                false,
                                ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'PRICE_1', 'CURRENCY_1']
                            )->GetNext();

                            if ($arElement) {
                                $arFields = [
                                    'PRODUCT_ID' => $arElement['ID'],
                                    'PRODUCT_PRICE_ID' => 1,
                                    'PRICE' => $arElement['PRICE_1'],
                                    'CURRENCY' => $arElement['CURRENCY_1'],
                                    'WEIGHT' => 0,
                                    'QUANTITY' => 1,
                                    'LID' => SITE_ID,
                                    'DELAY' => 'Y',
                                    'CAN_BUY' => 'Y',
                                    'NAME' => $arElement['NAME'],
                                    'MODULE' => 'sale',
                                    'NOTES' => '',
                                    'DETAIL_PAGE_URL' => $arElement['DETAIL_PAGE_URL'],
                                    'FUSER_ID' => $fUserID
                                ];

                                if (CSaleBasket::Add($arFields)) {
                                    $counter = getCounter($fUserID);

                                    return $counter >= 0 ? ['OK' => true, 'WISHLISTED' => true, 'WISHLIST_COUNTER' => $counter] : ['OK' => true, 'WISHLISTED' => true];
                                } else {
                                    return ['OK' => false, 'ERROR' => 'Приозошла ошибка при взаимодействии с базой данных (CSaleBasket::Add)'];
                                }
                            } else {
                                return ['OK' => false, 'ERROR' => 'Приозошла ошибка при взаимодействии с базой данных (CIBlockElement::GetList)'];
                            }
                        }
                    } else {
                        return ['OK' => false, 'ERROR' => 'Отсутвует корзина для данного пользователя (getResponce)'];
                    }
                }
            } else {
                return ['OK' => false, 'ERROR' => 'Переданный ID не является продуктом (productId)'];
            }
        } else {
            return ['OK' => false, 'ERROR' => 'Переданный ID не может быть отрицательным числом (productId)'];
        }
    } else {
        return ['OK' => false, 'ERROR' => 'Указанное REST действие не соответсвует имеющимся (action)'];
    }

    return;
}

$request = Application::getInstance()->getContext()->getRequest();
$responce = getResponce($request->getPost('ACTION'), intval($request->getPost('PRODUCT_ID')));

$APPLICATION->RestartBuffer();
header('Content-Type: application/json');
echo ($responce ? Json::encode($responce) : Json::encode(['OK' => false]));
die();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
```

Путь к папке с файлом используется при написании кода:
 - [(PHP) Wishlist catalog.item (элементы раздела)](#PHP_CATALOG_ITEM)
 - [(PHP) Wishlist catalog.element (детальный просмотр элемента)](#PHP_CATALOG_ELEMENT)

### <a name="PHP_SALE_BASKET_BTN"></a> (PHP) Wishlist счетчик кнопка-иконка (опционально)

Кнопка-иконка состояния (подразумеваем, что она будет только одна на всю страницу), будет отслеживать изменения внутри отложенных товаров пользователя, показывать текущее количество "избранных" товаров и иметь ссылку на соотвествующий раздел в корзине:

#### <a name="PHP_TEMPLATE_HEADER"></a> (PHP) Wishlist модификация header.php

- в шаблоне хедера (или в другом удобном для вам месте) поместим кнопки-иконку состояния:

```php
...

<? use Bitrix\Main\Loader; ?>

...

<? if(Loader::includeModule('sale')): ?>
<?
    $arWishlisted = CSaleBasket::GetList(
        [],
        [
            'FUSER_ID' => CSaleBasket::GetBasketUserID(),
            'LID' => SITE_ID,
            'ORDER_ID' => 'NULL',
            'DELAY' => 'Y'
        ],
        []
    );
?>

...

<a id="wishlist-cart-btn" class="wishlisted-cart-btn" href="/personal/basket/?wishlist=y" title="Избранное">
    <svg class="wishlisted-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 11 10" fill="<?=($arWishlisted ? '#55bc51' : '#bebebf')?>">
        <path d="M5.5 10L10 5.5C10 5.5 12.25 3.25002 10 1.00001C7.75 -1.25001 5.5 1.00001 5.5 1.00001C5.5 1.00001 3.25 -1.24999 1 1.00001C-1.25 3.25001 1 5.5 1 5.5L5.5 10Z"/>
    </svg>
    <span class="wishlisted-counter" style="<?=($arWishlisted ? '' : 'opacity: 0;')?>"><?=$arWishlisted?></span>
</a>

...

<? endif; ?>
...
```

Cсылка _"/personal/basket/?wishlist=y"_ ведет в корзину пользователя, а именно в раздел "избранное"

Get параметр _"wishlist=y"_ ссылки _"/personal/basket/?wishlist=y"_ используется при написании кода:
- [(PHP) Wishlist sale.basket.basket](#PHP_SALE_BASKET_BASKET)

Классы внутри кнопки, а также ее id используется при написании кода: 
- [(PHP) Wishlist catalog.item (элементы раздела)](#PHP_CATALOG_ITEM)
- [(PHP) Wishlist catalog.element (детальный просмотр элемента)](#PHP_CATALOG_ELEMENT)

#### <a name="PHP_SALE_BASKET_BASKET"></a> (PHP) Wishlist sale.basket.basket

Чтобы при нажатии на кнопку (ссылку) обработчик у пользователя открывалась страница с "избранными" товарами внесем изменения в шаблоне корзины `sale.basket.basket` (в качестве примера используется переопределение копии старого шаблона корзины `old_version_17`):

##### <a name="PHP_SALE_BASKET_BASKET_TEMPLATE"></a> (PHP) Wishlist модификация template.php

- в `template.php` внутри `div`, имеющего класс `bx_sort_container`, у ссылки с атрибутом `id` `basket_toolbar_button` содержимое атрибута `class` представим следующим образом:
```php
... class="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? '' : 'current')?>" ...
``` 
- в `template.php` внутри `div`, имеющего класс `bx_sort_container`, у ссылки с атрибутом `id` `basket_toolbar_button_delayed` содержимое атрибута `class` представим следующим образом:
```php
... class="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? 'current' : '')?>" ...
```

Итоговое содержимое `div`, имеющего класс `bx_sort_container` файла `template.php`:
```php
...
<div class="bx_sort_container">
    <a href="javascript:void(0)" id="basket_toolbar_button" class="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? '' : 'current')?>" onclick="showBasketItemsList()">
        <?=GetMessage('SALE_BASKET_ITEMS')?>
        <div id="normal_count" class="flat" style="display:none">
            &nbsp;(<?=$normalCount?>)
        </div>
    </a>
    <a href="javascript:void(0)" id="basket_toolbar_button_delayed" class="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? 'current' : '')?>" onclick="showBasketItemsList(2)" <?=$delayHidden?>>
        <?=GetMessage('SALE_BASKET_ITEMS_DELAYED')?>
        <div id="delay_count" class="flat">
            &nbsp;(<?=$delayCount?>)
        </div>
    </a>
    <a href="javascript:void(0)" id="basket_toolbar_button_subscribed" onclick="showBasketItemsList(3)" <?=$subscribeHidden?>>
        <?=GetMessage('SALE_BASKET_ITEMS_SUBSCRIBED')?>
        <div id="subscribe_count" class="flat">
            &nbsp;(<?=$subscribeCount?>)
        </div>
    </a>
    <a href="javascript:void(0)" id="basket_toolbar_button_not_available" onclick="showBasketItemsList(4)" <?=$naHidden?>>
        <?=GetMessage('SALE_BASKET_ITEMS_NOT_AVAILABLE')?>
        <div id="not_available_count" class="flat">
            &nbsp;(<?=$naCount?>)
        </div>
    </a>
</div>
...
```

##### <a name="PHP_SALE_BASKET_BASKET_ITEMS"></a> (PHP) Wishlist модификация basket_items.php

- в `basket_items.php` у `div` с атрибутом `id` `basket_items_list` содержимое атрибута `style` представим следующим образом:
```php
... style="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? 'display:none' : '')?>" ...
``` 

Итоговый открывающий `div` тег с атрибутом `id` `basket_items_list` файла `basket_items.php`:
```php
...
<div id="basket_items_list" style="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? 'display:none' : '')?>">
...
```

##### <a name="PHP_SALE_BASKET_BASKET_DELAYED"></a> (PHP) Wishlist модификация basket_items_delayed.php

- в `basket_items_delayed.php` у `div` с атрибутом `id` `basket_items_delayed` содержимое атрибута `style` представим следующим образом:
```php
... style="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? '' : 'display:none')?>" ...
``` 

Итоговый открывающий `div` тег с атрибутом `id` `basket_items_delayed` файла `basket_items_delayed.php`:
```php
...
<div id="basket_items_delayed" class="bx_ordercart_order_table_container" style="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? '' : 'display:none')?>">
...
```

### <a name="PHP_CATALOG_ITEM"></a> (PHP) Wishlist catalog.item (элементы раздела) (опционально) (поведение в будущем будет изменено)

Так как каждый элемент раздела `catalog.section` представлен как `catalog.item`, изменения будем вносить внутрь шаблона именно `catalog.item`, а не `catalog.section`

#### <a name="PHP_CATALOG_ITEM_TEMPLATE"></a> (PHP) Wishlist модификация шаблона

- в шаблоне элемента раздела поместим кнопку-иконку управления (подразумеваем, что она будет только одна на каждый товар):
```html
<button class="wishlisted-btn" type="button">
    <svg class="wishlisted-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 11 10" fill="#bebebf">
        <path d="M5.5 10L10 5.5C10 5.5 12.25 3.25002 10 1.00001C7.75 -1.25001 5.5 1.00001 5.5 1.00001C5.5 1.00001 3.25 -1.24999 1 1.00001C-1.25 3.25001 1 5.5 1 5.5L5.5 10Z"></path>
    </svg>
</button>
```

Классы внутри кнопки, а также ее собственный класс испльзуются при написании кода: 
- [(JS) Wishlist модификация script.js](#PHP_CATALOG_ITEM_SCRIPT)

#### <a name="PHP_CATALOG_ITEM_SCRIPT"></a> (JS) Wishlist модификация script.js

- в методе `init` объекта `JCCatalogItem` сразу же после инициализации поля `this.obProduct` добавим необходимые объекты:
```js
...
window.JCCatalogItem.prototype = {
    init: function()
    {
        var i = 0,
            treeItems = null;

        this.obProduct = BX(this.visual.ID);
        if (!this.obProduct)
        {
            this.errorCode = -1;
        }

        // Добавление объектов связанных с wishlist
        if (this.obProduct) {
            // Инициализации кнопки добавления/удаления (подразумеваем, что она только одна у каждого товара)
            this.obWishlistProductBtn = this.obProduct.querySelector('.wishlisted-btn');
            // 
            // Инициализации первой попавшейся кнопки-иконки состояния всего html документа (подразумеваем, что она только одна) (опционально)
            this.obWishlistCartBtn = document.querySelector('#wishlist-cart-btn');
            // 
        }
        // 

        ...
```

- в методе `init` объекта `JCCatalogItem` сразу же после проверки на ошибки (`if (this.errorCode === 0)`) добавим следующий код:
```js
...
if (this.errorCode === 0)
{
    // Добавление конфигурации для работы wishlist
    if (this.obWishlistProductBtn) {
        // url php скрипта к которому обращается wishlist
        this.wishlistUrl = '/ajax/check.php';
        // 
        // Добавление eventListener`ов
        BX.bind(this.obWishlistProductBtn, 'click', BX.proxy(this.wishlistHandleClick, this));
        // 
    }
    //

    ...
```

- после `init` метода объекта `JCCatalogItem` добавим необходимые методы:
```js
...
window.JCCatalogItem.prototype = {
    init: function() {
        ...
	},
    ...
    // Добавление wishlist методов
    // Прототип: функция(метод) wishlistHandleClick
    wishlistHandleClick: function() {
        if (this.obWishlistProductBtn) {
            // API FIX 
            this.obWishlistProductBtn.disabled = true;
            this.wishlistRunToggle();
        }
    },
    // 
    // Прототип: функция(метод) wishlistRunToggle
    wishlistRunToggle: function() {
        if (this.obWishlistProductBtn) {
            // API FIX 
            BX.ajax({
                method: 'POST',
                dataType: 'json',
                data: {"ACTION": "TOGGLE", "PRODUCT_ID": this.productType === 3 ? this.offers[this.offerNum].ID : this.product.id},
                url: this.wishlistUrl,
                onsuccess: BX.proxy(this.wishlistRunToggleResult, this)
            });
        }
    },
    // 
    // Прототип: функция(метод) wishlistRunCheck
    wishlistRunCheck: function() {
        if (this.obWishlistProductBtn) {
            // API FIX
            this.obWishlistProductBtn.disabled = true;

            BX.ajax({
                method: 'POST',
                dataType: 'json',
                data: {"ACTION": "CHECK", "PRODUCT_ID": this.productType === 3 ? this.offers[this.offerNum].ID : this.product.id},
                url: this.wishlistUrl,
                onsuccess: BX.proxy(this.wishlistRunCheckResult, this)
            });	
        }
    },
    // 
    // Прототип: функция(метод) wishlistRunCheckResult
    wishlistRunCheckResult: function(result) {
        // API FIX
        if (result && result.OK) {
            this.wishlistSetChecked(result.WISHLISTED);
        } else {
            console.error(result);
        }
        // API FIX
        if (this.obWishlistProductBtn) {
            this.obWishlistProductBtn.disabled = false;
        }	
    },
    // 
    // Прототип: функция(метод) wishlistRunToggleResult
    wishlistRunToggleResult: function(result) {
        // API FIX
        if (result && result.OK) {
            this.wishlistSetChecked(result.WISHLISTED);
            this.wishlistSetCounter(result.WISHLIST_COUNTER); // (опционально)
        } else {
            console.error(result);
        }
        // API FIX
        if (this.obWishlistProductBtn) {
            this.obWishlistProductBtn.disabled = false;
        }	
    },
    // 
    // Прототип: функция(метод) wishlistSetChecked
    wishlistSetChecked: function(checked) {
        // API FIX
        if (this.obWishlistProductBtn) {
            const icon = this.obWishlistProductBtn.querySelector('.wishlisted-icon');
            const label = this.obWishlistProductBtn.querySelector('.wishlisted-label');
            
            if (icon) {
                icon.setAttribute("fill", checked ? "#55bc51" : "#bebebf");
            }

            if (label) {
                label.textContent = checked ? 'В избранном' : 'В избранное';
            }
        }
    },
    // 
    // Прототип: функция(метод) wishlistSetCounter (опционально)
    wishlistSetCounter: function(count) {
        // API FIX
        if (this.obWishlistCartBtn) {
            const icon = this.obWishlistCartBtn.querySelector('.wishlisted-icon');
            const counter = this.obWishlistCartBtn.querySelector('.wishlisted-counter');

            if (icon) {
                icon.setAttribute("fill", count > 0 ? "#55bc51" : "#bebebf");
            }

            if (counter) {
                counter.style.opacity = count > 0 ? "1" : "0";
                counter.textContent = count;
            }
        }
    },
    // 
    // 
    ...
```

- в методе `changeInfo` объекта `JCCatalogItem` в самый конец (внутри `if (index > -1)`) добавим следующий код:
```js
...
changeInfo: function()
{
    ...
    if (index > -1)
    {
        ...
        // DEPRECATED! WILL BE CHANGED SOON!
        // ВНИМАНИЕ! Здесь добавлена функция перезапроса к серверу для получения текущего листа wishlist 
        this.wishlistRunCheck();
        // 
    }
},
...
```

### <a name="PHP_CATALOG_ELEMENT"></a> (PHP) Wishlist catalog.element (детальный просмотр элемента)

#### <a name="PHP_CATALOG_ELEMENT_TEMPLATE"></a> (PHP) Wishlist модификация шаблона

- в шаблоне детальной страницы просмотра элемента каталога поместим кнопку-иконку управления (подразумеваем, что она будет только одна):
```html
<button class="wishlisted-btn" type="button">
    <svg class="wishlisted-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 11 10" fill="#bebebf">
        <path d="M5.5 10L10 5.5C10 5.5 12.25 3.25002 10 1.00001C7.75 -1.25001 5.5 1.00001 5.5 1.00001C5.5 1.00001 3.25 -1.24999 1 1.00001C-1.25 3.25001 1 5.5 1 5.5L5.5 10Z"></path>
    </svg>
</button>
```

Классы внутри кнопки, а также ее собственный класс испльзуются при написании кода: 
- [(JS) Wishlist модификация script.js](#PHP_CATALOG_ELEMENT_SCRIPT)

#### <a name="PHP_CATALOG_ELEMENT_SCRIPT"></a> (JS) Wishlist модификация script.js

- в методе `init` объекта `JCCatalogElement` сразу же после инициализации поля `this.obProduct` добавим необходимые объекты:
```js
...
window.JCCatalogElement.prototype = {
    init: function()
    {
        var i = 0,
            j = 0,
            treeItems = null;

        this.obProduct = BX(this.visual.ID);
        if (!this.obProduct)
        {
            this.errorCode = -1;
        }

        // Добавление объектов связанных с wishlist
        if (this.obProduct) {
            // Инициализации кнопки добавления/удаления (подразумеваем, что она только одна у каждого товара)
            this.obWishlistProductBtn = this.obProduct.querySelector('.wishlisted-btn');
            // 
            // Инициализации первой попавшейся кнопки-иконки состояния всего html документа (подразумеваем, что она только одна) (опционально)
            this.obWishlistCartBtn = document.querySelector('#wishlist-cart-btn');
            // 
        }
        // 

        ...
```

- в методе `init` объекта `JCCatalogElement` сразу же после проверки на ошибки (`if (this.errorCode === 0)`) добавим следующий код:
```js
...
if (this.errorCode === 0)
{
    // Добавление конфигурации для работы wishlist
    if (this.obWishlistProductBtn) {
        // url php скрипта к которому обращается wishlist
        this.wishlistUrl = '/ajax/check.php';
        // 
        // Добавление eventListener`ов
        BX.bind(this.obWishlistProductBtn, 'click', BX.proxy(this.wishlistHandleClick, this));
        // 
    }
    //

    ...
```

- после `init` метода объекта `JCCatalogElement` добавим необходимые методы:
```js
...
window.JCCatalogElement.prototype = {
    init: function() {
        ...
	},
    ...
    // Добавление wishlist методов
    // Прототип: функция(метод) wishlistHandleClick
    wishlistHandleClick: function() {
        if (this.obWishlistProductBtn) {
            // API FIX 
            this.obWishlistProductBtn.disabled = true;
            this.wishlistRunToggle();
        }
    },
    // 
    // Прототип: функция(метод) wishlistRunToggle
    wishlistRunToggle: function() {
        if (this.obWishlistProductBtn) {
            // API FIX 
            BX.ajax({
                method: 'POST',
                dataType: 'json',
                data: {"ACTION": "TOGGLE", "PRODUCT_ID": this.productType === 3 ? this.offers[this.offerNum].ID : this.product.id},
                url: this.wishlistUrl,
                onsuccess: BX.proxy(this.wishlistRunToggleResult, this)
            });
        }
    },
    // 
    // Прототип: функция(метод) wishlistRunCheck
    wishlistRunCheck: function() {
        if (this.obWishlistProductBtn) {
            // API FIX
            this.obWishlistProductBtn.disabled = true;

            BX.ajax({
                method: 'POST',
                dataType: 'json',
                data: {"ACTION": "CHECK", "PRODUCT_ID": this.productType === 3 ? this.offers[this.offerNum].ID : this.product.id},
                url: this.wishlistUrl,
                onsuccess: BX.proxy(this.wishlistRunCheckResult, this)
            });	
        }
    },
    // 
    // Прототип: функция(метод) wishlistRunCheckResult
    wishlistRunCheckResult: function(result) {
        // API FIX
        if (result && result.OK) {
            this.wishlistSetChecked(result.WISHLISTED);
        } else {
            console.error(result);
        }
        // API FIX
        if (this.obWishlistProductBtn) {
            this.obWishlistProductBtn.disabled = false;
        }	
    },
    // 
    // Прототип: функция(метод) wishlistRunToggleResult
    wishlistRunToggleResult: function(result) {
        // API FIX
        if (result && result.OK) {
            this.wishlistSetChecked(result.WISHLISTED);
            this.wishlistSetCounter(result.WISHLIST_COUNTER); // (опционально)
        } else {
            console.error(result);
        }
        // API FIX
        if (this.obWishlistProductBtn) {
            this.obWishlistProductBtn.disabled = false;
        }	
    },
    // 
    // Прототип: функция(метод) wishlistSetChecked
    wishlistSetChecked: function(checked) {
        // API FIX
        if (this.obWishlistProductBtn) {
            const icon = this.obWishlistProductBtn.querySelector('.wishlisted-icon');
            const label = this.obWishlistProductBtn.querySelector('.wishlisted-label');
            
            if (icon) {
                icon.setAttribute("fill", checked ? "#55bc51" : "#bebebf");
            }

            if (label) {
                label.textContent = checked ? 'В избранном' : 'В избранное';
            }
        }
    },
    // 
    // Прототип: функция(метод) wishlistSetCounter (опционально)
    wishlistSetCounter: function(count) {
        // API FIX
        if (this.obWishlistCartBtn) {
            const icon = this.obWishlistCartBtn.querySelector('.wishlisted-icon');
            const counter = this.obWishlistCartBtn.querySelector('.wishlisted-counter');

            if (icon) {
                icon.setAttribute("fill", count > 0 ? "#55bc51" : "#bebebf");
            }

            if (counter) {
                counter.style.opacity = count > 0 ? "1" : "0";
                counter.textContent = count;
            }
        }
    },
    // 
    // 
    ...
```

- в методе `changeInfo` объекта `JCCatalogElement` в самый конец (внутри `if (index > -1)`) добавим следующий код:
```js
...
changeInfo: function()
{
    ...
    if (index > -1)
    {
        ...
        // ВНИМАНИЕ! Здесь добавлена функция перезапроса к серверу для получения текущего листа wishlist 
        this.wishlistRunCheck();
        // 
    }
},
...
```

### <a name="RESOURCES_ADDITIONAL"></a> Дополнительные материалы

Дополнительные материалы:
+ [Файл check.php](https://github.com/Welpodron/bitrix_wishlist/blob/main/ajax/check.php)
+ [Файл script.js (catalog.element)](https://github.com/Welpodron/bitrix_wishlist/blob/main/catalog.element/script.js)
+ [Файл script.js (catalog.item)](https://github.com/Welpodron/bitrix_wishlist/blob/main/catalog.item/script.js)
+ [Файл template.php (catalog.element)](https://github.com/Welpodron/bitrix_wishlist/blob/main/catalog.element/template.php)
+ [Файл template.php (catalog.item) (card)](https://github.com/Welpodron/bitrix_wishlist/blob/main/catalog.item/template.php)
+ [Файл template.php (sale.basket.basket)](https://github.com/Welpodron/bitrix_wishlist/blob/main/sale.basket.basket/template.php)
+ [Файл basket_items.php (sale.basket.basket)](https://github.com/Welpodron/bitrix_wishlist/blob/main/sale.basket.basket/basket_items.php)
+ [Файл basket_items_delayed.php (sale.basket.basket)](https://github.com/Welpodron/bitrix_wishlist/blob/main/sale.basket.basket/basket_items_delayed.php)
+ [Файл со стилями (общий)](https://github.com/Welpodron/bitrix_wishlist/blob/main/styles/style.css)

> @welpodron 2021