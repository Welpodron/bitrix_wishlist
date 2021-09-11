<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
    }

    use Bitrix\Highloadblock as HL;

    /**
     * @global CMain $APPLICATION
     * @var array $arParams
     * @var array $arResult
     * @var CatalogSectionComponent $component
     * @var CBitrixComponentTemplate $this
     * @var string $templateName
     * @var string $componentPath
     * @var string $templateFolder
     */

    $this->setFrameMode(true);

    CModule::IncludeModule('highloadblock'); // подключить инфоблоки
    use \Bitrix\Main\Localization\Loc;
    $hlblock_id = 2; // id инфоблока
    $hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();

    $entity = HL\HighloadBlockTable::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    $rsData = $entity_data_class::getList([
        'select' => ['UF_LINK', 'UF_NAME'],
        'order' => ['ID' => 'ASC'],
        'filter' => ['UF_XML_ID' => $arResult['PROPERTIES']['PROIZVODITEL']['VALUE']]
    ]);
    while ($arData = $rsData->Fetch()) {
        $arResult2[] = $arData;
    }
    $this->addExternalCss('/bitrix/css/main/bootstrap.css');

    $templateLibrary = ['popup', 'fx'];
    $currencyList = '';

    if (!empty($arResult['CURRENCIES'])) {
        $templateLibrary[] = 'currency';
        $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
    }

    $templateData = [
        'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
        'TEMPLATE_LIBRARY' => $templateLibrary,
        'CURRENCIES' => $currencyList,
        'ITEM' => [
            'ID' => $arResult['ID'],
            'IBLOCK_ID' => $arResult['IBLOCK_ID'],
            'OFFERS_SELECTED' => $arResult['OFFERS_SELECTED'],
            'JS_OFFERS' => $arResult['JS_OFFERS']
        ]
    ];
    unset($currencyList, $templateLibrary);

    $mainId = $this->GetEditAreaId($arResult['ID']);
    $itemIds = [
        'ID' => $mainId,
        'DISCOUNT_PERCENT_ID' => $mainId . '_dsc_pict',
        'STICKER_ID' => $mainId . '_sticker',
        'BIG_SLIDER_ID' => $mainId . '_big_slider',
        'BIG_IMG_CONT_ID' => $mainId . '_bigimg_cont',
        'SLIDER_CONT_ID' => $mainId . '_slider_cont',
        'OLD_PRICE_ID' => $mainId . '_old_price',
        'PRICE_ID' => $mainId . '_price',
        'DISCOUNT_PRICE_ID' => $mainId . '_price_discount',
        'PRICE_TOTAL' => $mainId . '_price_total',
        'SLIDER_CONT_OF_ID' => $mainId . '_slider_cont_',
        'QUANTITY_ID' => $mainId . '_quantity',
        'QUANTITY_DOWN_ID' => $mainId . '_quant_down',
        'QUANTITY_UP_ID' => $mainId . '_quant_up',
        'QUANTITY_MEASURE' => $mainId . '_quant_measure',
        'QUANTITY_LIMIT' => $mainId . '_quant_limit',
        'BUY_LINK' => $mainId . '_buy_link',
        'ADD_BASKET_LINK' => $mainId . '_add_basket_link',
        'BASKET_ACTIONS_ID' => $mainId . '_basket_actions',
        'NOT_AVAILABLE_MESS' => $mainId . '_not_avail',
        'COMPARE_LINK' => $mainId . '_compare_link',
        'DELAY_LINK' => $mainId . '_delay_link',
        'TREE_ID' => $mainId . '_skudiv',
        'DISPLAY_PROP_DIV' => $mainId . '_sku_prop',
        'DISPLAY_MAIN_PROP_DIV' => $mainId . '_main_sku_prop',
        'OFFER_GROUP' => $mainId . '_set_group_',
        'BASKET_PROP_DIV' => $mainId . '_basket_prop',
        'SUBSCRIBE_LINK' => $mainId . '_subscribe',
        'TABS_ID' => $mainId . '_tabs',
        'TAB_CONTAINERS_ID' => $mainId . '_tab_containers',
        'SMALL_CARD_PANEL_ID' => $mainId . '_small_card_panel',
        'TABS_PANEL_ID' => $mainId . '_tabs_panel'
    ];
    $obName = $templateData['JS_OBJ'] = preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
    $name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
    : $arResult['NAME'];
    $title = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
    : $arResult['NAME'];
    $alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
    : $arResult['NAME'];

    $haveOffers = !empty($arResult['OFFERS']);
    if ($haveOffers) {
        $actualItem = isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']])
        ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]
        : reset($arResult['OFFERS']);
        $showSliderControls = false;

        foreach ($arResult['OFFERS'] as $offer) {
            if ($offer['MORE_PHOTO_COUNT'] > 1) {
                $showSliderControls = true;
                break;
            }
        }
    } else {
        $actualItem = $arResult;
        $showSliderControls = $arResult['MORE_PHOTO_COUNT'] > 1;
    }

    $skuProps = [];
    $price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
    $measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
    $showDiscount = $price['PERCENT'] > 0;

    $showDescription = !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
    $showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
    $buyButtonClassName = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-default' : 'btn-link';
    $showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
    $showButtonClassName = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-default' : 'btn-link';
    $showSubscribe = $arParams['PRODUCT_SUBSCRIPTION'] === 'Y' && ($arResult['CATALOG_SUBSCRIBE'] === 'Y' || $haveOffers);

    $arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
    $arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCE_CATALOG_ADD');
    $arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');
    $arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
    $arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
    $arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
    $arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
    $arParams['MESS_COMMENTS_TAB'] = 'Отзывы';
    $arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
    $arParams['MESS_RELATIVE_QUANTITY_MANY'] = $arParams['MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
    $arParams['MESS_RELATIVE_QUANTITY_FEW'] = $arParams['MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');

    $positionClassMap = [
        'left' => 'product-item-label-left',
        'center' => 'product-item-label-center',
        'right' => 'product-item-label-right',
        'bottom' => 'product-item-label-bottom',
        'middle' => 'product-item-label-middle',
        'top' => 'product-item-label-top'
    ];

    $discountPositionClass = 'product-item-label-big';
    if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION'])) {
        foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
            $discountPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
        }
    }

    $labelPositionClass = 'product-item-label-big';
    if (!empty($arParams['LABEL_PROP_POSITION'])) {
        foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
            $labelPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
        }
    }
?>

<?
    echo '<pre>';
    print_r($arResult['OFFERS_SELECTED']);
    echo '</pre>';
?>
<script>
    var offers = <?=empty($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']) ? $arResult['ID'] : $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']?>; //получаем первоначально выбранное торговое предложение
    BX.addCustomEvent('onCatalogStoreProductChange', function (changeID){ //ловим событие изменения ID выбранного торгового предложения
    offers = changeID;
    });
    $(document).ready(function(){
    let leftArrowHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="red"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>';
    let rightArrowHtml = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="red"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>';
  $(".owl-carousel").owlCarousel({items: 4, dots: true, autoWidth: true, navText: ['', '']});
});
</script>
<div class="bx-catalog-element bx-<?=$arParams['TEMPLATE_THEME']?>" id="<?=$itemIds['ID']?>" itemscope itemtype="http://schema.org/Product">
	<div class="container-fluid">
		<?
            if ($arParams['DISPLAY_NAME'] === 'Y') {
            ?>
			<div class="row">
				<div class="col-xs-12">
					<h1 class="bx-title"><?=$name?></h1>
				</div>
			</div>
			<?
                }
            ?>
		<div class="row">
			<div class="col-md-6 col-sm-12">
				<div class="product-item-detail-slider-container" id="<?=$itemIds['BIG_SLIDER_ID']?>">
					<span class="product-item-detail-slider-close" data-entity="close-popup"></span>
					<div class="product-item-detail-slider-block
						<?=($arParams['IMAGE_RESOLUTION'] === '1by1' ? 'product-item-detail-slider-block-square' : '')?>"
						data-entity="images-slider-block">
						<span class="product-item-detail-slider-left" data-entity="slider-control-left" style="display: none;"></span>
						<span class="product-item-detail-slider-right" data-entity="slider-control-right" style="display: none;"></span>
						<?if (false): ?>
                        <div class="product-item-label-text <?=$labelPositionClass?>" id="<?=$itemIds['STICKER_ID']?>"
							<?=(!$arResult['LABEL'] ? 'style="display: none;"' : '')?>>
							<?
                                if ($arResult['LABEL'] && !empty($arResult['LABEL_ARRAY_VALUE'])) {
                                    foreach ($arResult['LABEL_ARRAY_VALUE'] as $code => $value) {
                                    ?>
									<div<?=(!isset($arParams['LABEL_PROP_MOBILE'][$code]) ? ' class="hidden-xs"' : '')?>>
										<span title="<?=$value?>"><?=$value?></span>
									</div>
									<?
                                            }
                                        }
                                    ?>
						</div>
                        <?endif;?>
