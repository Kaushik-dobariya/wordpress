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

    // 11. About Us: Add Core Value
    $('#sc-add-about-value-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-about-values-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 10px; width: 100%; align-items: center; margin-bottom: 8px;">' +
            '<input type="text" name="spicecraft_about_settings[values][items][' + idx + '][icon]" value="" placeholder="Icon name (e.g. shield, leaf, award)" style="width: 150px;" />' +
            '<input type="text" name="spicecraft_about_settings[values][items][' + idx + '][title]" value="" placeholder="Value Title (e.g. Uncompromising Purity)" class="regular-text" style="flex-grow: 1;" />' +
            '<input type="number" name="spicecraft_about_settings[values][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 70px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<div>' +
            '<textarea name="spicecraft_about_settings[values][items][' + idx + '][description]" placeholder="Explanation of this core principle..." rows="2" style="width: 100%;"></textarea>' +
            '</div>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 12. About Us: Add Quality Point
    $('#sc-add-about-quality-point-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-about-quality-points-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 10px; margin-bottom: 6px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; width: 100%; margin-bottom: 6px;">' +
            '<input type="text" name="spicecraft_about_settings[quality][points][' + idx + '][title]" value="" placeholder="Protocol Title (e.g. Cold Grinding Retention)" class="regular-text" style="flex-grow: 1;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<textarea name="spicecraft_about_settings[quality][points][' + idx + '][text]" placeholder="Point details..." rows="2" style="width: 100%;"></textarea>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 13. About Us: Add Sourcing Point
    $('#sc-add-about-sourcing-point-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-about-sourcing-points-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 10px; margin-bottom: 6px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; width: 100%; margin-bottom: 6px;">' +
            '<input type="text" name="spicecraft_about_settings[sourcing][points][' + idx + '][title]" value="" placeholder="Highlight (e.g. Direct Farm Contracts)" class="regular-text" style="flex-grow: 1;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<textarea name="spicecraft_about_settings[sourcing][points][' + idx + '][text]" placeholder="Point details..." rows="2" style="width: 100%;"></textarea>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 14. About Us: Add Manufacturing Highlight
    $('#sc-add-about-mfg-highlight-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-about-mfg-highlights-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 10px; margin-bottom: 6px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; width: 100%; margin-bottom: 6px;">' +
            '<input type="text" name="spicecraft_about_settings[manufacturing][highlights][' + idx + '][title]" value="" placeholder="Highlight (e.g. Cryogenic Low-Temp Milling)" class="regular-text" style="flex-grow: 1;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<textarea name="spicecraft_about_settings[manufacturing][highlights][' + idx + '][text]" placeholder="Short explanation..." rows="2" style="width: 100%;"></textarea>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 15. About Us: Add Statistic Row
    $('#sc-add-about-stat-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-about-stats-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<input type="text" name="spicecraft_about_settings[statistics][items][' + idx + '][value]" value="" placeholder="Value (e.g. 25, 100)" style="width: 100px;" />' +
            '<input type="text" name="spicecraft_about_settings[statistics][items][' + idx + '][suffix]" value="" placeholder="Suffix (+, %, K)" style="width: 80px;" />' +
            '<input type="text" name="spicecraft_about_settings[statistics][items][' + idx + '][label]" value="" placeholder="Label (e.g. Years of Heritage)" class="regular-text" style="flex-grow: 1;" />' +
            '<input type="number" name="spicecraft_about_settings[statistics][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 60px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 16. About Us: Add Milestone Row
    $('#sc-add-about-milestone-btn').on('click', function (e) {
        e.preventDefault();
        var container = $('#sc-about-milestones-container');
        var idx = container.children().length;
        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">' +
            '<input type="text" name="spicecraft_about_settings[milestones][items][' + idx + '][date_label]" value="" placeholder="Year / Label (e.g. 1998, The Beginning)" style="width: 180px;" />' +
            '<input type="text" name="spicecraft_about_settings[milestones][items][' + idx + '][title]" value="" placeholder="Milestone Title" class="regular-text" style="flex-grow: 1;" />' +
            '<input type="number" name="spicecraft_about_settings[milestones][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 60px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<div>' +
            '<textarea name="spicecraft_about_settings[milestones][items][' + idx + '][description]" placeholder="Milestone details..." rows="2" style="width: 100%;"></textarea>' +
            '</div>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 17. Shared CMS: Media Uploader Component
    $(document).on('click', '.sc-media-upload-btn', function (e) {
        e.preventDefault();
        var wrap = $(this).closest('.sc-media-uploader-wrap');
        var input = wrap.find('.sc-media-id-input');
        var preview = wrap.find('.sc-media-preview-box');
        var removeBtn = wrap.find('.sc-media-remove-btn');
        var mimeFilter = $(this).data('mime') || '';

        var mediaConfig = {
            title: 'Select or Upload Media',
            button: { text: 'Use This File' },
            multiple: false
        };

        if (mimeFilter) {
            mediaConfig.library = { type: mimeFilter };
        }

        var frame = wp.media(mediaConfig);

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            input.val(attachment.id);

            if (attachment.type === 'image') {
                var url = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : (attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                preview.html('<img src="' + url + '" alt="" style="max-width: 100%; height: auto; display: block; border-radius: 2px;" />');
            } else {
                var fileName = attachment.filename || attachment.title || 'Document';
                preview.html('<div style="display: flex; align-items: center; gap: 8px; text-align: left; padding: 4px;">' +
                    '<span class="dashicons dashicons-media-document" style="font-size: 28px; width: 28px; height: 28px; color: #d63638;"></span>' +
                    '<div style="overflow: hidden; text-overflow: ellipsis; max-width: 180px;">' +
                    '<strong style="display: block; font-size: 12px; color: #2c3338; word-break: break-all;">' + $('<div/>').text(fileName).html() + '</strong>' +
                    '<span style="font-size: 11px; color: #646970;">Document Attached</span>' +
                    '</div></div>');
            }
            removeBtn.show();
        });

        frame.open();
    });

    $(document).on('click', '.sc-media-remove-btn', function (e) {
        e.preventDefault();
        var wrap = $(this).closest('.sc-media-uploader-wrap');
        wrap.find('.sc-media-id-input').val('');
        wrap.find('.sc-media-preview-box').html('<span style="color: #8c8f94; font-size: 12px;">No file selected</span>');
        $(this).hide();
    });

    // 18. Shared CMS: Gallery Manager Component
    $(document).on('click', '.sc-gallery-add-btn', function (e) {
        e.preventDefault();
        var wrap = $(this).closest('.sc-gallery-uploader-wrap');
        var grid = wrap.find('.sc-gallery-grid');
        var inputName = $(this).data('input-name');
        var clearBtn = wrap.find('.sc-gallery-clear-btn');

        var frame = wp.media({
            title: 'Select or Upload Gallery Images',
            button: { text: 'Add to Gallery' },
            multiple: 'add'
        });

        frame.on('select', function () {
            var selection = frame.state().get('selection');
            grid.find('.sc-gallery-empty').remove();

            selection.each(function (att) {
                var item = att.toJSON();
                var thumb = (item.sizes && item.sizes.thumbnail) ? item.sizes.thumbnail.url : item.url;
                var el = $('<div class="sc-gallery-item" data-id="' + item.id + '" style="position: relative; width: 80px; height: 80px; border: 1px solid #ccd0d4; border-radius: 4px; overflow: hidden; background: #fff;">' +
                    '<img src="' + thumb + '" alt="" style="width: 100%; height: 100%; object-fit: cover;" />' +
                    '<button type="button" class="sc-gallery-item-remove" style="position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.7); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; line-height: 16px; text-align: center; cursor: pointer; font-size: 11px;">&times;</button>' +
                    '<input type="hidden" name="' + inputName + '[]" value="' + item.id + '" />' +
                    '</div>');
                grid.append(el);
            });
            clearBtn.show();
        });

        frame.open();
    });

    $(document).on('click', '.sc-gallery-item-remove', function (e) {
        e.preventDefault();
        var wrap = $(this).closest('.sc-gallery-uploader-wrap');
        $(this).closest('.sc-gallery-item').remove();
        if (wrap.find('.sc-gallery-item').length === 0) {
            wrap.find('.sc-gallery-grid').html('<p class="sc-gallery-empty" style="color: #8c8f94; font-size: 13px; margin: auto;">No gallery images selected.</p>');
            wrap.find('.sc-gallery-clear-btn').hide();
        }
    });

    $(document).on('click', '.sc-gallery-clear-btn', function (e) {
        e.preventDefault();
        var wrap = $(this).closest('.sc-gallery-uploader-wrap');
        wrap.find('.sc-gallery-grid').html('<p class="sc-gallery-empty" style="color: #8c8f94; font-size: 13px; margin: auto;">No gallery images selected.</p>');
        $(this).hide();
    });

    // 19. Shared CMS: Add Spec Row to Equipment
    $(document).on('click', '.sc-add-spec-btn', function (e) {
        e.preventDefault();
        var parentIdx = $(this).data('parent-idx');
        var optName = $(this).data('option-name');
        var container = $(this).siblings('.sc-spec-rows-container');
        var specIdx = container.children().length;

        var row = $('<div class="sc-spec-row" style="display: flex; gap: 6px; margin-bottom: 4px;">' +
            '<input type="text" name="' + optName + '[equipment][items][' + parentIdx + '][specs][' + specIdx + '][label]" value="" placeholder="Label (e.g. Material)" style="width: 45%;" />' +
            '<input type="text" name="' + optName + '[equipment][items][' + parentIdx + '][specs][' + specIdx + '][value]" value="" placeholder="Value (e.g. SS 316)" style="width: 45%;" />' +
            '<button type="button" class="button sc-remove-spec-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    $(document).on('click', '.sc-remove-spec-btn', function (e) {
        e.preventDefault();
        $(this).closest('.sc-spec-row').remove();
    });

    // 20. Shared CMS: Add Statistic Item (Manufacturing & Quality)
    $(document).on('click', '#sc-add-stat-btn, #sc-add-quality-stat-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var containerId = $(this).data('container') || 'sc-stat-rows';
        var container = $('#' + containerId);
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<input type="text" name="' + optName + '[statistics][items][' + idx + '][value]" value="" placeholder="Value (e.g. 50)" style="width: 90px;" />' +
            '<input type="text" name="' + optName + '[statistics][items][' + idx + '][suffix]" value="" placeholder="Suffix (+, %, MT)" style="width: 80px;" />' +
            '<input type="text" name="' + optName + '[statistics][items][' + idx + '][label]" value="" placeholder="Metric Label" class="regular-text" style="flex-grow: 1;" />' +
            '<input type="text" name="' + optName + '[statistics][items][' + idx + '][description]" value="" placeholder="Short Note (Optional)" style="flex-grow: 1;" />' +
            '<input type="number" name="' + optName + '[statistics][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 60px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 21. Shared CMS: Add Process Step
    $(document).on('click', '#sc-add-process-btn, #sc-add-qc-process-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var containerId = $(this).data('container') || 'sc-process-rows';
        var container = $('#' + containerId);
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 10px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; margin-bottom: 8px;">' +
            '<input type="text" name="' + optName + '[process][items][' + idx + '][step_number]" value="0' + (idx + 1) + '" placeholder="Step" style="width: 80px;" />' +
            '<input type="text" name="' + optName + '[process][items][' + idx + '][title]" value="" placeholder="Stage Title" class="regular-text" style="flex-grow: 1;" />' +
            '<input type="number" name="' + optName + '[process][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 70px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<textarea name="' + optName + '[process][items][' + idx + '][description]" placeholder="Detailed description of this stage..." rows="2" style="width: 100%; margin-bottom: 8px;"></textarea>' +
            '<div style="display: flex; align-items: center; gap: 12px;">' +
            '<label style="font-size: 12px; color: #50575e;">Stage Image:</label>' +
            '<div class="sc-media-uploader-wrap">' +
            '<input type="hidden" name="' + optName + '[process][items][' + idx + '][image_id]" class="sc-media-id-input" value="" />' +
            '<div class="sc-media-preview-box" style="margin-bottom: 4px; width: 60px; height: 40px; background: #f0f0f1; border: 1px dashed #c3c4c7; display: flex; align-items: center; justify-content: center;"><span style="font-size: 10px; color: #888;">None</span></div>' +
            '<button type="button" class="button button-secondary sc-media-upload-btn">Select Image</button>' +
            '</div>' +
            '</div>' +
            '</div>');
        container.append(row);
        row.find('input[name*="[title]"]').focus();
    });

    // 22. Shared CMS: Add Equipment Item
    $(document).on('click', '#sc-add-equipment-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var container = $('#sc-equipment-rows');
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row sc-card sc-equipment-card" style="padding: 14px; margin-bottom: 12px; background: #fdfdfd; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; margin-bottom: 8px;">' +
            '<input type="text" name="' + optName + '[equipment][items][' + idx + '][name]" value="" placeholder="Equipment / Machine Name" class="large-text" style="flex-grow: 1;" />' +
            '<input type="number" name="' + optName + '[equipment][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 70px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<textarea name="' + optName + '[equipment][items][' + idx + '][description]" placeholder="Operational description..." rows="2" style="width: 100%; margin-bottom: 8px;"></textarea>' +
            '<div style="display: flex; gap: 16px; flex-wrap: wrap;">' +
            '<div style="flex: 2; min-width: 280px;">' +
            '<p style="margin: 0 0 4px; font-weight: 600; font-size: 12px;">Technical Specifications</p>' +
            '<div class="sc-spec-rows-container" data-parent-idx="' + idx + '"></div>' +
            '<button type="button" class="button button-small sc-add-spec-btn" data-option-name="' + optName + '" data-parent-idx="' + idx + '">+ Add Spec Row</button>' +
            '</div>' +
            '</div>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 23. Shared CMS: Add Sourcing Region
    $(document).on('click', '#sc-add-region-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var container = $('#sc-region-rows');
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 10px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">' +
            '<input type="text" name="' + optName + '[regions][items][' + idx + '][name]" value="" placeholder="Region (e.g. Salem)" style="flex: 2; min-width: 140px;" />' +
            '<input type="text" name="' + optName + '[regions][items][' + idx + '][state]" value="" placeholder="State/Province" style="flex: 1; min-width: 110px;" />' +
            '<input type="text" name="' + optName + '[regions][items][' + idx + '][country]" value="" placeholder="Country" style="flex: 1; min-width: 90px;" />' +
            '<input type="text" name="' + optName + '[regions][items][' + idx + '][ingredient]" value="" placeholder="Spice / Crop" style="flex: 1.5; min-width: 120px;" />' +
            '<input type="number" name="' + optName + '[regions][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 60px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<textarea name="' + optName + '[regions][items][' + idx + '][description]" placeholder="Geographic/agro-climatic characteristics..." rows="2" style="width: 100%;"></textarea>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 24. Quality: Add Principle
    $(document).on('click', '#sc-add-principle-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var container = $('#sc-principles-rows');
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<input type="text" name="' + optName + '[principles][items][' + idx + '][title]" value="" placeholder="Principle Title" class="regular-text" style="flex: 2;" />' +
            '<input type="text" name="' + optName + '[principles][items][' + idx + '][description]" value="" placeholder="Explanation..." style="flex: 3;" />' +
            '<input type="text" name="' + optName + '[principles][items][' + idx + '][icon]" value="" placeholder="Icon" style="width: 80px;" />' +
            '<input type="number" name="' + optName + '[principles][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 60px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 25. Quality: Add Testing Item
    $(document).on('click', '#sc-add-testing-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var container = $('#sc-testing-rows');
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row sc-card" style="padding: 12px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<div style="display: flex; gap: 8px; margin-bottom: 6px;">' +
            '<input type="text" name="' + optName + '[testing][items][' + idx + '][name]" value="" placeholder="Test Name" class="regular-text" style="flex: 2;" />' +
            '<input type="text" name="' + optName + '[testing][items][' + idx + '][method]" value="" placeholder="Method (e.g. HPLC)" style="flex: 1.5;" />' +
            '<input type="text" name="' + optName + '[testing][items][' + idx + '][standard]" value="" placeholder="Standard" style="flex: 1.5;" />' +
            '<input type="number" name="' + optName + '[testing][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 60px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>' +
            '<textarea name="' + optName + '[testing][items][' + idx + '][description]" placeholder="Significance of test..." rows="2" style="width: 100%;"></textarea>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 26. Quality: Add Sourcing Highlight
    $(document).on('click', '#sc-add-sourcing-hl-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var container = $('#sc-sourcing-highlights');
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row" style="display: flex; gap: 8px; margin-bottom: 6px;">' +
            '<input type="text" name="' + optName + '[sourcing][highlights][' + idx + '][title]" value="" placeholder="Pillar Title" style="flex: 1;" />' +
            '<input type="text" name="' + optName + '[sourcing][highlights][' + idx + '][description]" value="" placeholder="Short detail..." style="flex: 2;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 27. Quality: Add Raw Material / Supplier Standard
    $(document).on('click', '#sc-add-raw-m-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var container = $('#sc-raw-materials-rows');
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<input type="text" name="' + optName + '[raw_materials][items][' + idx + '][title]" value="" placeholder="Standard Title" style="flex: 2;" />' +
            '<input type="text" name="' + optName + '[raw_materials][items][' + idx + '][description]" value="" placeholder="Criteria..." style="flex: 3;" />' +
            '<input type="number" name="' + optName + '[raw_materials][items][' + idx + '][order]" value="10" placeholder="Order" style="width: 60px;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 28. Quality: Add Traceability Step
    $(document).on('click', '#sc-add-trace-step-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var container = $('#sc-traceability-steps');
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px; background: #f9f9f9; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">' +
            '<input type="text" name="' + optName + '[traceability][steps][' + idx + '][step_number]" value="0' + (idx + 1) + '" placeholder="01" style="width: 50px;" />' +
            '<input type="text" name="' + optName + '[traceability][steps][' + idx + '][title]" value="" placeholder="Checkpoint" style="flex: 2;" />' +
            '<input type="text" name="' + optName + '[traceability][steps][' + idx + '][description]" value="" placeholder="Record keeping..." style="flex: 3;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });

    // 29. Quality: Add Food Safety Practice
    $(document).on('click', '#sc-add-safety-practice-btn', function (e) {
        e.preventDefault();
        var optName = $(this).data('option-name');
        var container = $('#sc-safety-practices');
        var idx = container.children().length;

        var row = $('<div class="sc-repeatable-row" style="display: flex; gap: 8px; margin-bottom: 6px;">' +
            '<input type="text" name="' + optName + '[food_safety][practices][' + idx + '][title]" value="" placeholder="Practice Title" style="flex: 1;" />' +
            '<input type="text" name="' + optName + '[food_safety][practices][' + idx + '][description]" value="" placeholder="Protocol description..." style="flex: 2;" />' +
            '<button type="button" class="button sc-remove-row-btn">&times;</button>' +
            '</div>');
        container.append(row);
        row.find('input:first').focus();
    });
});

