<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
    }

    /** @var array $arParams */
    /** @var array $arResult */
    /** @var array $arUrls */
    /** @var array $arHeaders */

    if (!empty($arResult['ERROR_MESSAGE'])) {
        ShowError($arResult['ERROR_MESSAGE']);
    }

    $bDelayColumn = true;
    $bDeleteColumn = true;
    $bWeightColumn = false;
    $bPropsColumn = false;
    $bPriceType = false;

    if ($normalCount > 0):
?>
<div id="basket_items_list" style="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? 'display:none' : '')?>">
	<div class="bx_ordercart_order_table_container">
		<table id="basket_items" class="table-delayed">
		<thead class="table-delayed__head">
			<tr class="table-delayed__row">
				<th class="table-delayed__cell">Товары</th>
				<th class="table-delayed__cell">Цена</th>
				<th class="table-delayed__cell">Количество</th>
				<th class="table-delayed__cell">Сумма</th>
				<th class="table-delayed__cell"></th>
			</tr>
		</thead>
			<tbody class="table-delayed__body">
				<?
                    foreach ($arResult['GRID']['ROWS'] as $k => $arItem):

                        if ($arItem['DELAY'] == 'N' && $arItem['CAN_BUY'] == 'Y'):
                    ?>
																																																																						<tr id="<?=$arItem['ID']?>"
																																																																								data-item-name="<?=$arItem['NAME']?>"
																																																																								data-item-brand="<?=$arItem[$arParams['BRAND_PROPERTY'] . '_VALUE']?>"
																																																																								data-item-price="<?=$arItem['PRICE']?>"
																																																																								data-item-currency="<?=$arItem['CURRENCY']?>"
																																																																								class="table-delayed__row"
																																																																						>
																																																						<td class="table-delayed__cell">
																																																							<div class="table-delayed__title-wrapper">
																																																								<?if ((true && $arItem['PREVIEW_PICTURE_SRC']) || $usePreviewPicture): ?>
																																																									<div class="table-delayed__img-wrapper">
																																																									<img class="table-delayed__img" src="<?=$arItem['PREVIEW_PICTURE_SRC']?>"/>
																																																									</div>
																																																								<?elseif (false && ($useDetailPicture && $arItem['DETAIL_PICTURE_SRC'])): ?>
			<div class="table-delayed__img-wrapper">
			<img class="table-delayed__img" src="<?=$arItem['DETAIL_PICTURE_SRC']?>"/>
			</div>
		<?endif;?>
		<h3 class="table-delayed__title"><a class="table-delayed__link" href="<?=$arItem['DETAIL_PAGE_URL']?>"><?=$arItem['NAME']?></a></h3>
	</div>
</td>
<td class="table-delayed__cell">
	<div class="table-delayed__cell-container">
		<span class="table-delayed__cell-desc">Цена: </span>
		<span id="current_price_<?=$arItem['ID']?>"><?=$arItem['PRICE_FORMATED']?></span>
	</div>
</td>
<td class="table-delayed__cell">
<?
    $ratio = isset($arItem['MEASURE_RATIO']) ? $arItem['MEASURE_RATIO'] : 0;
    $useFloatQuantity = ($arParams['QUANTITY_FLOAT'] == 'Y') ? true : false;
    $useFloatQuantityJS = ($useFloatQuantity ? 'true' : 'false');
    if (!isset($arItem['MEASURE_RATIO'])) {
        $arItem['MEASURE_RATIO'] = 1;
    }
?>
<?if (floatval($arItem['MEASURE_RATIO']) != 0): ?>
<div id="basket_quantity_control" class="basket-quantity">
	<span class="table-delayed__cell-desc table-delayed__cell-desc_control">Количество:</span>
<a class="basket-quantity__btn" href="javascript:void(0);" onclick="setQuantity(<?=$arItem['ID']?>, <?=$arItem['MEASURE_RATIO']?>, 'down', <?=$useFloatQuantityJS?>);">
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"  fill="none" viewBox="0 0 24 24" stroke="#bebebf">
		<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
	</svg>
</a>
		<input
	class="basket-quantity__input"
	type="text"
	size="3"
	id="QUANTITY_INPUT_<?=$arItem['ID']?>"
	name="QUANTITY_INPUT_<?=$arItem['ID']?>"
	maxlength="18"
	style="max-width: 50px"
	value="<?=$arItem['QUANTITY']?>"
	onchange="updateQuantity('QUANTITY_INPUT_<?=$arItem['ID']?>', '<?=$arItem['ID']?>', <?=$ratio?>, <?=$useFloatQuantityJS?>)"