<?
    if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y') {
        if ($haveOffers) {
        ?>
								<div class="product-item-label-ring <?=$discountPositionClass?>" id="<?=$itemIds['DISCOUNT_PERCENT_ID']?>"
									style="display: none;">
								</div>
								<?
                                        } else {
                                            if ($price['DISCOUNT'] > 0) {
                                            ?>
									<div class="product-item-label-ring <?=$discountPositionClass?>" id="<?=$itemIds['DISCOUNT_PERCENT_ID']?>"
										title="<?=-$price['PERCENT']?>%">
										<span><?=-$price['PERCENT']?>%</span>
									</div>
									<?
                                                }
                                            }
                                        }
                                    ?>
						<div class="product-item-detail-slider-images-container" data-entity="images-container">
							<?
                                if (!empty($actualItem['MORE_PHOTO'])) {
                                    foreach ($actualItem['MORE_PHOTO'] as $key => $photo) {
                                    ?>
									<div class="product-item-detail-slider-image<?=($key == 0 ? ' active' : '')?>" data-entity="image" data-id="<?=$photo['ID']?>">
										<img src="<?=$photo['SRC']?>" alt="<?=$alt?>" title="<?=$title?>"<?=($key == 0 ? ' itemprop="image"' : '')?>>
									</div>
									<?
                                            }
                                        }

                                        if ($arParams['SLIDER_PROGRESS'] === 'Y') {
                                        ?>
								<div class="product-item-detail-slider-progress-bar" data-entity="slider-progress-bar" style="width: 0;"></div>
								<?
                                    }
                                ?>
						</div>
					</div>
					<?
                        if ($showSliderControls) {
                            if ($haveOffers) {
                                foreach ($arResult['OFFERS'] as $keyOffer => $offer) {
                                    if (!isset($offer['MORE_PHOTO_COUNT']) || $offer['MORE_PHOTO_COUNT'] <= 0) {
                                        continue;
                                    }

                                    $strVisible = $arResult['OFFERS_SELECTED'] == $keyOffer ? '' : 'none';
                                ?>
								<div class="product-item-detail-slider-controls-block owl-carousel" id="<?=$itemIds['SLIDER_CONT_OF_ID'] . $offer['ID']?>" style="display: <?=$strVisible?>;">
									<?
                                                    foreach ($offer['MORE_PHOTO'] as $keyPhoto => $photo) {
                                                    ?>
										<div class="product-item-detail-slider-controls-image<?=($keyPhoto == 0 ? ' active' : '')?>"
											data-entity="slider-control" data-value="<?=$offer['ID'] . '_' . $photo['ID']?>">
											<img src="<?=$photo['SRC']?>">
										</div>
										<?
                                                        }
                                                    ?>
								</div>
								<?
                                            }
                                        } else {
                                        ?>
							<div class="product-item-detail-slider-controls-block owl-carousel" id="<?=$itemIds['SLIDER_CONT_ID']?>">
								<?
                                            if (!empty($actualItem['MORE_PHOTO'])) {
                                                foreach ($actualItem['MORE_PHOTO'] as $key => $photo) {
                                                ?>
										<div class="product-item-detail-slider-controls-image<?=($key == 0 ? ' active' : '')?>"
											data-entity="slider-control" data-value="<?=$photo['ID']?>">
											<img src="<?=$photo['SRC']?>">
										</div>
										<?
                                                        }
                                                    }
                                                ?>
							</div>
							<?
                                    }
                                }
                            ?>
				</div>
			</div>
			<div class="col-md-6 col-sm-12 block_detal">
				<div class="row">
					<div class="col-sm-12">
						<div class='art'>
							<p><?=$arResult['PROPERTIES']['CML2_ARTICLE']['NAME']?> : <span><?if (!$arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']): ?>нет<?endif;?>
											<?=$arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']?></span></p>
						</div>

						<div>
						<div class="product-item-detail-pay-block">
							<?
                                foreach ($arParams['PRODUCT_PAY_BLOCK_ORDER'] as $blockName) {
                                    switch ($blockName) {
                                        case 'rating':
                                            if ($arParams['USE_VOTE_RATING'] === 'Y') {
                                            ?>
											<div class="product-item-detail-info-container">
												<?
                                                                    $APPLICATION->IncludeComponent(
                                                                        'bitrix:iblock.vote',
                                                                        'stars',
                                                                        [
                                                                            'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                                                                            'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                                                                            'ELEMENT_ID' => $arResult['ID'],
                                                                            'ELEMENT_CODE' => '',
                                                                            'MAX_VOTE' => '5',
                                                                            'VOTE_NAMES' => ['1', '2', '3', '4', '5'],
                                                                            'SET_STATUS_404' => 'N',
                                                                            'DISPLAY_AS_RATING' => 'vote_avg',
                                                                            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                                                            'CACHE_TIME' => $arParams['CACHE_TIME']
                                                                        ],
                                                                        $component,
                                                                        ['HIDE_ICONS' => 'Y']
                                                                    );
                                                                ?>
											</div>
											<?
                                                            }

                                                            break;

                                                        case 'price':
                                                        ?>
										<div class="product-item-detail-info-container float_left">
											<?
                                                            if ($arParams['SHOW_OLD_PRICE'] === 'Y') {
                                                            ?>
												<div class="product-item-detail-price-old" id="<?=$itemIds['OLD_PRICE_ID']?>"
													style="display: <?=($showDiscount ? '' : 'none')?>;">
													<?=($showDiscount ? $price['PRINT_RATIO_BASE_PRICE'] : '')?>
												</div>
												<?
                                                                }
                                                            ?>
											<div class="product-item-detail-price-current" id="<?=$itemIds['PRICE_ID']?>">
												<?=$price['PRINT_RATIO_PRICE']?>
											</div>
											<?
                                                            if ($arParams['SHOW_OLD_PRICE'] === 'Y') {
                                                            ?>
												<div class="item_economy_price" id="<?=$itemIds['DISCOUNT_PRICE_ID']?>"
													style="display: <?=($showDiscount ? '' : 'none')?>;">
													<?
                                                                        if ($showDiscount) {
                                                                            echo Loc::getMessage('CT_BCE_CATALOG_ECONOMY_INFO2', ['#ECONOMY#' => $price['PRINT_RATIO_DISCOUNT']]);
                                                                        }
                                                                    ?>
												</div>
												<?
                                                                }
                                                            ?>
										</div>
										<?

                                                        foreach ($arParams['PRODUCT_INFO_BLOCK_ORDER'] as $blockName) {
                                                            switch ($blockName) {
                                                                case 'sku':
                                                                    if ($haveOffers && !empty($arResult['OFFERS_PROP'])) {
                                                                    ?>
											<div id="<?=$itemIds['TREE_ID']?>" class='row'>
												<?
                                                                                foreach ($arResult['SKU_PROPS'] as $skuProperty) {
                                                                                    if (!isset($arResult['OFFERS_PROP'][$skuProperty['CODE']])) {
                                                                                        continue;
                                                                                    }

                                                                                    $propertyId = $skuProperty['ID'];
                                                                                    $skuProps[] = [
                                                                                        'ID' => $propertyId,
                                                                                        'SHOW_MODE' => $skuProperty['SHOW_MODE'],
                                                                                        'VALUES' => $skuProperty['VALUES'],
                                                                                        'VALUES_COUNT' => $skuProperty['VALUES_COUNT']
                                                                                    ];
                                                                                ?>
													<div class="product-item-detail-info-container" style='margin:0;padding:15px;' data-entity="sku-line-block">
														<div class="product-item-detail-info-container-title"><?=htmlspecialcharsEx($skuProperty['NAME'])?></div>
														<div class="product-item-scu-container">
															<div class="product-item-scu-block">
																<div class="product-item-scu-list">
																	<ul class="product-item-scu-item-list">
																		<?
                                                                                                            foreach ($skuProperty['VALUES'] as &$value) {
                                                                                                                $value['NAME'] = htmlspecialcharsbx($value['NAME']);

                                                                                                                if ($skuProperty['SHOW_MODE'] === 'PICT') {
                                                                                                                ?>
																				<li class="product-item-scu-item-color-container" title="<?=$value['NAME']?>"
																					data-treevalue="<?=$propertyId?>_<?=$value['ID']?>"
																					data-onevalue="<?=$value['ID']?>">
                                                                                    <?if ($value['PICT']['ID'] > 0): ?>
                                                                                        <div class="product-item-scu-item-color product-item-scu-item-color-image" style="background-image: url(<?=$value['PICT']['SRC']?>)">
                                                                                        </div>
                                                                                    <?else: ?>
                                                                                        <div class="product-item-scu-item-color product-item-scu-item-color-text">
                                                                                            <?=$value['NAME']?>
                                                                                        </div>
                                                                                    <?endif;?>
																				</li>
																				<?
                                                                                                                        } else {
                                                                                                                        ?>
																				<li class="product-item-scu-item-color-container" title="<?=$value['NAME']?>"
																					data-treevalue="<?=$propertyId?>_<?=$value['ID']?>"
																					data-onevalue="<?=$value['ID']?>">
                                                                                    <div class="product-item-scu-item-color product-item-scu-item-color-text">
                                                                                        <?=$value['NAME']?>
                                                                                    </div>
																				</li>
																				<?
                                                                                                                        }
                                                                                                                    }
                                                                                                                ?>
																	</ul>
																	<div style="clear: both;"></div>
																</div>
															</div>
														</div>
													</div>
													<?
                                                                                    }
                                                                                ?>
											</div>
											<?
                                                                        }

                                                                        break;

                                                                    case 'props':
                                                                        if (!empty($arResult['DISPLAY_PROPERTIES']) || $arResult['SHOW_OFFERS_PROPS']) {
                                                                        ?>
						<div class="product-item-detail-info-container " style='margin:0;'>

												<?
                                                                                if (!empty($arResult['DISPLAY_PROPERTIES'])) {
                                                                                ?>
													<dl class="product-item-detail-properties">
														<?
                                                                                            if (isset($arResult['PROPERTIES']['PROIZVODITEL']['VALUE']) && $arResult['PROPERTIES']['PROIZVODITEL']['VALUE'] != '') {
                                                                                            ?>
<div class="description2 description3"><div class="text"><?=$arResult['PROPERTIES']['PROIZVODITEL']['NAME']?></div><div class="text2"><a href="<?=$arResult2[0]['UF_LINK']?>"><?=$arResult2[0]['UF_NAME']?></a></div></div><div class='clear2 description3'></div>
<?
                                    }
                                    foreach ($arResult['DISPLAY_PROPERTIES'] as $property) {
                                        if (isset($arParams['MAIN_BLOCK_PROPERTY_CODE'][$property['CODE']])) {
                                        ?>
																<div class="description2 description3">
																<div class="text"><?=$property['NAME']?></div>
																<div class="text2"><?=(is_array($property['DISPLAY_VALUE'])
                                    ? implode(' / ', $property['DISPLAY_VALUE'])
                                    : $property['DISPLAY_VALUE'])?>
																</div>
																</div>
																<div class='clear2 description3'></div>
																<?
                                                                                                        }
                                                                                                    }
                                                                                                    unset($property);
                                                                                                ?>
													</dl>
													<?
                                                                                    }

                                                                                    if ($arResult['SHOW_OFFERS_PROPS']) {
                                                                                    ?>
													<dl class="product-item-detail-properties" id="<?=$itemIds['DISPLAY_MAIN_PROP_DIV']?>"></dl>
													<?
                                                                                    }
                                                                                ?>
											</div>
											<?
                                                                        }

                                                                        break;
                                                                }
                                                            }
                                                            break;

                                                        case 'priceRanges':
                                                            if ($arParams['USE_PRICE_COUNT']) {
                                                                $showRanges = !$haveOffers && count($actualItem['ITEM_QUANTITY_RANGES']) > 1;
                                                                $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';
                                                            ?>
											<div class="product-item-detail-info-container"
												<?=$showRanges ? '' : 'style="display: none;"'?>
												data-entity="price-ranges-block">
												<div class="product-item-detail-info-container-title">
													<?=$arParams['MESS_PRICE_RANGES_TITLE']?>
													<span data-entity="price-ranges-ratio-header">
														(<?=(Loc::getMessage(
                'CT_BCE_CATALOG_RATIO_PRICE',
                ['#RATIO#' => ($useRatio ? $measureRatio : '1') . ' ' . $actualItem['ITEM_MEASURE']['TITLE']]
            ))?>)
													</span>
												</div>
												<dl class="product-item-detail-properties" data-entity="price-ranges-body">
													<?
                                                                        if ($showRanges) {
                                                                            foreach ($actualItem['ITEM_QUANTITY_RANGES'] as $range) {
                                                                                if ($range['HASH'] !== 'ZERO-INF') {
                                                                                    $itemPrice = false;

                                                                                    foreach ($arResult['ITEM_PRICES'] as $itemPrice) {
                                                                                        if ($itemPrice['QUANTITY_HASH'] === $range['HASH']) {
                                                                                            break;
                                                                                        }
                                                                                    }

                                                                                    if ($itemPrice) {
                                                                                    ?>
																	<dt>
																		<?
                                                                                                            echo Loc::getMessage(
                                                                                                                'CT_BCE_CATALOG_RANGE_FROM',
                                                                                                                ['#FROM#' => $range['SORT_FROM'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE']]
                                                                                                            ) . ' ';

                                                                                                            if (is_infinite($range['SORT_TO'])) {
                                                                                                                echo Loc::getMessage('CT_BCE_CATALOG_RANGE_MORE');
                                                                                                            } else {
                                                                                                                echo Loc::getMessage(
                                                                                                                    'CT_BCE_CATALOG_RANGE_TO',
                                                                                                                    ['#TO#' => $range['SORT_TO'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE']]
                                                                                                                );
                                                                                                            }
                                                                                                        ?>
																	</dt>
																	<dd><?=($useRatio ? $itemPrice['PRINT_RATIO_PRICE'] : $itemPrice['PRINT_PRICE'])?></dd>
																	<?
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    ?>
												</dl>
											</div>
											<?
                                                                unset($showRanges, $useRatio, $itemPrice, $range);
                                                            }

                                                            break;

                                                        case 'quantityLimit':
                                                            if ($arParams['SHOW_MAX_QUANTITY'] !== 'N') {
                                                                if ($haveOffers) {
                                                                ?>
												<div class="product-item-detail-info-container" id="<?=$itemIds['QUANTITY_LIMIT']?>" style="display: none;">
													<div class="product-item-detail-info-container-title">
														<?=$arParams['MESS_SHOW_MAX_QUANTITY']?>:
														<span class="product-item-quantity" data-entity="quantity-limit-value"></span>
													</div>
												</div>
												<?
                                                                    } else {
                                                                        if (
                                                                            $measureRatio
                                                                            && (float) $actualItem['CATALOG_QUANTITY'] > 0
                                                                            && $actualItem['CATALOG_QUANTITY_TRACE'] === 'Y'
                                                                            && $actualItem['CATALOG_CAN_BUY_ZERO'] === 'N'
                                                                        ) {
                                                                        ?>
													<div class="product-item-detail-info-container" id="<?=$itemIds['QUANTITY_LIMIT']?>">
														<div class="product-item-detail-info-container-title">
															<?=$arParams['MESS_SHOW_MAX_QUANTITY']?>:
															<span class="product-item-quantity" data-entity="quantity-limit-value">
																<?
                                                                                            if ($arParams['SHOW_MAX_QUANTITY'] === 'M') {
                                                                                                if ((float) $actualItem['CATALOG_QUANTITY'] / $measureRatio >= $arParams['RELATIVE_QUANTITY_FACTOR']) {
                                                                                                    echo $arParams['MESS_RELATIVE_QUANTITY_MANY'];
                                                                                                } else {
                                                                                                    echo $arParams['MESS_RELATIVE_QUANTITY_FEW'];
                                                                                                }
                                                                                            } else {
                                                                                                echo $actualItem['CATALOG_QUANTITY'] . ' ' . $actualItem['ITEM_MEASURE']['TITLE'];
                                                                                            }
                                                                                        ?>
															</span>
														</div>
													</div>
													<?
                                                                            }
                                                                        }
                                                                    }

                                                                    break;

                                                                case 'quantity':
                                                                if ($arParams['USE_PRODUCT_QUANTITY']) {
                                                                    ?><div class='flex-cart'>
											<div class="product-item-detail-info-container float_left amount_block" style="<?=(!$actualItem['CAN_BUY'] ? 'display: none;' : '')?>"
												data-entity="quantity-block">
												<div class="product-item-detail-info-container-title"><?=Loc::getMessage('CATALOG_QUANTITY')?></div>
												<div class="product-item-amount">
													<div class="product-item-amount-field-container">
													<div class='border_1'>
														<a class="product-item-amount-field-btn-minus" id="<?=$itemIds['QUANTITY_DOWN_ID']?>"
															href="javascript:void(0)" rel="nofollow">
														</a>
														<input class="product-item-amount-field" id="<?=$itemIds['QUANTITY_ID']?>" type="tel"
															value="<?=$price['MIN_QUANTITY']?>">
														<a class="product-item-amount-field-btn-plus" id="<?=$itemIds['QUANTITY_UP_ID']?>"
															href="javascript:void(0)" rel="nofollow">
														</a>
													</div>
														<span class="product-item-amount-description-container">

															<span id="<?=$itemIds['PRICE_TOTAL']?>"></span>
														</span>
													</div>
												</div>
											</div>
											<?
                                                            }

                                                            break;

                                                        case 'buttons':
                                                        ?>

										<div data-entity="main-button-container" class='main-button-container'>
											<div id="<?=$itemIds['BASKET_ACTIONS_ID']?>" style="display: <?=($actualItem['CAN_BUY'] ? '' : 'none')?>;">
												<?
                                                                if ($showAddBtn) {
                                                                ?>
													<div class="product-item-detail-info-container">
														<a class="btn <?=$showButtonClassName?> product-item-detail-buy-button" id="<?=$itemIds['ADD_BASKET_LINK']?>"
															href="javascript:void(0);">
															<span><?=$arParams['MESS_BTN_ADD_TO_BASKET']?></span>
														</a>
													</div>
													<?
                                                                    }

                                                                    if ($showBuyBtn) {
                                                                    ?>
													<div class="product-item-detail-info-container">
														<a class="btn <?=$buyButtonClassName?> product-item-detail_buy" id="<?=$itemIds['BUY_LINK']?>"
															href="javascript:void(0);">
															<span><?=$arParams['MESS_BTN_BUY']?></span>
														</a>
													</div>
													<?
                                                                    }
                                                                ?>
											</div>
											<?
                                                            if ($showSubscribe) {
                                                            ?>
												<div class="product-item-detail-info-container">
													<?
                                                                        $APPLICATION->IncludeComponent(
                                                                            'bitrix:catalog.product.subscribe',
                                                                            '',
                                                                            [
                                                                                'PRODUCT_ID' => $arResult['ID'],
                                                                                'BUTTON_ID' => $itemIds['SUBSCRIBE_LINK'],
                                                                                'BUTTON_CLASS' => 'btn btn-default product-item-detail-buy-button',
                                                                                'DEFAULT_DISPLAY' => !$actualItem['CAN_BUY']
                                                                            ],
                                                                            $component,
                                                                            ['HIDE_ICONS' => 'Y']
                                                                        );
                                                                    ?>
												</div>
												<?
                                                                }
                                                            ?>
											<div class="product-item-detail-info-container">
												<a class="btn btn-link product-item-detail-buy-button none" id="<?=$itemIds['NOT_AVAILABLE_MESS']?>"
													href="javascript:void(0)"
													rel="nofollow" style="display: <?=(!$actualItem['CAN_BUY'] ? '' : 'none')?>;">
													<?=$arParams['MESS_NOT_AVAILABLE']?>
												</a>
											</div>
										</div>
										<?
                                                        break;
                                                }
                                            }
                                            $APPLICATION->IncludeComponent(
                                                'webes:oneclick',
                                                'green-cart',
                                                [
                                                    'BUTTON_ONE_CLICK' => 'Купить в 1 клик',
                                                    'BUTTON_ONE_CLICK_CLASS' => 'o-w-btn o-w-btn-sm',
                                                    'CART_COUNT' => 'Всего товаров:',
                                                    'CART_SUM' => 'на сумму',
                                                    'CHOOSE_PROPERTIES' => [
                                                        0 => '50',
                                                        1 => ''
                                                    ],
                                                    'CURRENT_CART' => 'N',
                                                    'CURRENT_CART_EMPTY' => 'Ваша корзина пуста',
                                                    'CURRENT_CART_ORDER' => 'Оформление заказа',
                                                    'CURRENT_CART_TITLE' => 'Текущая корзина',
                                                    'ELEMENT_ID' => $arResult['ID'],
                                                    'IBLOCK_ID' => '4',
                                                    'IBLOCK_TYPE' => 'catalog',
                                                    'MESS_SUCCESS_TITLE' => 'Ваш заказ принят. Ожидайте, с Вами свяжется менеджер',
                                                    'METRIKA_OPEN' => '',
                                                    'METRIKA_SEND' => '',
                                                    'MODAL_COMMENT' => 'Комментарий',
                                                    'MODAL_COMMENT_EX' => 'Адрес доставки или другой комментарий к заказу',
                                                    'MODAL_CONTINUE' => 'Продолжить',
                                                    'MODAL_COST' => 'Цена',
                                                    'MODAL_CURRENCY' => 'руб.',
                                                    'MODAL_DELIVERY' => 'Выберите способ доставки:',
                                                    'MODAL_DELIVERY_SYSTEMS' => '',
                                                    'MODAL_EMAIL' => 'E-mail',
                                                    'MODAL_EMAIL_EX' => 'mail@youdomain.com',
                                                    'MODAL_ERROR_DATA' => 'Ошибка данных',
                                                    'MODAL_HEADER' => 'Быстрое оформление заказа',
                                                    'MODAL_ORDER_NUMBER' => 'Вашему заказу присвоен номер',
                                                    'MODAL_PAY' => 'Выберите способ оплаты:',
                                                    'MODAL_PAY_DEFAULT' => '1',
                                                    'MODAL_PAY_SYSTEMS' => '',
                                                    'MODAL_PRIVACY_FULL_LINE' => 'Я согласен с условиями {PRIVACY_LINK}Политики конфиденциальности компании{/PRIVACY_LINK}, в соответствии с 152-ФЗ',
                                                    'MODAL_TEXT_BEFORE' => 'После отправки заказа наши менеджеры свяжутся с Вами с ближайшее время',
                                                    'MODAL_TEXT_BUTTON' => 'Оформить заказ',
                                                    'MODAL_YOUR_NAME' => 'Ваше Имя',
                                                    'MODAL_YOUR_NAME_EX' => 'Иван Иванович',
                                                    'MODAL_YOUR_PHONE' => 'Телефон',
                                                    'MODAL_YOUR_PHONE_EX' => '+7 ХХХ ХХХ ХХХХ',
                                                    'PRIVACY_LINK' => '',
                                                    'REQUEST_COMMENT' => 'Y',
                                                    'REQUEST_DELIVERY' => 'N',
                                                    'REQUEST_EMAIL' => 'Y',
                                                    'REQUEST_EMAIL_REQ' => 'N',
                                                    'REQUEST_FIO' => 'Y',
                                                    'REQUEST_FIO_REQ' => 'N',
                                                    'REQUEST_PAYMENT' => 'N',
                                                    'REQUEST_PHONE' => 'Y',
                                                    'REQUEST_PHONE_REQ' => 'N',
                                                    'REQUEST_PRIVACY' => 'Y',
                                                    'REQUEST_QUANTITY' => 'Y',
                                                    'USER_EMAIL' => 'EMAIL',
                                                    'USER_FIO' => 'FIO',
                                                    'USER_PHONE' => 'PHONE',
                                                    'WHATSAPP' => 'N',
                                                    'WHATSAPP_BTN_CLASS' => 'o-w-wa-style',
                                                    'WHATSAPP_BTN_NAME' => 'Купить WhatsApp',
                                                    'WHATSAPP_FRAZE' => 'Меня заинтересовало: ',
                                                    'WHATSAPP_PHONE' => '',
                                                    'COMPONENT_TEMPLATE' => 'green-cart',
                                                    'COMPOSITE_FRAME_MODE' => 'A',
                                                    'COMPOSITE_FRAME_TYPE' => 'AUTO'
                                                ],
                                                false
                                        );?>
</div>
<form class="catalog-element-utility">
    <ul class="catalog-element-utility__list">
        <?if ($arParams['DISPLAY_COMPARE']): ?>
            <li class="catalog-element-utility__item">
                <label id="<?=$itemIds['COMPARE_LINK']?>">
                    <input class="wishlisted-checkbox" type="checkbox" data-entity="compare-checkbox">
                    <span class="wishlisted-checkbox-label">
                        <svg width="20" height="20" viewBox="0 0 16 16" fill="#bebebf" xmlns="http://www.w3.org/2000/svg">
                            <rect width="2" height="5" x="1" y="10" rx="1"></rect>
                            <rect width="2" height="9" x="6" y="6" rx="1"></rect>
                            <rect width="2" height="14" x="11" y="1" rx="1"></rect>
                        </svg>
                        <span data-entity="compare-title">
                            В сравнение
                        </span>
                    </span>
                </label>
            </li>
        <?endif;?>
        <li class="catalog-element-utility__item">
            <button class="wishlisted-btn" type="button">
                <svg class="wishlisted-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 11 10" fill="<?=($arResult['PRODUCT_IN_WISHLIST'] ? '#55bc51' : '#bebebf')?>">
                    <path d="M5.5 10L10 5.5C10 5.5 12.25 3.25002 10 1.00001C7.75 -1.25001 5.5 1.00001 5.5 1.00001C5.5 1.00001 3.25 -1.24999 1 1.00001C-1.25 3.25001 1 5.5 1 5.5L5.5 10Z"></path>
                </svg>
                <span class="wishlisted-label">
                    <?=($arResult['PRODUCT_IN_WISHLIST'] ? 'В избранном' : 'В избранное')?>
                </span>
            </button>
        </li>
    </ul>
</form>

<?$APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => SITE_DIR . 'include/podpis.php'], false);?>

						</div>
					</div>
						</div>
					</div>
				</div>

		<div class="row">
			<div class="col-xs-12">
				<?
                    if ($haveOffers) {
                        if ($arResult['OFFER_GROUP']) {
                            foreach ($arResult['OFFER_GROUP_VALUES'] as $offerId) {
                            ?>
							<span id="<?=$itemIds['OFFER_GROUP'] . $offerId?>" style="display: none;">
								<?
                                                $APPLICATION->IncludeComponent(
                                                    'bitrix:catalog.set.constructor',
                                                    '.default',
                                                    [
                                                        'IBLOCK_ID' => $arResult['OFFERS_IBLOCK'],
                                                        'ELEMENT_ID' => $offerId,
                                                        'PRICE_CODE' => $arParams['PRICE_CODE'],
                                                        'BASKET_URL' => $arParams['BASKET_URL'],
                                                        'OFFERS_CART_PROPERTIES' => $arParams['OFFERS_CART_PROPERTIES'],
                                                        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                                        'CACHE_TIME' => $arParams['CACHE_TIME'],
                                                        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                                        'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
                                                        'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                                        'CURRENCY_ID' => $arParams['CURRENCY_ID']
                                                    ],
                                                    $component,
                                                    ['HIDE_ICONS' => 'Y']
                                                );
                                            ?>
							</span>
							<?
                                        }
                                    }
                                } else {
                                    if ($arResult['MODULES']['catalog'] && $arResult['OFFER_GROUP']) {
                                        $APPLICATION->IncludeComponent(
                                            'bitrix:catalog.set.constructor',
                                            '.default',
                                            [
                                                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                                                'ELEMENT_ID' => $arResult['ID'],
                                                'PRICE_CODE' => $arParams['PRICE_CODE'],
                                                'BASKET_URL' => $arParams['BASKET_URL'],
                                                'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                                'CACHE_TIME' => $arParams['CACHE_TIME'],
                                                'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                                'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
                                                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                                'CURRENCY_ID' => $arParams['CURRENCY_ID']
                                            ],
                                            $component,
                                            ['HIDE_ICONS' => 'Y']
                                        );
                                    }
                                }
                            ?>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12">
				<div class="row" id="<?=$itemIds['TABS_ID']?>">
					<div class="col-xs-12">
						<div class="product-item-detail-tabs-container">
							<ul class="product-item-detail-tabs-list">

									<li class="product-item-detail-tab active" data-entity="tab" data-value="description">
										<a href="javascript:void(0);" class="product-item-detail-tab-link">
											<span><?=$arParams['MESS_DESCRIPTION_TAB']?></span>
										</a>
									</li>
									<?

                                        if (!empty($arResult['DISPLAY_PROPERTIES']) || $arResult['SHOW_OFFERS_PROPS']) {
                                        ?>
									<li class="product-item-detail-tab" data-entity="tab" data-value="properties">
										<a href="javascript:void(0);" class="product-item-detail-tab-link">
											<span><?=$arParams['MESS_PROPERTIES_TAB']?></span>
										</a>
									</li>
									<?
                                    }?>
<?if (!empty($arResult['PROPERTIES']['instrukcija']['VALUE'])): ?>
									<li class="product-item-detail-tab" data-entity="tab" data-value="documents">
										<a href="javascript:void(0);" class="product-item-detail-tab-link">
											<span>Документы</span>
										</a>
									</li>
							<?endif;?>
<?if ($arParams['USE_COMMENTS'] === 'Y') {
    ?>
									<li class="product-item-detail-tab" data-entity="tab" data-value="comments">
										<a href="javascript:void(0);" class="product-item-detail-tab-link">
											<span><?=$arParams['MESS_COMMENTS_TAB']?></span>
										</a>
									</li>
									<?
                                        }
                                        if (!empty($arResult['PROPERTIES']['PRIMENENIE']['~VALUE']['TEXT'])) {
                                        ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="primenenie">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arResult['PROPERTIES']['PRIMENENIE']['NAME']?></span>
					</a>
				</li>
				<?
                    }
                    if (!empty($arResult['PROPERTIES']['preimushestva']['~VALUE']['TEXT'])) {
                    ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="preimushestva">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arResult['PROPERTIES']['preimushestva']['NAME']?></span>
					</a>
				</li>
				<?
                    }
                    if (!empty($arResult['PROPERTIES']['SERTIFICAT']['VALUE'])) {
                    ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="sertificat">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arResult['PROPERTIES']['SERTIFICAT']['NAME']?></span>
					</a>
				</li>
				<?
                    }
                ?>
							</ul>
						</div>
					</div>
				</div>
				<div class="row" id="<?=$itemIds['TAB_CONTAINERS_ID']?>">
					<div class="col-xs-12">


							<div class="product-item-detail-tab-content active" data-entity="tab-container" data-value="description"
								itemprop="description">

								<?
                                    if (
                                        $arResult['PREVIEW_TEXT'] != ''
                                        && (
                                            $arParams['DISPLAY_PREVIEW_TEXT_MODE'] === 'S'
                                            || ($arParams['DISPLAY_PREVIEW_TEXT_MODE'] === 'E' && $arResult['DETAIL_TEXT'] == '')
                                        )
                                    ) {
                                        echo $arResult['PREVIEW_TEXT_TYPE'] === 'html' ? $arResult['PREVIEW_TEXT'] : '<p>' . $arResult['PREVIEW_TEXT'] . '</p>';
                                    }

                                    if ($arResult['DETAIL_TEXT'] != '') {
                                        echo $arResult['DETAIL_TEXT_TYPE'] === 'html' ? $arResult['DETAIL_TEXT'] : '<p>' . $arResult['DETAIL_TEXT'] . '</p>';
                                    }
                                ?>
							</div>


							<?if (!empty($arResult['PROPERTIES']['instrukcija']['VALUE'])): ?>
								<div class="product-item-detail-tab-content" data-entity="tab-container" data-value="documents">
									<div class="list-documents">
									<?foreach ($arResult['PROPERTIES']['instrukcija']['VALUE'] as $dbInstructionId): ?>
<?
    $dbInstruction = CFile::GetFileArray($dbInstructionId);
    $arInstruction['SRC'] = $dbInstruction['SRC'];

    $arFileInfo = pathinfo($dbInstruction['ORIGINAL_NAME']);

    $arInstruction['NAME'] = $arFileInfo['filename'];
    $arInstruction['EXTENSION'] = '.' . $arFileInfo['extension'];
    $arInstruction['SIZE'] = round(($dbInstruction['FILE_SIZE'] / 1024 / 1024), 2) . ' MB';
?>
										<a class="list-documents__item" href="<?=$arInstruction['SRC']?>" target="_blank">
											<div class="list-documents__container">
												<span class="list-documents__title"><?=$arInstruction['NAME']?></span>
												<span class="list-documents__sub"><?=$arInstruction['SIZE']?></span>
												<span class="list-documents__dec"><?=$arInstruction['EXTENSION']?></span>
											</div>
										</a>
									<?endforeach;?>
									</div>
								</div>
							<?endif;?>

							<?

                                if (!empty($arResult['DISPLAY_PROPERTIES']) || $arResult['SHOW_OFFERS_PROPS']) {
                                ?>
							<div class="product-item-detail-tab-content" data-entity="tab-container" data-value="properties">
								<?
                                        if (!empty($arResult['DISPLAY_PROPERTIES'])) {
                                        ?>
									<dl class="product-item-detail-properties">
										<?
                                                    foreach ($arResult['DISPLAY_PROPERTIES'] as $property) {
                                                    ?>
											<div class='clearfix edd'><dt><?=$property['NAME']?></dt>
											<dd><?=(
                is_array($property['DISPLAY_VALUE'])
                ? implode(' / ', $property['DISPLAY_VALUE'])
                : $property['DISPLAY_VALUE']
            )?>
										</dd></div>
											<?
                                                        }
                                                        unset($property);
                                                    ?>
									</dl>
									<?
                                            }

                                            if ($arResult['SHOW_OFFERS_PROPS']) {
                                            ?>
									<dl class="product-item-detail-properties" id="<?=$itemIds['DISPLAY_PROP_DIV']?>"></dl>
									<?
                                            }
                                        ?>
							</div>
							<?
                            }?>
<?if ($arParams['USE_COMMENTS'] === 'Y') {
    ?>
							<div class="product-item-detail-tab-content" data-entity="tab-container" data-value="comments" style="display: none;">
								<?
                                        $APPLICATION->IncludeComponent(
                                            'bitrix:catalog.comments',
                                            '',
                                            [
                                                'ELEMENT_ID' => $arResult['ID'],
                                                'ELEMENT_CODE' => '',
                                                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                                                'SHOW_DEACTIVATED' => $arParams['SHOW_DEACTIVATED'],
                                                'URL_TO_COMMENT' => '',
                                                'WIDTH' => '',
                                                'COMMENTS_COUNT' => '5',
                                                'BLOG_USE' => $arParams['BLOG_USE'],
                                                'FB_USE' => $arParams['FB_USE'],
                                                'FB_APP_ID' => $arParams['FB_APP_ID'],
                                                'VK_USE' => $arParams['VK_USE'],
                                                'VK_API_ID' => $arParams['VK_API_ID'],
                                                'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                                'CACHE_TIME' => $arParams['CACHE_TIME'],
                                                'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                                'BLOG_TITLE' => '',
                                                'BLOG_URL' => $arParams['BLOG_URL'],
                                                'PATH_TO_SMILE' => '',
                                                'EMAIL_NOTIFY' => $arParams['BLOG_EMAIL_NOTIFY'],
                                                'AJAX_POST' => 'Y',
                                                'SHOW_SPAM' => 'Y',
                                                'SHOW_RATING' => 'N',
                                                'FB_TITLE' => '',
                                                'FB_USER_ADMIN_ID' => '',
                                                'FB_COLORSCHEME' => 'light',
                                                'FB_ORDER_BY' => 'reverse_time',
                                                'VK_TITLE' => '',
                                                'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME']
                                            ],
                                            $component,
                                            ['HIDE_ICONS' => 'Y']
                                        );
                                    ?>
							</div>
							<?
                                }
                            if (!empty($arResult['PROPERTIES']['PRIMENENIE']['~VALUE']['TEXT'])) {?>
							<div class="product-item-detail-tab-content" data-entity="tab-container" data-value="primenenie" style="display: none;">
							<div>
<?=$arResult['PROPERTIES']['PRIMENENIE']['~VALUE']['TEXT'];?>
								</div>
						</div>
							<?}
                            if (!empty($arResult['PROPERTIES']['preimushestva']['~VALUE']['TEXT'])) {?>
							<div class="product-item-detail-tab-content preimushestva_block" data-entity="tab-container" data-value="preimushestva" style="display: none;">
							<div>
<?=$arResult['PROPERTIES']['preimushestva']['~VALUE']['TEXT'];?>
								</div>
						</div>
<?}
if (!empty($arResult['PROPERTIES']['SERTIFICAT']['VALUE'])) {?>
							<div class="product-item-detail-tab-content" data-entity="tab-container" data-value="sertificat" style="display: none;">
							<div>
<?foreach ($arResult['PROPERTIES']['SERTIFICAT']['VALUE'] as $k => $value): ?>
<?$img267 = CFile::GetPath($value);?>
						<p><a href='<?=$img267;?>' target="_blank"><?=$arResult['PROPERTIES']['SERTIFICAT']['DESCRIPTION'][$k]?></a></p>
<?endforeach?>
								</div>
						</div>
<?}?>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<?
                    if ($arResult['CATALOG'] && $actualItem['CAN_BUY'] && \Bitrix\Main\ModuleManager::isModuleInstalled('sale')) {
                        $APPLICATION->IncludeComponent(
                            'bitrix:sale.prediction.product.detail',
                            '.default',
                            [
                                'BUTTON_ID' => $showBuyBtn ? $itemIds['BUY_LINK'] : $itemIds['ADD_BASKET_LINK'],
                                'POTENTIAL_PRODUCT_TO_BUY' => [
                                    'ID' => isset($arResult['ID']) ? $arResult['ID'] : null,
                                    'MODULE' => isset($arResult['MODULE']) ? $arResult['MODULE'] : 'catalog',
                                    'PRODUCT_PROVIDER_CLASS' => isset($arResult['PRODUCT_PROVIDER_CLASS']) ? $arResult['PRODUCT_PROVIDER_CLASS'] : 'CCatalogProductProvider',
                                    'QUANTITY' => isset($arResult['QUANTITY']) ? $arResult['QUANTITY'] : null,
                                    'IBLOCK_ID' => isset($arResult['IBLOCK_ID']) ? $arResult['IBLOCK_ID'] : null,

                                    'PRIMARY_OFFER_ID' => isset($arResult['OFFERS'][0]['ID']) ? $arResult['OFFERS'][0]['ID'] : null,
                                    'SECTION' => [
                                        'ID' => isset($arResult['SECTION']['ID']) ? $arResult['SECTION']['ID'] : null,
                                        'IBLOCK_ID' => isset($arResult['SECTION']['IBLOCK_ID']) ? $arResult['SECTION']['IBLOCK_ID'] : null,
                                        'LEFT_MARGIN' => isset($arResult['SECTION']['LEFT_MARGIN']) ? $arResult['SECTION']['LEFT_MARGIN'] : null,
                                        'RIGHT_MARGIN' => isset($arResult['SECTION']['RIGHT_MARGIN']) ? $arResult['SECTION']['RIGHT_MARGIN'] : null
                                    ]
                                ]
                            ],
                            $component,
                            ['HIDE_ICONS' => 'Y']
                        );
                    }

                    if ($arResult['CATALOG'] && $arParams['USE_GIFTS_DETAIL'] == 'Y' && \Bitrix\Main\ModuleManager::isModuleInstalled('sale')) {
                    ?>
					<div data-entity="parent-container">
						<?
                                if (!isset($arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE']) || $arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE'] !== 'Y') {
                                ?>
							<div class="catalog-block-header" data-entity="header" data-showed="false" style="display: none; opacity: 0;">
								<?=($arParams['GIFTS_DETAIL_BLOCK_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_GIFT_BLOCK_TITLE_DEFAULT'))?>
							</div>
							<?
                                    }

                                    CBitrixComponent::includeComponentClass('bitrix:sale.products.gift');
                                    $APPLICATION->IncludeComponent(
                                        'bitrix:sale.products.gift',
                                        '.default',
                                        [
                                            'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
                                            'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],

                                            'PRODUCT_ROW_VARIANTS' => '',
                                            'PAGE_ELEMENT_COUNT' => 0,
                                            'DEFERRED_PRODUCT_ROW_VARIANTS' => \Bitrix\Main\Web\Json::encode(
                                                SaleProductsGiftComponent::predictRowVariants(
                                                    $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
                                                    $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT']
                                                )
                                            ),
                                            'DEFERRED_PAGE_ELEMENT_COUNT' => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],

                                            'SHOW_DISCOUNT_PERCENT' => $arParams['GIFTS_SHOW_DISCOUNT_PERCENT'],
                                            'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
                                            'SHOW_OLD_PRICE' => $arParams['GIFTS_SHOW_OLD_PRICE'],
                                            'PRODUCT_DISPLAY_MODE' => 'Y',
                                            'PRODUCT_BLOCKS_ORDER' => $arParams['GIFTS_PRODUCT_BLOCKS_ORDER'],
                                            'SHOW_SLIDER' => $arParams['GIFTS_SHOW_SLIDER'],
                                            'SLIDER_INTERVAL' => isset($arParams['GIFTS_SLIDER_INTERVAL']) ? $arParams['GIFTS_SLIDER_INTERVAL'] : '',
                                            'SLIDER_PROGRESS' => isset($arParams['GIFTS_SLIDER_PROGRESS']) ? $arParams['GIFTS_SLIDER_PROGRESS'] : '',

                                            'TEXT_LABEL_GIFT' => $arParams['GIFTS_DETAIL_TEXT_LABEL_GIFT'],

                                            'LABEL_PROP_' . $arParams['IBLOCK_ID'] => [],
                                            'LABEL_PROP_MOBILE_' . $arParams['IBLOCK_ID'] => [],
                                            'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],

                                            'ADD_TO_BASKET_ACTION' => (isset($arParams['ADD_TO_BASKET_ACTION']) ? $arParams['ADD_TO_BASKET_ACTION'] : ''),
                                            'MESS_BTN_BUY' => $arParams['~GIFTS_MESS_BTN_BUY'],
                                            'MESS_BTN_ADD_TO_BASKET' => $arParams['~GIFTS_MESS_BTN_BUY'],
                                            'MESS_BTN_DETAIL' => $arParams['~MESS_BTN_DETAIL'],
                                            'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],

                                            'SHOW_PRODUCTS_' . $arParams['IBLOCK_ID'] => 'Y',
                                            'PROPERTY_CODE_' . $arParams['IBLOCK_ID'] => $arParams['LIST_PROPERTY_CODE'],
                                            'PROPERTY_CODE_MOBILE' . $arParams['IBLOCK_ID'] => $arParams['LIST_PROPERTY_CODE_MOBILE'],
                                            'PROPERTY_CODE_' . $arResult['OFFERS_IBLOCK'] => $arParams['OFFER_TREE_PROPS'],
                                            'OFFER_TREE_PROPS_' . $arResult['OFFERS_IBLOCK'] => $arParams['OFFER_TREE_PROPS'],
                                            'CART_PROPERTIES_' . $arResult['OFFERS_IBLOCK'] => $arParams['OFFERS_CART_PROPERTIES'],
                                            'ADDITIONAL_PICT_PROP_' . $arParams['IBLOCK_ID'] => (isset($arParams['ADD_PICT_PROP']) ? $arParams['ADD_PICT_PROP'] : ''),
                                            'ADDITIONAL_PICT_PROP_' . $arResult['OFFERS_IBLOCK'] => (isset($arParams['OFFER_ADD_PICT_PROP']) ? $arParams['OFFER_ADD_PICT_PROP'] : ''),

                                            'HIDE_NOT_AVAILABLE' => 'Y',
                                            'HIDE_NOT_AVAILABLE_OFFERS' => 'Y',
                                            'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
                                            'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
                                            'PRICE_CODE' => $arParams['PRICE_CODE'],
                                            'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],
                                            'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                                            'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                            'BASKET_URL' => $arParams['BASKET_URL'],
                                            'ADD_PROPERTIES_TO_BASKET' => $arParams['ADD_PROPERTIES_TO_BASKET'],
                                            'PRODUCT_PROPS_VARIABLE' => $arParams['PRODUCT_PROPS_VARIABLE'],
                                            'PARTIAL_PRODUCT_PROPERTIES' => $arParams['PARTIAL_PRODUCT_PROPERTIES'],
                                            'USE_PRODUCT_QUANTITY' => 'N',
                                            'PRODUCT_QUANTITY_VARIABLE' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
                                            'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                            'POTENTIAL_PRODUCT_TO_BUY' => [
                                                'ID' => isset($arResult['ID']) ? $arResult['ID'] : null,
                                                'MODULE' => isset($arResult['MODULE']) ? $arResult['MODULE'] : 'catalog',
                                                'PRODUCT_PROVIDER_CLASS' => isset($arResult['PRODUCT_PROVIDER_CLASS']) ? $arResult['PRODUCT_PROVIDER_CLASS'] : 'CCatalogProductProvider',
                                                'QUANTITY' => isset($arResult['QUANTITY']) ? $arResult['QUANTITY'] : null,
                                                'IBLOCK_ID' => isset($arResult['IBLOCK_ID']) ? $arResult['IBLOCK_ID'] : null,

                                                'PRIMARY_OFFER_ID' => isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'])
                                                ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']
                                                : null,
                                                'SECTION' => [
                                                    'ID' => isset($arResult['SECTION']['ID']) ? $arResult['SECTION']['ID'] : null,
                                                    'IBLOCK_ID' => isset($arResult['SECTION']['IBLOCK_ID']) ? $arResult['SECTION']['IBLOCK_ID'] : null,
                                                    'LEFT_MARGIN' => isset($arResult['SECTION']['LEFT_MARGIN']) ? $arResult['SECTION']['LEFT_MARGIN'] : null,
                                                    'RIGHT_MARGIN' => isset($arResult['SECTION']['RIGHT_MARGIN']) ? $arResult['SECTION']['RIGHT_MARGIN'] : null
                                                ]
                                            ],

                                            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
                                            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
                                            'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY']
                                        ],
                                        $component,
                                        ['HIDE_ICONS' => 'Y']
                                    );
                                ?>
					</div>
					<?
                        }

                        if ($arResult['CATALOG'] && $arParams['USE_GIFTS_MAIN_PR_SECTION_LIST'] == 'Y' && \Bitrix\Main\ModuleManager::isModuleInstalled('sale')) {
                        ?>
					<div data-entity="parent-container">
						<?
                                if (!isset($arParams['GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE']) || $arParams['GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE'] !== 'Y') {
                                ?>
							<div class="catalog-block-header" data-entity="header" data-showed="false" style="display: none; opacity: 0;">
								<?=($arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_GIFTS_MAIN_BLOCK_TITLE_DEFAULT'))?>
							</div>
							<?
                                    }

                                    $APPLICATION->IncludeComponent(
                                        'bitrix:sale.gift.main.products',
                                        '.default',
                                        [
                                            'PAGE_ELEMENT_COUNT' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'],
                                            'LINE_ELEMENT_COUNT' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'],
                                            'HIDE_BLOCK_TITLE' => 'Y',
                                            'BLOCK_TITLE' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'],

                                            'OFFERS_FIELD_CODE' => $arParams['OFFERS_FIELD_CODE'],
                                            'OFFERS_PROPERTY_CODE' => $arParams['OFFERS_PROPERTY_CODE'],

                                            'AJAX_MODE' => $arParams['AJAX_MODE'],
                                            'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                                            'IBLOCK_ID' => $arParams['IBLOCK_ID'],

                                            'ELEMENT_SORT_FIELD' => 'ID',
                                            'ELEMENT_SORT_ORDER' => 'DESC',
                                            //'ELEMENT_SORT_FIELD2' => $arParams['ELEMENT_SORT_FIELD2'],
                                            //'ELEMENT_SORT_ORDER2' => $arParams['ELEMENT_SORT_ORDER2'],
                                            'FILTER_NAME' => 'searchFilter',
                                            'SECTION_URL' => $arParams['SECTION_URL'],
                                            'DETAIL_URL' => $arParams['DETAIL_URL'],
                                            'BASKET_URL' => $arParams['BASKET_URL'],
                                            'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
                                            'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
                                            'SECTION_ID_VARIABLE' => $arParams['SECTION_ID_VARIABLE'],

                                            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                                            'CACHE_TIME' => $arParams['CACHE_TIME'],

                                            'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                                            'SET_TITLE' => $arParams['SET_TITLE'],
                                            'PROPERTY_CODE' => $arParams['PROPERTY_CODE'],
                                            'PRICE_CODE' => $arParams['PRICE_CODE'],
                                            'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                                            'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],

                                            'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                                            'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                            'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                                            'HIDE_NOT_AVAILABLE' => 'Y',
                                            'HIDE_NOT_AVAILABLE_OFFERS' => 'Y',
                                            'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
                                            'PRODUCT_BLOCKS_ORDER' => $arParams['GIFTS_PRODUCT_BLOCKS_ORDER'],

                                            'SHOW_SLIDER' => $arParams['GIFTS_SHOW_SLIDER'],
                                            'SLIDER_INTERVAL' => isset($arParams['GIFTS_SLIDER_INTERVAL']) ? $arParams['GIFTS_SLIDER_INTERVAL'] : '',
                                            'SLIDER_PROGRESS' => isset($arParams['GIFTS_SLIDER_PROGRESS']) ? $arParams['GIFTS_SLIDER_PROGRESS'] : '',

                                            'ADD_PICT_PROP' => (isset($arParams['ADD_PICT_PROP']) ? $arParams['ADD_PICT_PROP'] : ''),
                                            'LABEL_PROP' => (isset($arParams['LABEL_PROP']) ? $arParams['LABEL_PROP'] : ''),
                                            'LABEL_PROP_MOBILE' => (isset($arParams['LABEL_PROP_MOBILE']) ? $arParams['LABEL_PROP_MOBILE'] : ''),
                                            'LABEL_PROP_POSITION' => (isset($arParams['LABEL_PROP_POSITION']) ? $arParams['LABEL_PROP_POSITION'] : ''),
                                            'OFFER_ADD_PICT_PROP' => (isset($arParams['OFFER_ADD_PICT_PROP']) ? $arParams['OFFER_ADD_PICT_PROP'] : ''),
                                            'OFFER_TREE_PROPS' => (isset($arParams['OFFER_TREE_PROPS']) ? $arParams['OFFER_TREE_PROPS'] : ''),
                                            'SHOW_DISCOUNT_PERCENT' => (isset($arParams['SHOW_DISCOUNT_PERCENT']) ? $arParams['SHOW_DISCOUNT_PERCENT'] : ''),
                                            'DISCOUNT_PERCENT_POSITION' => (isset($arParams['DISCOUNT_PERCENT_POSITION']) ? $arParams['DISCOUNT_PERCENT_POSITION'] : ''),
                                            'SHOW_OLD_PRICE' => (isset($arParams['SHOW_OLD_PRICE']) ? $arParams['SHOW_OLD_PRICE'] : ''),
                                            'MESS_BTN_BUY' => (isset($arParams['~MESS_BTN_BUY']) ? $arParams['~MESS_BTN_BUY'] : ''),
                                            'MESS_BTN_ADD_TO_BASKET' => (isset($arParams['~MESS_BTN_ADD_TO_BASKET']) ? $arParams['~MESS_BTN_ADD_TO_BASKET'] : ''),
                                            'MESS_BTN_DETAIL' => (isset($arParams['~MESS_BTN_DETAIL']) ? $arParams['~MESS_BTN_DETAIL'] : ''),
                                            'MESS_NOT_AVAILABLE' => (isset($arParams['~MESS_NOT_AVAILABLE']) ? $arParams['~MESS_NOT_AVAILABLE'] : ''),
                                            'ADD_TO_BASKET_ACTION' => (isset($arParams['ADD_TO_BASKET_ACTION']) ? $arParams['ADD_TO_BASKET_ACTION'] : ''),
                                            'SHOW_CLOSE_POPUP' => (isset($arParams['SHOW_CLOSE_POPUP']) ? $arParams['SHOW_CLOSE_POPUP'] : ''),
                                            'DISPLAY_COMPARE' => (isset($arParams['DISPLAY_COMPARE']) ? $arParams['DISPLAY_COMPARE'] : ''),
                                            'COMPARE_PATH' => (isset($arParams['COMPARE_PATH']) ? $arParams['COMPARE_PATH'] : '')
                                        ]
                                         + [
                                            'OFFER_ID' => empty($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'])
                                            ? $arResult['ID']
                                            : $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'],
                                            'SECTION_ID' => $arResult['SECTION']['ID'],
                                            'ELEMENT_ID' => $arResult['ID'],

                                            'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
                                            'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
                                            'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY']
                                        ],
                                        $component,
                                        ['HIDE_ICONS' => 'Y']
                                    );
                                ?>
					</div>
					<?
                        }
                    ?>
			</div>
		</div>
	</div>

	<!--Top tabs-->
	<div class="product-item-detail-tabs-container-fixed hidden-xs" id="<?=$itemIds['TABS_PANEL_ID']?>">
		<ul class="product-item-detail-tabs-list">

				<li class="product-item-detail-tab active" data-entity="tab" data-value="description">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arParams['MESS_DESCRIPTION_TAB']?></span>
					</a>
				</li>
				<?

                    if (!empty($arResult['DISPLAY_PROPERTIES']) || $arResult['SHOW_OFFERS_PROPS']) {
                    ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="properties">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arParams['MESS_PROPERTIES_TAB']?></span>
					</a>
				</li>
				<?
                }?>
<?if (!empty($arResult['PROPERTIES']['instrukcija']['VALUE'])): ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="documents">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span>Документы</span>
					</a>
				</li>
		<?endif;?>
<?if ($arParams['USE_COMMENTS'] === 'Y') {
    ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="comments">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arParams['MESS_COMMENTS_TAB']?></span>
					</a>
				</li>
				<?
                    }
                    if (!empty($arResult['PROPERTIES']['PRIMENENIE']['~VALUE']['TEXT'])) {
                    ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="primenenie">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arResult['PROPERTIES']['PRIMENENIE']['NAME']?></span>
					</a>
				</li>
				<?
                    }
                    if (!empty($arResult['PROPERTIES']['preimushestva']['~VALUE']['TEXT'])) {
                    ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="primenenie">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arResult['PROPERTIES']['preimushestva']['NAME']?></span>
					</a>
				</li>
				<?
                    }
                    if (!empty($arResult['PROPERTIES']['SERTIFICAT']['VALUE'])) {
                    ?>
				<li class="product-item-detail-tab" data-entity="tab" data-value="sertificat">
					<a href="javascript:void(0);" class="product-item-detail-tab-link">
						<span><?=$arResult['PROPERTIES']['SERTIFICAT']['NAME']?></span>
					</a>
				</li>
				<?
                    }

                ?>
		</ul>
	</div>

	<meta itemprop="name" content="<?=$name?>" />
	<meta itemprop="category" content="<?=$arResult['CATEGORY_PATH']?>" />
	<?
        if ($haveOffers) {
            foreach ($arResult['JS_OFFERS'] as $offer) {
                $currentOffersList = [];

                if (!empty($offer['TREE']) && is_array($offer['TREE'])) {
                    foreach ($offer['TREE'] as $propName => $skuId) {
                        $propId = (int) substr($propName, 5);

                        foreach ($skuProps as $prop) {
                            if ($prop['ID'] == $propId) {
                                foreach ($prop['VALUES'] as $propId => $propValue) {
                                    if ($propId == $skuId) {
                                        $currentOffersList[] = $propValue['NAME'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                $offerPrice = $offer['ITEM_PRICES'][$offer['ITEM_PRICE_SELECTED']];
            ?>
			<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
				<meta itemprop="sku" content="<?=htmlspecialcharsbx(implode('/', $currentOffersList))?>" />
				<meta itemprop="price" content="<?=$offerPrice['RATIO_PRICE']?>" />
				<meta itemprop="priceCurrency" content="<?=$offerPrice['CURRENCY']?>" />
				<link itemprop="availability" href="http://schema.org/<?=($offer['CAN_BUY'] ? 'InStock' : 'OutOfStock')?>" />
			</span>
			<?
                    }

                    unset($offerPrice, $currentOffersList);
                } else {
                ?>
		<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
			<meta itemprop="price" content="<?=$price['RATIO_PRICE']?>" />
			<meta itemprop="priceCurrency" content="<?=$price['CURRENCY']?>" />
			<link itemprop="availability" href="http://schema.org/<?=($actualItem['CAN_BUY'] ? 'InStock' : 'OutOfStock')?>" />
		</span>
		<?
            }
        ?>

<?
    if ($haveOffers) {
        $offerIds = [];
        $offerCodes = [];

        $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';

        foreach ($arResult['JS_OFFERS'] as $ind => &$jsOffer) {
            $offerIds[] = (int) $jsOffer['ID'];
            $offerCodes[] = $jsOffer['CODE'];

            $fullOffer = $arResult['OFFERS'][$ind];
            $measureName = $fullOffer['ITEM_MEASURE']['TITLE'];

            $strAllProps = '';
            $strMainProps = '';
            $strPriceRangesRatio = '';
            $strPriceRanges = '';

            if ($arResult['SHOW_OFFERS_PROPS']) {
                if (!empty($jsOffer['DISPLAY_PROPERTIES'])) {
                    foreach ($jsOffer['DISPLAY_PROPERTIES'] as $property) {
                        $current = '<dt>' . $property['NAME'] . '</dt><dd>' . (
                            is_array($property['VALUE'])
                            ? implode(' / ', $property['VALUE'])
                            : $property['VALUE']
                        ) . '</dd>';
                        $strAllProps .= $current;

                        if (isset($arParams['MAIN_BLOCK_OFFERS_PROPERTY_CODE'][$property['CODE']])) {
                            $strMainProps .= $current;
                        }
                    }

                    unset($current);
                }
            }

            if ($arParams['USE_PRICE_COUNT'] && count($jsOffer['ITEM_QUANTITY_RANGES']) > 1) {
                $strPriceRangesRatio = '(' . Loc::getMessage(
                    'CT_BCE_CATALOG_RATIO_PRICE',
                    ['#RATIO#' => ($useRatio
                        ? $fullOffer['ITEM_MEASURE_RATIOS'][$fullOffer['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']
                        : '1'
                    ) . ' ' . $measureName]
                ) . ')';

                foreach ($jsOffer['ITEM_QUANTITY_RANGES'] as $range) {
                    if ($range['HASH'] !== 'ZERO-INF') {
                        $itemPrice = false;

                        foreach ($jsOffer['ITEM_PRICES'] as $itemPrice) {
                            if ($itemPrice['QUANTITY_HASH'] === $range['HASH']) {
                                break;
                            }
                        }

                        if ($itemPrice) {
                            $strPriceRanges .= '<dt>' . Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_FROM',
                                ['#FROM#' => $range['SORT_FROM'] . ' ' . $measureName]
                            ) . ' ';

                            if (is_infinite($range['SORT_TO'])) {
                                $strPriceRanges .= Loc::getMessage('CT_BCE_CATALOG_RANGE_MORE');
                            } else {
                                $strPriceRanges .= Loc::getMessage(
                                    'CT_BCE_CATALOG_RANGE_TO',
                                    ['#TO#' => $range['SORT_TO'] . ' ' . $measureName]
                                );
                            }

                            $strPriceRanges .= '</dt><dd>' . ($useRatio ? $itemPrice['PRINT_RATIO_PRICE'] : $itemPrice['PRINT_PRICE']) . '</dd>';
                        }
                    }
                }

                unset($range, $itemPrice);
            }

            $jsOffer['DISPLAY_PROPERTIES'] = $strAllProps;
            $jsOffer['DISPLAY_PROPERTIES_MAIN_BLOCK'] = $strMainProps;
            $jsOffer['PRICE_RANGES_RATIO_HTML'] = $strPriceRangesRatio;
            $jsOffer['PRICE_RANGES_HTML'] = $strPriceRanges;
        }

        $templateData['OFFER_IDS'] = $offerIds;
        $templateData['OFFER_CODES'] = $offerCodes;
        unset($jsOffer, $strAllProps, $strMainProps, $strPriceRanges, $strPriceRangesRatio, $useRatio);

        $jsParams = [
            'CONFIG' => [
                'USE_CATALOG' => $arResult['CATALOG'],
                'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
                'SHOW_PRICE' => true,
                'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
                'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
                'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
                'SHOW_SKU_PROPS' => $arResult['SHOW_OFFERS_PROPS'],
                'OFFER_GROUP' => $arResult['OFFER_GROUP'],
                'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
                'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
                'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
                'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
                'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
                'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
                'USE_STICKERS' => true,
                'USE_SUBSCRIBE' => $showSubscribe,
                'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
                'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
                'ALT' => $alt,
                'TITLE' => $title,
                'MAGNIFIER_ZOOM_PERCENT' => 200,
                'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
                'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
                'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null
            ],
            'PRODUCT_TYPE' => $arResult['CATALOG_TYPE'],
            'VISUAL' => $itemIds,
            'DEFAULT_PICTURE' => [
                'PREVIEW_PICTURE' => $arResult['DEFAULT_PICTURE'],
                'DETAIL_PICTURE' => $arResult['DEFAULT_PICTURE']
            ],
            'PRODUCT' => [
                'ID' => $arResult['ID'],
                'ACTIVE' => $arResult['ACTIVE'],
                'NAME' => $arResult['~NAME'],
                'CATEGORY' => $arResult['CATEGORY_PATH']
            ],
            'BASKET' => [
                'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
                'BASKET_URL' => $arParams['BASKET_URL'],
                'SKU_PROPS' => $arResult['OFFERS_PROP_CODES'],
                'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
                'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
            ],
            'OFFERS' => $arResult['JS_OFFERS'],
            'OFFER_SELECTED' => $arResult['OFFERS_SELECTED'],
            'TREE_PROPS' => $skuProps
        ];
    } else {
        $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
        if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !$emptyProductProperties) {
        ?>
		<div id="<?=$itemIds['BASKET_PROP_DIV']?>" style="display: none;">
			<?
                        if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])) {
                            foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propId => $propInfo) {
                            ?>
					<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propId?>]" value="<?=htmlspecialcharsbx($propInfo['ID'])?>">
					<?
                                        unset($arResult['PRODUCT_PROPERTIES'][$propId]);
                                    }
                                }

                                $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
                                if (!$emptyProductProperties) {
                                ?>
				<table>
					<?
                                    foreach ($arResult['PRODUCT_PROPERTIES'] as $propId => $propInfo) {
                                    ?>
						<tr>
							<td><?=$arResult['PROPERTIES'][$propId]['NAME']?></td>
							<td>
								<?
                                                    if (
                                                        $arResult['PROPERTIES'][$propId]['PROPERTY_TYPE'] === 'L'
                                                        && $arResult['PROPERTIES'][$propId]['LIST_TYPE'] === 'C'
                                                    ) {
                                                        foreach ($propInfo['VALUES'] as $valueId => $value) {
                                                        ?>
										<label>
											<input type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propId?>]"
												value="<?=$valueId?>" <?=($valueId == $propInfo['SELECTED'] ? '"checked"' : '')?>>
											<?=$value?>
										</label>
										<br>
										<?
                                                                }
                                                            } else {
                                                            ?>
									<select name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propId?>]">
										<?
                                                                foreach ($propInfo['VALUES'] as $valueId => $value) {
                                                                ?>
											<option value="<?=$valueId?>" <?=($valueId == $propInfo['SELECTED'] ? '"selected"' : '')?>>
												<?=$value?>
											</option>
											<?
                                                                    }
                                                                ?>
									</select>
									<?
                                                        }
                                                    ?>
							</td>
						</tr>
						<?
                                        }
                                    ?>
				</table>
				<?
                            }
                        ?>
		</div>
		<?
                }
            ?>

	<?

            $jsParams = [
                'CONFIG' => [
                    'USE_CATALOG' => $arResult['CATALOG'],
                    'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
                    'SHOW_PRICE' => !empty($arResult['ITEM_PRICES']),
                    'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
                    'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
                    'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                    'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
                    'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
                    'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
                    'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
                    'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
                    'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
                    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
                    'USE_STICKERS' => true,
                    'USE_SUBSCRIBE' => $showSubscribe,
                    'ALT' => $alt,
                    'TITLE' => $title,
                    'MAGNIFIER_ZOOM_PERCENT' => 200,
                    'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
                    'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
                    'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                    ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                    : null
                ],
                'VISUAL' => $itemIds,
                'PRODUCT_TYPE' => $arResult['CATALOG_TYPE'],
                'PRODUCT' => [
                    'ID' => $arResult['ID'],
                    'ACTIVE' => $arResult['ACTIVE'],
                    'PICT' => reset($arResult['MORE_PHOTO']),
                    'NAME' => $arResult['~NAME'],
                    'SUBSCRIPTION' => true,
                    'ITEM_PRICE_MODE' => $arResult['ITEM_PRICE_MODE'],
                    'ITEM_PRICES' => $arResult['ITEM_PRICES'],
                    'ITEM_PRICE_SELECTED' => $arResult['ITEM_PRICE_SELECTED'],
                    'ITEM_QUANTITY_RANGES' => $arResult['ITEM_QUANTITY_RANGES'],
                    'ITEM_QUANTITY_RANGE_SELECTED' => $arResult['ITEM_QUANTITY_RANGE_SELECTED'],
                    'ITEM_MEASURE_RATIOS' => $arResult['ITEM_MEASURE_RATIOS'],
                    'ITEM_MEASURE_RATIO_SELECTED' => $arResult['ITEM_MEASURE_RATIO_SELECTED'],
                    'SLIDER_COUNT' => $arResult['MORE_PHOTO_COUNT'],
                    'SLIDER' => $arResult['MORE_PHOTO'],
                    'CAN_BUY' => $arResult['CAN_BUY'],
                    'CHECK_QUANTITY' => $arResult['CHECK_QUANTITY'],
                    'QUANTITY_FLOAT' => is_float($arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
                    'MAX_QUANTITY' => $arResult['CATALOG_QUANTITY'],
                    'STEP_QUANTITY' => $arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
                    'CATEGORY' => $arResult['CATEGORY_PATH']
                ],
                'BASKET' => [
                    'ADD_PROPS' => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
                    'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
                    'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
                    'EMPTY_PROPS' => $emptyProductProperties,
                    'BASKET_URL' => $arParams['BASKET_URL'],
                    'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
                    'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
                ]
            ];
            unset($emptyProductProperties);
        }
    ?>


<div class="row ask-question-wrap"> <div class="ask-question-title col-xs-12 col-sm-6 col-md-3">            <div>Обратная связь</div>
        </div>  <div class="ask-question-button-wrap col-xs-12 col-sm-6 col-md-9">
<?$APPLICATION->IncludeComponent(
        'altasib:feedback.form',
        'zvonok',
        [
            'ACTIVE_ELEMENT' => 'Y',
            'ADD_HREF_LINK' => 'Y',
            'ADD_LEAD' => 'N',
            'ALX_IBLOCK_ELEMENT_LINK' => '',
            'ALX_LINK_POPUP' => 'Y',
            'ALX_LOAD_PAGE' => 'N',
            'ALX_NAME_LINK' => 'Напишите нам',
            'ALX_POPUP_TITLE' => '',
            'BBC_MAIL' => '',
            'CAPTCHA_TYPE' => 'default',
            'CATEGORY_SELECT_NAME' => 'Выберите категорию',
            'CHANGE_CAPTCHA' => 'N',
            'CHECKBOX_TYPE' => 'CHECKBOX',
            'CHECK_ERROR' => 'Y',
            'COLOR_OTHER' => '#009688',
            'COLOR_SCHEME' => 'BRIGHT',
            'COLOR_THEME' => '',
            'COMPONENT_TEMPLATE' => 'zvonok',
            'COMPOSITE_FRAME_MODE' => 'A',
            'COMPOSITE_FRAME_TYPE' => 'AUTO',
            'EVENT_TYPE' => 'ALX_FEEDBACK_FORM',
            'FB_TEXT_NAME' => '',
            'FB_TEXT_SOURCE' => 'PREVIEW_TEXT',
            'FORM_ID' => '1',
            'IBLOCK_ID' => '13',
            'IBLOCK_TYPE' => 'altasib_feedback',
            'INPUT_APPEARENCE' => [0 => 'FLOATING_LABELS'],
            'JQUERY_EN' => 'N',
            'LINK_SEND_MORE_TEXT' => 'Отправить ещё одно сообщение',
            'LOCAL_REDIRECT_ENABLE' => 'N',
            'MASKED_INPUT_PHONE' => [0 => 'PHONE'],
            'MESSAGE_OK' => 'Ваше сообщение было успешно отправлено',
            'NAME_ELEMENT' => 'ALX_DATE',
            'NOT_CAPTCHA_AUTH' => 'Y',
            'POPUP_ANIMATION' => '0',
            'PROPERTY_FIELDS' => [0 => 'PHONE', 1 => 'FIO'],
            'PROPERTY_FIELDS_REQUIRED' => [0 => 'PHONE', 1 => 'FIO'],
            'PROPS_AUTOCOMPLETE_EMAIL' => [0 => 'EMAIL'],
            'PROPS_AUTOCOMPLETE_NAME' => [0 => 'FIO'],
            'PROPS_AUTOCOMPLETE_PERSONAL_PHONE' => [0 => 'PHONE'],
            'PROPS_AUTOCOMPLETE_VETO' => 'N',
            'REQUIRED_SECTION' => 'N',
            'SECTION_FIELDS_ENABLE' => 'N',
            'SECTION_MAIL_ALL' => '',
            'SEND_IMMEDIATE' => 'Y',
            'SEND_MAIL' => 'N',
            'SHOW_LINK_TO_SEND_MORE' => 'Y',
            'SHOW_MESSAGE_LINK' => 'Y',
            'SPEC_CHAR' => 'N',
            'USERMAIL_FROM' => 'N',
            'USER_CONSENT' => 'N',
            'USER_CONSENT_ID' => '0',
            'USER_CONSENT_INPUT_LABEL' => '',
            'USER_CONSENT_IS_CHECKED' => 'Y',
            'USER_CONSENT_IS_LOADED' => 'N',
            'USE_CAPTCHA' => 'Y',
            'WIDTH_FORM' => '50%'
        ]
);?>
 </div>    </div>

 <div class='row'>

<div class='pochoz' data-entity="parent-container">

							<p class='zadolovok2'><?=GetMessage('CATALOG_PERSONAL_RECOM')?></p>

<?$rsList = CIBlockElement::GetList(
        ['NAME' => 'DESC'],
        ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y', 'SECTION_ID' => $arResult['SECTION']['ID']],
        false,
        ['nPageSize' => '4', 'nElementID' => $arResult['ID']],

        ['ID']
    );
    $namesArray = [];
    while ($arItem = $rsList->Fetch()) {
        if ($arItem['ID'] == $arResult['ID']) {
            continue;
        }

        $namesArray[] = $arItem['ID'];
    }
    $GLOBALS['arCatalogElementFilter'] = [
        'ID' => $namesArray
    ];
?>

<div class="catalog-element-section-recommended">
  <?$APPLICATION->IncludeComponent(
          'bitrix:catalog.section',
          'carusel',
          [
              'ACTION_VARIABLE' => 'action',
              'ADD_PROPERTIES_TO_BASKET' => 'Y',
              'ADD_SECTIONS_CHAIN' => 'N',
              'ADD_TO_BASKET_ACTION' => 'ADD',
              'AJAX_MODE' => 'N',
              'AJAX_OPTION_ADDITIONAL' => '',
              'AJAX_OPTION_HISTORY' => 'N',
              'AJAX_OPTION_JUMP' => 'N',
              'AJAX_OPTION_STYLE' => 'Y',
              'BACKGROUND_IMAGE' => '-',
              'BASKET_URL' => '/personal/basket.php',
              'BORDERS' => 'Y',
              'BROWSER_TITLE' => '-',
              'CACHE_FILTER' => 'N',
              'CACHE_GROUPS' => 'Y',
              'CACHE_TIME' => '3600',
              'CACHE_TYPE' => 'Y',
              'COLUMNS' => 4,
              'COMPATIBLE_MODE' => 'Y',
              'COMPOSITE_FRAME_MODE' => 'A',
              'COMPOSITE_FRAME_TYPE' => 'AUTO',
              'CONVERT_CURRENCY' => 'N',
              'CURRENCY_ID' => $arParams['CURRENCY_ID'],
              'DETAIL_URL' => '',
              'DISABLE_INIT_JS_IN_COMPONENT' => 'N',
              'DISPLAY_BOTTOM_PAGER' => 'N',
              'DISPLAY_COMPARE' => 'N',
              'DISPLAY_TOP_PAGER' => 'N',
              'ELEMENT_SORT_FIELD' => 'sort',
              'ELEMENT_SORT_FIELD2' => 'id',
              'ELEMENT_SORT_ORDER' => 'asc',
              'ELEMENT_SORT_ORDER2' => 'desc',
              'FILTER_NAME' => 'arCatalogElementFilter',
              'HIDE_NOT_AVAILABLE' => 'N',
              'HIDE_NOT_AVAILABLE_OFFERS' => 'N',
              'IBLOCK_ID' => $arParams['IBLOCK_ID'],
              'IBLOCK_TYPE' => 'altasib_feedback',
              'INCLUDE_SUBSECTIONS' => 'Y',
              'LAZY_LOAD' => 'N',
              'LINE_ELEMENT_COUNT' => '2',
              'LOAD_ON_SCROLL' => 'N',
              'MESSAGE_404' => '',
              'MESS_BTN_ADD_TO_BASKET' => 'В корзину',
              'MESS_BTN_BUY' => 'Купить',
              'MESS_BTN_DETAIL' => 'Подробнее',
              'MESS_BTN_SUBSCRIBE' => 'Подписаться',
              'MESS_NOT_AVAILABLE' => 'Нет в наличии',
              'META_DESCRIPTION' => '-',
              'META_KEYWORDS' => '-',
              'OFFERS_LIMIT' => '0',
              'PAGER_BASE_LINK_ENABLE' => 'N',
              'PAGER_DESC_NUMBERING' => 'N',
              'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
              'PAGER_SHOW_ALL' => 'N',
              'PAGER_SHOW_ALWAYS' => 'N',
              'PAGER_TEMPLATE' => '.default',
              'PAGER_TITLE' => 'Товары',
              'PAGE_ELEMENT_COUNT' => '6',
              'PARTIAL_PRODUCT_PROPERTIES' => 'N',
              'POSITION' => 'left',
              'PRICE_CODE' => ['BASE'],
              'PRICE_VAT_INCLUDE' => 'Y',
              'PRODUCT_ID_VARIABLE' => 'id',
              'PRODUCT_PROPERTIES' => [],
              'PRODUCT_PROPS_VARIABLE' => 'prop',
              'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
              'PRODUCT_SUBSCRIPTION' => 'N',
              'PROPERTY_CODE' => ['', ''],
              'RCM_PROD_ID' => $_REQUEST['PRODUCT_ID'],
              'RCM_TYPE' => 'personal',
              'SECTION_CODE' => '',
              'SECTION_ID' => $_REQUEST['SECTION_ID'],
              'SECTION_ID_VARIABLE' => 'SECTION_ID',
              'SECTION_URL' => '',
              'SECTION_USER_FIELDS' => ['', ''],
              'SEF_MODE' => 'N',
              'SET_BROWSER_TITLE' => 'N',
              'SET_LAST_MODIFIED' => 'N',
              'SET_META_DESCRIPTION' => 'N',
              'SET_META_KEYWORDS' => 'N',
              'SET_STATUS_404' => 'N',
              'SET_TITLE' => 'N',
              'SHOW_404' => 'N',
              'SHOW_ALL_WO_SECTION' => 'Y',
              'SHOW_CLOSE_POPUP' => 'Y',
              'SHOW_DISCOUNT_PERCENT' => 'N',
              'SHOW_FROM_SECTION' => 'N',
              'SHOW_MAX_QUANTITY' => 'N',
              'SHOW_OLD_PRICE' => 'N',
              'SHOW_PRICE_COUNT' => '1',
              'SIZE' => 'small',
              'SLIDER_DOTS' => 'Y',
              'SLIDER_USE' => 'Y',
              'TEMPLATE_THEME' => 'blue',
              'USE_ENHANCED_ECOMMERCE' => 'N',
              'USE_MAIN_ELEMENT_SECTION' => 'N',
              'USE_PRICE_COUNT' => 'N',
              'USE_PRODUCT_QUANTITY' => 'N',
              'WIDE' => $arVisual['WIDE'] ? 'Y' : 'N'
          ],
          $component
  );?>
</div>
		</div>
					</div>

<?$ElementId = $arResult['ID'];
$db_groups = CIBlockElement::GetElementGroups($ElementId, true);
?><div class='row'>
		<p class='zadolovok2'>Популярные разделы</p>
   <div class='ul_block ul_block_top'><?while ($ar_group = $db_groups->GetNext()) {
                                              echo '<a href="' . $ar_group['SECTION_PAGE_URL'] . '">';
                                              echo $ar_group['NAME'];
                                          echo '</a>';?>
<?}?></div>
		</div>
				</div>
		</div>
<br><br>
 <div class=''>
</div>

<?
    if ($arParams['DISPLAY_COMPARE']) {
        $jsParams['COMPARE'] = [
            'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
            'COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
            'COMPARE_PATH' => $arParams['COMPARE_PATH']
        ];
    }
?>
<script>
	BX.message({
		ECONOMY_INFO_MESSAGE: '<?=GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2')?>',
		TITLE_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR')?>',
		TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS')?>',
		BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR')?>',
		BTN_SEND_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS')?>',
		BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
		BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE')?>',
		BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
		TITLE_SUCCESSFUL: '<?=GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK')?>',
		COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK')?>',
		COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
		COMPARE_TITLE: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE')?>',
		BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
		PRODUCT_GIFT_LABEL: '<?=GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL')?>',
		PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX')?>',
		RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
		RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
		SITE_ID: '<?=SITE_ID?>'
	});

	var <?=$obName?> = new JCCatalogElement(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>

<?

unset($actualItem, $itemIds, $jsParams);