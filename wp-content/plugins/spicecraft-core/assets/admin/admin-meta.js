/**
 * SpiceCraft Core - Admin Meta Box Interactions
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
});
