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
