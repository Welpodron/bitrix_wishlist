<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
    }

    /** @var array $arParams */
    /** @var array $arResult */
    /** @var array $arUrls */
    /** @var array $arHeaders */

    $bPriceType = false;
    $bDelayColumn = false;
    $bDeleteColumn = false;
    $bWeightColumn = false;
    $bPropsColumn = false;
?>
<div id="basket_items_delayed" class="bx_ordercart_order_table_container" style="<?=($_REQUEST['wishlist'] && $delayCount > 0 ? '' : 'display:none')?>">
	<?
        // echo '<pre>';
        // Количество товара добалвенного в список желаемого print_r($arResult['GRID']['ROWS'][116648]['QUANTITY']);
        // тип Количества товара добалвенного в список желаемого (штуки, кило и тд) print_r($arResult['GRID']['ROWS'][116648]['MEASURE_TEXT']);
        // Имя товара добалвенного в список желаемого print_r($arResult['GRID']['ROWS'][116648]['NAME']);
        // ссылка на товар в каталоге добалвенного в список желаемого print_r($arResult['GRID']['ROWS'][116648]['DETAIL_PAGE_URL']);
        // стоимость одной единицы товара с учетом скидки и отформатированная print_r($arResult['GRID']['ROWS'][116648]['FULL_PRICE_FORMATED'])
        // стоимость всех единиц товара с учетом скидки и выбранного количества и отформатированная print_r($arResult['GRID']['ROWS'][116648]['SUM_FULL_PRICE_FORMATED'])
        // детальная картинка (ее урл) $arResult['GRID']['ROWS'][116648]['DETAIL_PICTURE_SRC']
        // детальная картинка (ее айди) $arResult['GRID']['ROWS'][116648]['DETAIL_PICTURE']
        // превью картинка (ее урл) $arResult['GRID']['ROWS'][116648]['PREVIEW_PICTURE_SRC']
        // превью картинка (ее айди) $arResult['GRID']['ROWS'][116648]['PREVIEW_PICTURE']
        // print_r($arResult['GRID']['ROWS']);
        // echo '</pre>';
    ?>
	<table id="delayed_items" class="table-delayed">
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
			<?foreach ($arResult['GRID']['ROWS'] as $arItem): ?>
<?if ($arItem['DELAY'] == 'Y' && $arItem['CAN_BUY'] == 'Y'): ?>
						<tr id="<?=$arItem['ID']?>" class="table-delayed__row">
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
									<span><?=$arItem['FULL_PRICE_FORMATED']?></span>
								</div>
							</td>
							<td class="table-delayed__cell">
								<div class="table-delayed__cell-container">
									<span class="table-delayed__cell-desc">Количество: </span>
									<span><?=$arItem['QUANTITY'] . ' ' . $arItem['MEASURE_TEXT']?></span>
								</div>
							</td>
							<td class="table-delayed__cell">
								<div class="table-delayed__cell-container">
									<span class="table-delayed__cell-desc">Сумма: </span>
									<b><?=$arItem['SUM_FULL_PRICE_FORMATED']?></b>
								</div>
							</td>
							<td class="table-delayed__cell table-delayed__cell_links">
								<ul class="table-delayed__links-list">
									<li class="table-delayed__links-item">
										<a class="table-delayed__links-link" title="Добавить к заказу" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['add'])?>">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#bebebf">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
											</svg>
										</a>
									</li>
									<li class="table-delayed__links-item">
										<a class="table-delayed__links-link" title="Удалить" href="<?=str_replace('#ID#', $arItem['ID'], $arUrls['delete'])?>">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#bebebf">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
											</svg>
										</a>
									</li>
								</ul>
							</td>
						</tr>
				<?endif;?>
<?endforeach;?>
		</tbody>
</table>
</div>