>
<a class="basket-quantity__btn" href="javascript:void(0);" onclick="setQuantity(<?=$arItem['ID']?>, <?=$arItem['MEASURE_RATIO']?>, 'up', <?=$useFloatQuantityJS?>);">
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#bebebf">
		<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
	</svg>
</a>
</div>
<?endif;?>
		<input type="hidden" id="QUANTITY_<?=$arItem['ID']?>" name="QUANTITY_<?=$arItem['ID']?>" value="<?=$arItem['QUANTITY']?>" />
	</td>
	<td class="table-delayed__cell">
		<div class="table-delayed__cell-container">
			<span class="table-delayed__cell-desc">Сумма: </span>
			<b id="sum_<?=$arItem['ID']?>"><?=$arItem['SUM_FULL_PRICE_FORMATED']?></b>
		</div>
	</td>
<?if ($bDelayColumn || $bDeleteColumn): ?>
			<td class="table-delayed__cell table-delayed__cell_links">
				<ul class="table-delayed__links-list">
					<li class="table-delayed__links-item">
						<a class="table-delayed__links-link" title="Отложить товар" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['delay'])?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#bebebf">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
							</svg>
						</a>
					</li>
					<li class="table-delayed__links-item">
						<a class="table-delayed__links-link" title="Удалить товар" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['delete'])?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#bebebf">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
							</svg>
						</a>
					</li>
				</ul>
			</td>
<?endif;?>
		</tr>
<?endif;?>
<?endforeach;?>
			</tbody>
		</table>
	</div>
	<input type="hidden" id="column_headers" value="<?=htmlspecialcharsbx(implode($arHeaders, ','))?>" />
	<input type="hidden" id="offers_props" value="<?=htmlspecialcharsbx(implode($arParams['OFFERS_PROPS'], ','))?>" />
	<input type="hidden" id="action_var" value="<?=htmlspecialcharsbx($arParams['ACTION_VARIABLE'])?>" />
	<input type="hidden" id="quantity_float" value="<?=($arParams['QUANTITY_FLOAT'] == 'Y') ? 'Y' : 'N'?>" />
	<input type="hidden" id="price_vat_show_value" value="<?=($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y') ? 'Y' : 'N'?>" />
	<input type="hidden" id="hide_coupon" value="<?=($arParams['HIDE_COUPON'] == 'Y') ? 'Y' : 'N'?>" />
	<input type="hidden" id="use_prepayment" value="<?=($arParams['USE_PREPAYMENT'] == 'Y') ? 'Y' : 'N'?>" />
	<input type="hidden" id="auto_calculation" value="<?=($arParams['AUTO_CALCULATION'] == 'N') ? 'N' : 'Y'?>" />
	<div class="checkout-block">
		<?if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y'): ?>
			<div class="checkout-block__info">
				<span><?echo GetMessage('SALE_VAT_EXCLUDED') ?></span>
				<span id="allSum_wVAT_FORMATED"><?=$arResult['allSum_wVAT_FORMATED']?></span>
			</div>
			<?if (floatval($arResult['allVATSum']) > 0): ?>
				<div class="checkout-block__info">
					<span><?echo GetMessage('SALE_VAT') ?></span>
					<span id="allVATSum_FORMATED"><?=$arResult['allVATSum_FORMATED']?></span>
				</div>
			<?endif;?>
<?endif;?>
		<div class="checkout-block__info">
			<b class="fwb"><?=GetMessage('SALE_TOTAL')?></b>
			<b class="fwb" id="allSum_FORMATED"><?=str_replace(' ', '&nbsp;', $arResult['allSum_FORMATED'])?></b>
		</div>
		<a href="javascript:void(0)" onclick="checkOut();" class="checkout-block__link"><?=GetMessage('SALE_ORDER')?></a>
	</div>
</div>
<?
    else:
?>
<div id="basket_items_list" style="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? 'display:none' : '')?>">
	<table>
		<tbody>
			<tr>
				<td style="text-align:center">
					<div class=""><?=GetMessage('SALE_NO_ITEMS');?></div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<?
endif;