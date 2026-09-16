/**
 * SpiceCraft Core - Admin Meta Box & Homepage CMS Interactions
 */
jQuery(document).ready(function ($) {
    'use strict';

    // 1. Meta Box Tabs Switching
    $('.sc-metabox-tab-btn').on('click', function (e) {
        e.preventDefault();
        var targetId = $(this).data('tab');

        $('.sc-metabox-tab-btn').removeClass('is-active');
        $(this).addClass('is-active');

        $('.sc-metabox-panel').hide();
        $('#' + targetId).show();
    });

    // 2. Add Highlight
    $('#sc-add-highlight-btn').on('click', function (e) {
        e.preventDefault();
        var newRow = $('<div class="sc-repeatable-row">' +
            '<span class="dashicons dashicons-menu sc-drag-handle"></span>' +
            '<input type="text" name="_sc_highlights[]" value="" class="widefat" placeholder="e.g. 100% Pure & Natural" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        $('#sc-highlights-container').append(newRow);
        newRow.find('input').focus();
    });

    // 3. Add Specification Row
    $('#sc-add-spec-btn').on('click', function (e) {
        e.preventDefault();
        var rowCount = $('#sc-specs-table tbody tr').length;
        var newRow = $('<tr>' +
            '<td><input type="text" name="_sc_specifications[' + rowCount + '][label]" value="" class="widefat" placeholder="e.g. Moisture Content" /></td>' +
            '<td><input type="text" name="_sc_specifications[' + rowCount + '][value]" value="" class="widefat" placeholder="e.g. Max 10%" /></td>' +
            '<td style="text-align: center;"><button type="button" class="button sc-remove-row-btn">&times;</button></td>' +
            '</tr>');
        $('#sc-specs-table tbody').append(newRow);
        newRow.find('input:first').focus();
    });

    // 4. Add Nutrient Row
    $('#sc-add-nutrient-btn').on('click', function (e) {
        e.preventDefault();
        var rowCount = $('#sc-nutrition-table tbody tr').length;
        var newRow = $('<tr>' +
            '<td><input type="text" name="_sc_nutrition_data[' + rowCount + '][nutrient]" value="" class="widefat" placeholder="e.g. Energy" /></td>' +
            '<td><input type="text" name="_sc_nutrition_data[' + rowCount + '][value]" value="" class="widefat" placeholder="e.g. 350" /></td>' +
            '<td><input type="text" name="_sc_nutrition_data[' + rowCount + '][unit]" value="" class="widefat" placeholder="kcal, g, mg" /></td>' +
            '<td style="text-align: center;"><button type="button" class="button sc-remove-row-btn">&times;</button></td>' +
            '</tr>');
        $('#sc-nutrition-table tbody').append(newRow);
        newRow.find('input:first').focus();
    });

    // 5. Pre-fill Standard Spice Nutrients
    $('#sc-load-std-nutrition-btn').on('click', function (e) {
        e.preventDefault();
        var stdNutrients = [
            { name: 'Energy', unit: 'kcal' },
            { name: 'Protein', unit: 'g' },
            { name: 'Carbohydrates', unit: 'g' },
            { name: 'Total Sugars', unit: 'g' },
            { name: 'Total Fat', unit: 'g' },
            { name: 'Saturated Fat', unit: 'g' },
            { name: 'Trans Fat', unit: 'g' },
            { name: 'Dietary Fiber', unit: 'g' },
            { name: 'Sodium', unit: 'mg' }
        ];

        var tbody = $('#sc-nutrition-table tbody');
        if (tbody.find('tr').length > 0) {
            if (!confirm('Add standard nutrients to the existing table?')) {
                return;
            }
        }

        var startIndex = tbody.find('tr').length;
        stdNutrients.forEach(function (nut, idx) {
            var rowIdx = startIndex + idx;
            var row = $('<tr>' +
                '<td><input type="text" name="_sc_nutrition_data[' + rowIdx + '][nutrient]" value="' + nut.name + '" class="widefat" /></td>' +
                '<td><input type="text" name="_sc_nutrition_data[' + rowIdx + '][value]" value="" class="widefat" placeholder="0" /></td>' +
                '<td><input type="text" name="_sc_nutrition_data[' + rowIdx + '][unit]" value="' + nut.unit + '" class="widefat" /></td>' +
                '<td style="text-align: center;"><button type="button" class="button sc-remove-row-btn">&times;</button></td>' +
                '</tr>');
            tbody.append(row);
        });
    });

    // 6. Universal Remove Row Event
    $(document).on('click', '.sc-remove-row-btn', function (e) {
        e.preventDefault();
        var row = $(this).closest('.sc-repeatable-row, tr');
        row.fadeOut(150, function () {
            row.remove();
        });
    });

    // 7. WordPress Media Library Frame for Homepage CMS
    $(document).on('click', '.sc-media-select-btn', function (e) {
        e.preventDefault();
        var box = $(this).closest('.sc-media-uploader-box');
        var input = box.find('.sc-media-id-input');
        var img = box.find('.sc-media-preview-img');
        var icon = box.find('.sc-media-placeholder-icon');
        var removeBtn = box.find('.sc-media-remove-btn');

        var frame = wp.media({
            title: 'Select or Upload Image',
            button: { text: 'Use This Image' },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            input.val(attachment.id);
            var url = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
            img.attr('src', url).show();
            icon.hide();
            removeBtn.show();
        });

        frame.open();
    });

    $(document).on('click', '.sc-media-remove-btn', function (e) {
        e.preventDefault();
        var box = $(this).closest('.sc-media-uploader-box');
        box.find('.sc-media-id-input').val('');
        box.find('.sc-media-preview-img').attr('src', '').hide();
        box.find('.sc-media-placeholder-icon').show();
        $(this).hide();
    });

    // 8. Add Why Choose Us Item
    $('#sc-add-wcu-item-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-wcu-items-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-bottom: 8px;">' +
            '<input type="text" name="spicecraft_homepage_settings[why_choose_us][items][' + idx + '][icon]" value="" placeholder="Icon (e.g. leaf, shield, award)" style="width: 140px;" />' +
            '<input type="text" name="spicecraft_homepage_settings[why_choose_us][items][' + idx + '][title]" value="" placeholder="Feature Title" class="regular-text" style="flex-grow: 1;" />' +
            '<input type="number" name="spicecraft_homepage_settings[why_choose_us][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 70px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<div>' +
            '<textarea name="spicecraft_homepage_settings[why_choose_us][items][' + idx + '][description]" placeholder="Short explanation of differentiator" rows="2" style="width: 100%;"></textarea>' +
            '</div>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 9. Add Quality Point
    $('#sc-add-qs-point-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-qs-points-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 10px; margin-bottom: 6px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; width: 100%; margin-bottom: 6px;">' +
            '<input type="text" name="spicecraft_homepage_settings[quality_sourcing][points][' + idx + '][title]" value="" placeholder="Point Title (e.g. Origin Traceability)" class="regular-text" style="flex-grow: 1;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<textarea name="spicecraft_homepage_settings[quality_sourcing][points][' + idx + '][text]" placeholder="Short point details" rows="2" style="width: 100%;"></textarea>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 10. Add Manufacturing Metric Stat
    $('#sc-add-mfg-stat-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-mfg-stats-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row" style="display: flex; gap: 8px; margin-bottom: 6px;">' +
            '<input type="text" name="spicecraft_homepage_settings[manufacturing][stats][' + idx + '][label]" value="" placeholder="Metric Label (e.g. Processing Lines)" class="regular-text" />' +
            '<input type="text" name="spicecraft_homepage_settings[manufacturing][stats][' + idx + '][value]" value="" placeholder="Value (e.g. 4 Dedicated)" class="regular-text" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });
});
