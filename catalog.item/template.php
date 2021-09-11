<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
    }

    use \Bitrix\Main\Localization\Loc;

    /**
     * @global CMain $APPLICATION
     * @var array $arParams
     * @var array $item
     * @var array $actualItem
     * @var array $minOffer
     * @var array $itemIds
     * @var array $price
     * @var array $measureRatio
     * @var bool $haveOffers
     * @var bool $showSubscribe
     * @var array $morePhoto
     * @var bool $showSlider
     * @var bool $itemHasDetailUrl
     * @var string $imgTitle
     * @var string $productTitle
     * @var string $buttonSizeClass
     * @var CatalogSectionComponent $component
     */
?>

<div class="product-item">
<?$APPLICATION->IncludeComponent(
        'webes:oneclick',
        'green1',
        [
            'BUTTON_ONE_CLICK' => 'Купить в 1 клик',
            'BUTTON_ONE_CLICK_CLASS' => 'o-w-btn o-w-btn-sm',
            'CART_COUNT' => 'Всего товаров:',
            'CART_SUM' => 'на сумму',
            'CHOOSE_PROPERTIES' => [
                0 => '',
                1 => ''
            ],
            'CURRENT_CART' => 'N',
            'CURRENT_CART_EMPTY' => 'Ваша корзина пуста',
            'CURRENT_CART_ORDER' => 'Оформление заказа',
            'CURRENT_CART_TITLE' => 'Текущая корзина',
            'ELEMENT_ID' => $item['ID'],
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
            'MODAL_EMAIL_EX' => 'info@svet-shop.online',
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
            'COMPONENT_TEMPLATE' => 'green1',
            'COMPOSITE_FRAME_MODE' => 'A',
            'COMPOSITE_FRAME_TYPE' => 'AUTO'
        ],
        false
);?>
<?if ($itemHasDetailUrl): ?>
	<a class="product-item-image-wrapper" href="<?=$item['DETAIL_PAGE_URL']?>" title="<?=$imgTitle?>"
		data-entity="image-wrapper">
		<?else: ?>
		<span class="product-item-image-wrapper" data-entity="image-wrapper">
	<?endif;?>
		<span class="product-item-image-slider-slide-container slide" id="<?=$itemIds['PICT_SLIDER']?>"
			<?=($showSlider ? '' : 'style="display: none;"')?>
			data-slider-interval="<?=$arParams['SLIDER_INTERVAL']?>" data-slider-wrap="true">
			<?
                if ($showSlider) {
                    foreach ($morePhoto as $key => $photo) {
                    ?>
					<span class="product-item-image-slide item <?=($key == 0 ? 'active' : '')?>" style="background-image: url('<?=$photo['SRC']?>');"></span>
					<?
                            }
                        }
                    ?>
		</span>
<?
    $renderImage = CFile::ResizeImageGet($item['PREVIEW_PICTURE']['ID'], ['width' => 200, 'height' => 200], BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
?>
		<span class="product-item-image-original" id="<?=$itemIds['PICT']?>"
			src='//opt-1645342.ssl.1c-bitrix-cdn.ru/bitrix/templates/eshop_bootstrap_green/js/picture.loading.svg?16028444841838'  loading="lazy" data-lazyload-use="true" data-original="<?=$renderImage['src']?>"style="<?=($showSlider ? 'display: none;' : '')?>"></span>
		<?
            if ($item['SECOND_PICT']) {
                $bgImage = !empty($item['PREVIEW_PICTURE_SECOND']) ? $item['PREVIEW_PICTURE_SECOND']['SRC'] : $item['PREVIEW_PICTURE']['SRC'];
            ?>

			<span class="product-item-image-alternative" id="<?=$itemIds['SECOND_PICT']?>"></span>
			<?
                }

                if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y') {
                ?>
			<div class="product-item-label-ring <?=$discountPositionClass?>" id="<?=$itemIds['DSC_PERC']?>"
				<?=($price['PERCENT'] > 0 ? '' : 'style="display: none;"')?>>
				<span><?=-$price['PERCENT']?>%</span>
			</div>
			<?
                }

                if ($item['LABEL']) {
                ?>
			<div class="product-item-label-text <?=$labelPositionClass?>" id="<?=$itemIds['STICKER_ID']?>">
				<?
                        if (!empty($item['LABEL_ARRAY_VALUE'])) {
                            foreach ($item['LABEL_ARRAY_VALUE'] as $code => $value) {
                            ?>
						<div<?=(!isset($item['LABEL_PROP_MOBILE'][$code]) ? ' class="d-none d-sm-block"' : '')?>>
							<span title="<?=$value?>"><?=$value?></span>
						</div>
						<?
                                    }
                                }
                            ?>
			</div>
			<?
                }
            ?>
		<span class="product-item-image-slider-control-container" id="<?=$itemIds['PICT_SLIDER']?>_indicator"
			<?=($showSlider ? '' : 'style="display: none;"')?>>
			<?
                if ($showSlider) {
                    foreach ($morePhoto as $key => $photo) {
                    ?>
					<span class="product-item-image-slider-control<?=($key == 0 ? ' active' : '')?>" data-go-to="<?=$key?>"></span>
					<?
                            }
                        }
                    ?>
		</span>
		<?
            if ($arParams['SLIDER_PROGRESS'] === 'Y') {
            ?>
			<span class="product-item-image-slider-progress-bar-container">
				<span class="product-item-image-slider-progress-bar" id="<?=$itemIds['PICT_SLIDER']?>_progress_bar" style="width: 0;"></span>
			</span>
			<?
                }
            ?>
