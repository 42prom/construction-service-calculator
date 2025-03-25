/**
 * Admin JavaScript for the Construction Service Calculator
 *
 * Handles all admin functionality including service management,
 * SVG uploads, and form interactions.
 *
 * @since      1.0.0
 * @package    Construction_Service_Calculator
 */

(function($) {
    'use strict';

    /**
     * Initialize admin functionality
     */
    function initAdmin() {
        // Initialize service icon uploader
        initServiceIconUploader();
        
        // Initialize service custom fields
        initServiceCustomFields();
        
        // Initialize category management
        initCategoryManagement();
        
        // Initialize unit management
        initUnitManagement();
        
        // Initialize color picker
        initColorPicker();
        
        // Initialize theme preview
        initThemePreview();
        
        // Initialize submissions management
        initSubmissionsManagement();
        
        // Initialize analytics period change
        initAnalyticsPeriodChange();
    }

    /**
     * Initialize service icon uploader
     */
    function initServiceIconUploader() {
        // Check if we're on the service edit page
        if (!$('.csc-icon-uploader').length) {
            return;
        }
        
        // Upload SVG icon button
        $('.csc-upload-icon-button').on('click', function(e) {
            e.preventDefault();
            
            // Create media uploader
            const fileFrame = wp.media({
                title: csc_admin_vars.svg_upload_title || 'Upload SVG Icon',
                button: {
                    text: csc_admin_vars.svg_upload_button || 'Use this icon'
                },
                multiple: false,
                library: {
                    type: 'image/svg+xml'
                }
            });
            
            // When an image is selected in the media frame
            fileFrame.on('select', function() {
                const attachment = fileFrame.state().get('selection').first().toJSON();
                
                // Check if it's an SVG
                if (attachment.subtype !== 'svg+xml') {
                    alert(csc_admin_vars.svg_invalid_error);
                    return;
                }
                
                // Update icon preview and hidden input
                updateIconPreview(attachment.url);
                $('#csc_icon_url').val(attachment.url);
                
                // Show remove button
                $('.csc-remove-icon-button').show();
            });
            
            // Open the media uploader
            fileFrame.open();
        });
        
        // Select from library button
        $('.csc-select-library-icon-button').on('click', function(e) {
            e.preventDefault();
            
            // Show SVG library modal
            showSvgLibraryModal();
        });
        
        // Remove icon button
        $('.csc-remove-icon-button').on('click', function(e) {
            e.preventDefault();
            
            // Clear icon preview and hidden input
            $('.csc-svg-preview').empty();
            $('.csc-svg-preview').hide();
            $('.csc-no-icon').show();
            $('#csc_icon_url').val('');
            
            // Hide remove button
            $(this).hide();
        });
    }

    /**
     * Update icon preview
     * 
     * @param {string} iconUrl The icon URL
     */
    function updateIconPreview(iconUrl) {
        // Show loading indicator
        $('.csc-svg-preview').html('<p>Loading...</p>');
        $('.csc-svg-preview').show();
        $('.csc-no-icon').hide();
        
        // Load the SVG content
        $.get(iconUrl, function(data) {
            // Convert SVG data to string
            const svgString = new XMLSerializer().serializeToString(data.documentElement);
            
            // Update preview
            $('.csc-svg-preview').html(svgString);
        })
        .fail(function() {
            $('.csc-svg-preview').html('<p>Error loading SVG</p>');
        });
    }

    /**
     * Show SVG library modal
     */
    function showSvgLibraryModal() {
        // Create modal container if it doesn't exist
        if (!$('#csc-svg-library-modal').length) {
            $('body').append(`
                <div id="csc-svg-library-modal" class="csc-modal">
                    <div class="csc-modal-content">
                        <div class="csc-modal-header">
                            <h3>${csc_admin_vars.svg_library_title || 'SVG Icon Library'}</h3>
                            <button type="button" class="csc-modal-close">&times;</button>
                        </div>
                        <div class="csc-modal-body">
                            <div class="csc-svg-library-container">
                                <div class="csc-svg-library-loading">Loading icons...</div>
                                <div class="csc-svg-library-grid"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            // Close modal on click outside or on close button
            $('#csc-svg-library-modal').on('click', function(e) {
                if ($(e.target).is('#csc-svg-library-modal') || $(e.target).is('.csc-modal-close')) {
                    $('#csc-svg-library-modal').hide();
                }
            });
        }
        
        // Show the modal
        $('#csc-svg-library-modal').show();
        
        // Load SVG library via AJAX
        $.ajax({
            url: csc_admin_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'csc_get_svg_library',
                nonce: csc_admin_vars.nonce
            },
            success: function(response) {
                if (response.success && response.data.icons) {
                    // Clear loading indicator
                    $('.csc-svg-library-loading').hide();
                    
                    // Clear existing grid
                    $('.csc-svg-library-grid').empty();
                    
                    // Add icons to grid
                    const icons = response.data.icons;
                    if (icons.length === 0) {
                        $('.csc-svg-library-grid').html('<p>No icons found in the library.</p>');
                    } else {
                        icons.forEach(function(icon) {
                            const iconItem = $(`
                                <div class="csc-svg-library-item" data-url="${icon.file_url}">
                                    <div class="csc-svg-library-preview">
                                        ${icon.svg_content}
                                    </div>
                                    <div class="csc-svg-library-name">${icon.filename}</div>
                                </div>
                            `);
                            
                            // Add click handler
                            iconItem.on('click', function() {
                                const iconUrl = $(this).data('url');
                                
                                // Update icon preview and hidden input
                                updateIconPreview(iconUrl);
                                $('#csc_icon_url').val(iconUrl);
                                
                                // Show remove button
                                $('.csc-remove-icon-button').show();
                                
                                // Close modal
                                $('#csc-svg-library-modal').hide();
                            });
                            
                            $('.csc-svg-library-grid').append(iconItem);
                        });
                    }
                } else {
                    $('.csc-svg-library-container').html('<p>Error loading SVG library.</p>');
                }
            },
            error: function() {
                $('.csc-svg-library-container').html('<p>Error loading SVG library.</p>');
            }
        });
    }

    /**
     * Initialize service custom fields
     */
    function initServiceCustomFields() {
        // Check if we're on the service edit page
        if (!$('.csc-custom-fields').length) {
            return;
        }
        
        // Add custom field button
        $('.csc-add-field-button').on('click', function() {
            // Get the template
            const template = document.querySelector('#csc-custom-field-template');
            if (!template) return;
            
            // Clone the template content
            const newRow = template.content.cloneNode(true);
            
            // Remove the "no custom fields" row if it exists
            $('.csc-no-custom-fields').remove();
            
            // Add the new row to the table
            $('.csc-custom-fields-table tbody').append(newRow);
            
            // Focus on the first input in the new row
            $('.csc-custom-fields-table tbody tr:last-child input:first').focus();
        });
        
        // Remove custom field button (delegated event)
        $('.csc-custom-fields-table').on('click', '.csc-remove-field-button', function() {
            $(this).closest('tr').remove();
            
            // If no rows left, add the "no custom fields" row
            if ($('.csc-custom-field-row').length === 0) {
                $('.csc-custom-fields-table tbody').html(`
                    <tr class="csc-no-custom-fields">
                        <td colspan="3">${csc_admin_vars.no_custom_fields || 'No custom fields added yet.'}</td>
                    </tr>
                `);
            }
        });
    }

    /**
     * Initialize category management
     */
    function initCategoryManagement() {
        // Check if we're on the categories page
        if (!$('.csc-categories-container').length) {
            return;
        }
        
        // Add category button
        $('.csc-add-category-button').on('click', function() {
            // Show the add category form
            $('.csc-add-category-form').slideDown();
            
            // Focus on the first input
            $('#csc_new_category_key').focus();
        });
        
        // Remove category button (delegated event)
        $('.csc-categories-table').on('click', '.csc-remove-category-button', function() {
            if (confirm(csc_admin_vars.confirm_delete)) {
                $(this).closest('tr').remove();
            }
        });
        
        // Auto-generate key from name
        $('#csc_new_category_name').on('input', function() {
            const name = $(this).val();
            const key = name.toLowerCase()
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
                
            $('#csc_new_category_key').val(key);
        });
    }

    /**
     * Initialize unit management
     */
    function initUnitManagement() {
        // Check if we're on the units management section
        if (!$('.csc-units-container').length) {
            return;
        }
        
        // Add unit button
        $('.csc-add-unit-button').on('click', function() {
            // Show the add unit form
            $('.csc-add-unit-form').slideDown();
            
            // Focus on the first input
            $('#csc_new_unit_key').focus();
        });
        
        // Cancel add unit button
        $('.csc-cancel-add-unit').on('click', function() {
            // Hide the form and clear inputs
            $('.csc-add-unit-form').slideUp();
            $('#csc_new_unit_key').val('');
            $('#csc_new_unit_name').val('');
            $('#csc_new_unit_symbol').val('');
        });
        
        // Remove unit button (delegated event)
        $('.csc-units-table').on('click', '.csc-remove-unit-button', function() {
            if (confirm(csc_admin_vars.confirm_delete)) {
                $(this).closest('tr').remove();
            }
        });
        
        // Auto-generate key from name
        $('#csc_new_unit_name').on('input', function() {
            const name = $(this).val();
            const key = name.toLowerCase()
                .replace(/[^a-z0-9]/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '');
                
            $('#csc_new_unit_key').val(key);
        });
    }

    /**
     * Initialize color picker
     */
    function initColorPicker() {
        // Check if we have color picker elements
        if ($('.csc-color-picker').length) {
            $('.csc-color-picker').wpColorPicker();
        }
    }

    /**
     * Initialize theme preview
     */
    function initThemePreview() {
        // Check if we're on the settings page
        if (!$('#csc_theme').length) {
            return;
        }
        
        // Theme select change
        $('#csc_theme').on('change', function() {
            const selectedTheme = $(this).val();
            
            // Update preview
            $('.csc-theme-sample').removeClass('active');
            $(`.csc-theme-sample.${selectedTheme}`).addClass('active');
        });
    }

    /**
     * Initialize submissions management
     */
    function initSubmissionsManagement() {
        // Check if we're on the submissions page
        if (!$('.csc-submissions-container').length) {
            return;
        }
        
        // Bulk action checkboxes
        $('#csc_select_all').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.csc-submission-checkbox').prop('checked', isChecked);
        });
        
        // Reply to customer button
        $('.csc-reply-button').on('click', function() {
            $('.csc-reply-form').slideDown();
        });
        
        // Cancel reply button
        $('.csc-cancel-reply-button').on('click', function() {
            $('.csc-reply-form').slideUp();
        });
        
        // Send reply button
        $('.csc-send-reply-button').on('click', function() {
            const $button = $(this);
            const originalText = $button.text();
            const formData = {
                to: $('input[name="csc_reply_to"]').val(),
                subject: $('input[name="csc_reply_subject"]').val(),
                message: $('textarea[name="csc_reply_message"]').val()
            };
            
            // Validate form
            if (!formData.subject || !formData.message) {
                alert(csc_admin_vars.reply_required_fields || 'Please fill in all required fields.');
                return;
            }
            
            // Show loading state
            $button.text(csc_admin_vars.sending || 'Sending...').prop('disabled', true);
            
            // Submit the form
            $('form#post').submit();
        });
    }

    /**
     * Initialize analytics period change
     */
    function initAnalyticsPeriodChange() {
        // Check if we're on the tools page with analytics
        if (!$('.csc-analytics-period-select').length) {
            return;
        }
        
        // Period select change
        $('.csc-analytics-period-select').on('change', function() {
            const $form = $(this).closest('form');
            $form.submit();
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        initAdmin();
    });

})(jQuery);