<?if ($itemHasDetailUrl): ?>
	</a>
<?else: ?>
	</span>
<?endif;?>
<?//ITEM TITLE START?>
<p class="product-item-title">
    <?if ($itemHasDetailUrl): ?>
        <a href="<?=$item['DETAIL_PAGE_URL']?>" title="<?=$productTitle?>">
    <?endif;?>
<?if ($item['PROPERTIES']['IZMENENIE']['VALUE'] == Y && $GLOBALS['name_razdel']): ?>
            <?=($GLOBALS['name_razdel'] . ' ' . ($item['PROPERTIES']['NAME_SOKRASH']['VALUE'] ? $item['PROPERTIES']['NAME_SOKRASH']['VALUE'] : $arItem['NAME']))?>
<?else: ?>
            <?=$item['NAME']?>
<?endif;?>
<?if ($itemHasDetailUrl): ?>
        </a>
    <?endif;?>
</p>
<?//ITEM TITLE END?>
<?
    if (!empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
        foreach ($arParams['PRODUCT_BLOCKS_ORDER'] as $blockName) {
            switch ($blockName) {
            case 'price': ?>
					<div class="product-item-info-container product-item-price-container" data-entity="price-block">
						<?
                                            if ($arParams['SHOW_OLD_PRICE'] === 'Y') {
                                            ?>
							<span class="product-item-price-old" id="<?=$itemIds['PRICE_OLD']?>"
								<?=($price['RATIO_PRICE'] >= $price['RATIO_BASE_PRICE'] ? 'style="display: none;"' : '')?>>
								<?=$price['PRINT_RATIO_BASE_PRICE']?>
							</span>&nbsp;
							<?
                                                }
                                            ?>
						<span class="product-item-price-current" id="<?=$itemIds['PRICE']?>">
							<?
                                                if (!empty($price)) {
                                                    if ($arParams['PRODUCT_DISPLAY_MODE'] === 'N' && $haveOffers) {
                                                        echo Loc::getMessage(
                                                            'CT_BCI_TPL_MESS_PRICE_SIMPLE_MODE',
                                                            [
                                                                '#PRICE#' => $price['PRINT_RATIO_PRICE'],
                                                                '#VALUE#' => $measureRatio,
                                                                '#UNIT#' => $minOffer['ITEM_MEASURE']['TITLE']
                                                            ]
                                                        );
                                                    } else {
                                                        echo $price['PRINT_RATIO_PRICE'];
                                                    }
                                                }
                                            ?>
						</span>
					</div>
					<?
                                        break;

                                    case 'quantityLimit':
                                        if ($arParams['SHOW_MAX_QUANTITY'] !== 'N') {
                                            if ($haveOffers) {
                                                if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y') {
                                                ?>
								<div class="product-item-info-container product-item-hidden"
									id="<?=$itemIds['QUANTITY_LIMIT']?>"
									style="display: none;"
									data-entity="quantity-limit-block">
									<div class="product-item-info-container-title text-muted">
										<?=$arParams['MESS_SHOW_MAX_QUANTITY']?>:
										<span class="product-item-quantity text-dark" data-entity="quantity-limit-value"></span>
									</div>
								</div>
								<?
                                                            }
                                                        } else {
                                                            if (
                                                                $measureRatio
                                                                && (float) $actualItem['CATALOG_QUANTITY'] > 0
                                                                && $actualItem['CATALOG_QUANTITY_TRACE'] === 'Y'
                                                                && $actualItem['CATALOG_CAN_BUY_ZERO'] === 'N'
                                                            ) {
                                                            ?>
								<div class="product-item-info-container product-item-hidden" id="<?=$itemIds['QUANTITY_LIMIT']?>">
									<div class="product-item-info-container-title text-muted">
										<?=$arParams['MESS_SHOW_MAX_QUANTITY']?>:
										<span class="product-item-quantity text-dark" data-entity="quantity-limit-value">
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
                                                    if (!$haveOffers) {
                                                        if ($actualItem['CAN_BUY'] && $arParams['USE_PRODUCT_QUANTITY']) {
                                                        ?>
							<div class="product-item-info-container product-item-hidden" data-entity="quantity-block">
								<div class="product-item-amount">
									<div class="product-item-amount-field-container">
										<span class="product-item-amount-field-btn-minus no-select" id="<?=$itemIds['QUANTITY_DOWN']?>"></span>
										<div class="product-item-amount-field-block">
											<input class="product-item-amount-field" id="<?=$itemIds['QUANTITY']?>" type="number" name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>" value="<?=$measureRatio?>">

										</div>
										<span class="product-item-amount-field-btn-plus no-select" id="<?=$itemIds['QUANTITY_UP']?>"></span>
									</div>
								</div>
							</div>
							<?
                                                    }
                                                } elseif ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y') {
                                                    if ($arParams['USE_PRODUCT_QUANTITY']) {
                                                    ?>
							<div class="product-item-info-container product-item-hidden" data-entity="quantity-block">
								<div class="product-item-amount">
									<div class="product-item-amount-field-container">
										<span class="product-item-amount-field-btn-minus no-select" id="<?=$itemIds['QUANTITY_DOWN']?>"></span>
										<div class="product-item-amount-field-block">
											<input class="product-item-amount-field" id="<?=$itemIds['QUANTITY']?>" type="number" name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>" value="<?=$measureRatio?>">
											<span class="product-item-amount-description-container">
												<span id="<?=$itemIds['QUANTITY_MEASURE']?>"><?=$actualItem['ITEM_MEASURE']['TITLE']?></span>
												<span id="<?=$itemIds['PRICE_TOTAL']?>"></span>
											</span>
										</div>
										<span class="product-item-amount-field-btn-plus no-select" id="<?=$itemIds['QUANTITY_UP']?>"></span>
									</div>
								</div>
							</div>
							<?
                                                    }
                                                }

                                                break;

                                            case 'buttons':
                                            ?>
					<div class="product-item-info-container product-item-hidden" data-entity="buttons-block">
						<?
                                            if (!$haveOffers) {
                                                if ($actualItem['CAN_BUY']) {
                                                ?>
								<div class="product-item-button-container" id="<?=$itemIds['BASKET_ACTIONS']?>">
									<button class="btn btn-primary <?=$buttonSizeClass?>" id="<?=$itemIds['BUY_LINK']?>"
											href="javascript:void(0)" rel="nofollow">
										<?=($arParams['ADD_TO_BASKET_ACTION'] === 'BUY' ? $arParams['MESS_BTN_BUY'] : $arParams['MESS_BTN_ADD_TO_BASKET'])?>
									</button>
								</div>
								<?
                                                        } else {
                                                        ?>
								<div class="product-item-button-container">
									<?
                                                                if ($showSubscribe) {
                                                                    $APPLICATION->IncludeComponent(
                                                                        'bitrix:catalog.product.subscribe',
                                                                        '',
                                                                        [
                                                                            'PRODUCT_ID' => $actualItem['ID'],
                                                                            'BUTTON_ID' => $itemIds['SUBSCRIBE_LINK'],
                                                                            'BUTTON_CLASS' => 'btn btn-primary ' . $buttonSizeClass,
                                                                            'DEFAULT_DISPLAY' => true,
                                                                            'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE']
                                                                        ],
                                                                        $component,
                                                                        ['HIDE_ICONS' => 'Y']
                                                                    );
                                                                }
                                                            ?>
									<button class="btn btn-link <?=$buttonSizeClass?>"
											id="<?=$itemIds['NOT_AVAILABLE_MESS']?>" href="javascript:void(0)" rel="nofollow">
										<?=$arParams['MESS_NOT_AVAILABLE']?>
									</button>
								</div>
								<?
                                                        }
                                                    } else {
                                                        if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y') {
                                                        ?>
								<div class="product-item-button-container">
									<?
                                                                if ($showSubscribe) {
                                                                    $APPLICATION->IncludeComponent(
                                                                        'bitrix:catalog.product.subscribe',
                                                                        '',
                                                                        [
                                                                            'PRODUCT_ID' => $item['ID'],
                                                                            'BUTTON_ID' => $itemIds['SUBSCRIBE_LINK'],
                                                                            'BUTTON_CLASS' => 'btn btn-primary ' . $buttonSizeClass,
                                                                            'DEFAULT_DISPLAY' => !$actualItem['CAN_BUY'],
                                                                            'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE']
                                                                        ],
                                                                        $component,
                                                                        ['HIDE_ICONS' => 'Y']
                                                                    );
                                                                }
                                                            ?>
									<button class="btn btn-link <?=$buttonSizeClass?>"
											id="<?=$itemIds['NOT_AVAILABLE_MESS']?>" href="javascript:void(0)" rel="nofollow"
										<?=($actualItem['CAN_BUY'] ? 'style="display: none;"' : '')?>>
										<?=$arParams['MESS_NOT_AVAILABLE']?>
									</button>
									<div id="<?=$itemIds['BASKET_ACTIONS']?>" <?=($actualItem['CAN_BUY'] ? '' : 'style="display: none;"')?>>
										<button class="btn btn-primary <?=$buttonSizeClass?>" id="<?=$itemIds['BUY_LINK']?>"
												href="javascript:void(0)" rel="nofollow">
											<?=($arParams['ADD_TO_BASKET_ACTION'] === 'BUY' ? $arParams['MESS_BTN_BUY'] : $arParams['MESS_BTN_ADD_TO_BASKET'])?>
										</button>
									</div>
								</div>
								<?
                                                        } else {
                                                        ?>
								<div class="product-item-button-container">
									<button class="btn btn-primary <?=$buttonSizeClass?>" href="<?=$item['DETAIL_PAGE_URL']?>">
										<?=$arParams['MESS_BTN_DETAIL']?>
									</button>
								</div>
								<?
                                                        }
                                                    }
                                                ?>
					</div>
					<?
                                        break;

                                    case 'props':
                                        if (!$haveOffers) {
                                            if (!empty($item['DISPLAY_PROPERTIES'])) {
                                            ?>
							<div class="product-item-info-container product-item-hidden" data-entity="props-block">
								<dl class="product-item-properties">
									<?
                                                                foreach ($item['DISPLAY_PROPERTIES'] as $code => $displayProperty) {
                                                                ?>
										<dt class="text-muted<?=(!isset($item['PROPERTY_CODE_MOBILE'][$code]) ? ' d-none d-sm-block' : '')?>">
											<?=$displayProperty['NAME']?>
										</dt>
										<dd class="text-dark<?=(!isset($item['PROPERTY_CODE_MOBILE'][$code]) ? ' d-none d-sm-block' : '')?>">
											<?=(is_array($displayProperty['DISPLAY_VALUE'])
                            ? implode(' / ', $displayProperty['DISPLAY_VALUE'])
                            : $displayProperty['DISPLAY_VALUE'])?>
										</dd>
										<?
                                                                    }
                                                                ?>
								</dl>
							</div>
							<?
                                                    }

                                                    if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !empty($item['PRODUCT_PROPERTIES'])) {
                                                    ?>
							<div id="<?=$itemIds['BASKET_PROP_DIV']?>" style="display: none;">
								<?
                                                            if (!empty($item['PRODUCT_PROPERTIES_FILL'])) {
                                                                foreach ($item['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo) {
                                                                ?>
										<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]"
											value="<?=htmlspecialcharsbx($propInfo['ID'])?>">
										<?
                                                                            unset($item['PRODUCT_PROPERTIES'][$propID]);
                                                                        }
                                                                    }

                                                                    if (!empty($item['PRODUCT_PROPERTIES'])) {
                                                                    ?>
									<table>
										<?
                                                                        foreach ($item['PRODUCT_PROPERTIES'] as $propID => $propInfo) {
                                                                        ?>
											<tr>
												<td><?=$item['PROPERTIES'][$propID]['NAME']?></td>
												<td>
													<?
                                                                                        if (
                                                                                            $item['PROPERTIES'][$propID]['PROPERTY_TYPE'] === 'L'
                                                                                            && $item['PROPERTIES'][$propID]['LIST_TYPE'] === 'C'
                                                                                        ) {
                                                                                            foreach ($propInfo['VALUES'] as $valueID => $value) {
                                                                                            ?>
															<label>
																<?$checked = $valueID === $propInfo['SELECTED'] ? 'checked' : '';?>
																<input type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]"
																	value="<?=$valueID?>" <?=$checked?>>
																<?=$value?>
															</label>
															<br />
															<?
                                                                                                    }
                                                                                                } else {
                                                                                                ?>
														<select name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]">
															<?
                                                                                                    foreach ($propInfo['VALUES'] as $valueID => $value) {
                                                                                                        $selected = $valueID === $propInfo['SELECTED'] ? 'selected' : '';
                                                                                                    ?>
																<option value="<?=$valueID?>" <?=$selected?>>
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
                                                } else {
                                                    $showProductProps = !empty($item['DISPLAY_PROPERTIES']);
                                                    $showOfferProps = $arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && $item['OFFERS_PROPS_DISPLAY'];

                                                    if ($showProductProps || $showOfferProps) {
                                                    ?>
							<div class="product-item-info-container product-item-hidden" data-entity="props-block">
								<dl class="product-item-properties">
									<?
                                                                if ($showProductProps) {
                                                                    foreach ($item['DISPLAY_PROPERTIES'] as $code => $displayProperty) {
                                                                    ?>
											<dt class="text-muted<?=(!isset($item['PROPERTY_CODE_MOBILE'][$code]) ? ' d-none d-sm-block' : '')?>">
												<?=$displayProperty['NAME']?>
											</dt>
											<dd class="text-dark<?=(!isset($item['PROPERTY_CODE_MOBILE'][$code]) ? ' d-none d-sm-block' : '')?>">
												<?=(is_array($displayProperty['DISPLAY_VALUE'])
                                ? implode(' / ', $displayProperty['DISPLAY_VALUE'])
                                : $displayProperty['DISPLAY_VALUE'])?>
											</dd>
											<?
                                                                            }
                                                                        }

                                                                        if ($showOfferProps) {
                                                                        ?>
										<span id="<?=$itemIds['DISPLAY_PROP_DIV']?>" style="display: none;"></span>
										<?
                                                                    }
                                                                ?>
								</dl>
							</div>
							<?
                                                    }
                                                }

                                                break;

                                            case 'sku':
                                                if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && $haveOffers && !empty($item['OFFERS_PROP'])) {
                                                ?>
						<div class="product-item-info-container product-item-hidden" id="<?=$itemIds['PROP_DIV']?>">
							<?
                                                    foreach ($arParams['SKU_PROPS'] as $skuProperty) {
                                                        $propertyId = $skuProperty['ID'];
                                                        $skuProperty['NAME'] = htmlspecialcharsbx($skuProperty['NAME']);
                                                        if (!isset($item['SKU_TREE_VALUES'][$propertyId])) {
                                                            continue;
                                                        }

                                                    ?>
								<div data-entity="sku-block">
									<div class="product-item-scu-container" data-entity="sku-line-block">
										<div class="product-item-scu-block-title text-muted"><?=$skuProperty['NAME']?></div>
										<div class="product-item-scu-block">
											<div class="product-item-scu-list">
												<ul class="product-item-scu-item-list">
													<?
                                                                                foreach ($skuProperty['VALUES'] as $value) {
                                                                                    if (!isset($item['SKU_TREE_VALUES'][$propertyId][$value['ID']])) {
                                                                                        continue;
                                                                                    }

                                                                                    $value['NAME'] = htmlspecialcharsbx($value['NAME']);

                                                                                    if ($skuProperty['SHOW_MODE'] === 'PICT') {
                                                                                    ?>
															<li class="product-item-scu-item-color-container" title="<?=$value['NAME']?>" data-treevalue="<?=$propertyId?>_<?=$value['ID']?>" data-onevalue="<?=$value['ID']?>">
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
															<li class="product-item-scu-item-text-container" title="<?=$value['NAME']?>"
																data-treevalue="<?=$propertyId?>_<?=$value['ID']?>" data-onevalue="<?=$value['ID']?>">
																<div class="product-item-scu-item-color product-item-scu-item-color-text">
																	<?=$value['NAME']?>
																</div>
															</li>
															<?
                                                                                            }
                                                                                        }
                                                                                    ?>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<?
                                                        }
                                                    ?>
						</div>
						<?
                                                foreach ($arParams['SKU_PROPS'] as $skuProperty) {
                                                    if (!isset($item['OFFERS_PROP'][$skuProperty['CODE']])) {
                                                        continue;
                                                    }

                                                    $skuProps[] = [
                                                        'ID' => $skuProperty['ID'],
                                                        'SHOW_MODE' => $skuProperty['SHOW_MODE'],
                                                        'VALUES' => $skuProperty['VALUES'],
                                                        'VALUES_COUNT' => $skuProperty['VALUES_COUNT']
                                                    ];
                                                }

                                                unset($skuProperty, $value);

                                                if ($item['OFFERS_PROPS_DISPLAY']) {
                                                    foreach ($item['JS_OFFERS'] as $keyOffer => $jsOffer) {
                                                        $strProps = '';

                                                        if (!empty($jsOffer['DISPLAY_PROPERTIES'])) {
                                                            foreach ($jsOffer['DISPLAY_PROPERTIES'] as $displayProperty) {
                                                                $strProps .= '<dt>' . $displayProperty['NAME'] . '</dt><dd>'
                                                                    . (is_array($displayProperty['VALUE'])
                                                                    ? implode(' / ', $displayProperty['VALUE'])
                                                                    : $displayProperty['VALUE'])
                                                                    . '</dd>';
                                                            }
                                                        }

                                                        $item['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;
                                                    }
                                                    unset($jsOffer, $strProps);
                                                }
                                            }

                                            break;
                                    }
                                }
                            }
                        ?>
<?//COMPARE + WISHLIST BLOCK START ?>
    <form class="product-item-compare-container">
        <?if ($arParams['DISPLAY_COMPARE'] && (!$haveOffers || $arParams['PRODUCT_DISPLAY_MODE'] === 'Y')): ?>
            <div class="product-item-compare">
                <div class="checkbox">
                    <label id="<?=$itemIds['COMPARE_LINK']?>">
                        <input type="checkbox" data-entity="compare-checkbox">
                        <i class="glyph-icon-compare"></i>
                        <span data-entity="compare-title">
                            <svg width="20" height="20" viewBox="0 0 16 16" class="bi bi-bar-chart-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <rect width="2" height="5" x="1" y="10" rx="1"></rect>
                                <rect width="2" height="9" x="6" y="6" rx="1"></rect>
                                <rect width="2" height="14" x="11" y="1" rx="1"></rect>
                            </svg>
                        </span>
                    </label>
                </div>
            </div>
        <?endif;?>
<?if (true || $arParams['DISPLAY_WISHLIST']): ?>
            <button class="wishlisted-btn" type="button">
                <svg class="wishlisted-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 11 10" fill="#bebebf">
                    <path d="M5.5 10L10 5.5C10 5.5 12.25 3.25002 10 1.00001C7.75 -1.25001 5.5 1.00001 5.5 1.00001C5.5 1.00001 3.25 -1.24999 1 1.00001C-1.25 3.25001 1 5.5 1 5.5L5.5 10Z"></path>
                </svg>
            </button>
        <?endif;?>
    </form>
<?//COMPARE + WISHLIST BLOCK END?>
</div>