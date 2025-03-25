/**
 * Public-facing JavaScript for the Construction Service Calculator
 *
 * Handles all frontend functionality including real-time calculations,
 * form submission, and user interactions.
 *
 * @since      1.0.0
 * @package    Construction_Service_Calculator
 */

(function($) {
    'use strict';

    // Store selected services data
    let selectedServices = [];
    
    // Store calculation results
    let calculationResults = {};

    /**
     * Initialize the calculator
     */
    function initCalculator() {
        // Initialize service selection
        initServiceSelection();
        
        // Initialize quantity inputs
        initQuantityInputs();
        
        // Initialize category tabs
        initCategoryTabs();
        
        // Initialize show more/less
        initShowMoreLess();
        
        // Initialize form steps
        initFormSteps();
        
        // Initialize form submission
        initFormSubmission();
        
        // Initialize print/save functionality
        initPrintSave();
    }

    /**
     * Initialize service selection
     */
    function initServiceSelection() {
        // Service item click (full card click)
        $('.csc-service-item').on('click', function(e) {
            // If clicking on the quantity input itself, don't handle the click
            if ($(e.target).is('.csc-service-quantity') || $(e.target).is('.csc-unit-symbol')) {
                return;
            }
            
            // Toggle service activation
            const $serviceItem = $(this);
            const serviceId = $serviceItem.data('service-id');
            const $quantityInput = $serviceItem.find('.csc-service-quantity');
            
            // If quantity is 0, set to 1 (activate), otherwise set to 0 (deactivate)
            const currentQuantity = parseFloat($quantityInput.val()) || 0;
            
            if (currentQuantity <= 0) {
                // Always use 1 when clicking to activate
                $quantityInput.val('1');
                toggleServiceSelection(serviceId, true);
            } else {
                $quantityInput.val('0');
                toggleServiceSelection(serviceId, false);
            }
        });
    }

    /**
     * Initialize quantity inputs
     */
    function initQuantityInputs() {
        // Quantity input change
        $('.csc-service-quantity').on('change', function() {
            const $input = $(this);
            const serviceId = $input.data('service-id');
            let quantity = parseFloat($input.val());
            
            // If invalid, default to 0
            if (isNaN(quantity)) {
                quantity = 0;
                $input.val('0');
            }
            
            // Check if quantity is greater than 0 to activate service
            if (quantity > 0) {
                toggleServiceSelection(serviceId, true);
            } else {
                toggleServiceSelection(serviceId, false);
            }
        });
        
        // Allow typing any value during input
        $('.csc-service-quantity').on('input', function() {
            const $input = $(this);
            const inputValue = $input.val();
            
            // Allow empty field during typing
            if (inputValue === '') {
                return;
            }
            
            // Otherwise, ensure it's numeric (only fix obvious errors)
            if (!/^[0-9]*\.?[0-9]*$/.test(inputValue)) {
                $input.val(inputValue.replace(/[^0-9.]/g, ''));
            }
            
            // Check if value is > 0 and toggle selection accordingly
            const quantity = parseFloat(inputValue) || 0;
            const serviceId = $input.data('service-id');
            
            if (quantity > 0) {
                // Only activate if not already active to avoid recalculating constantly
                const isActive = $(this).closest('.csc-service-item').hasClass('csc-selected');
                if (!isActive) {
                    toggleServiceSelection(serviceId, true);
                } else {
                    // Just update the quantity if already active
                    updateServiceQuantity(serviceId, quantity);
                }
            } else {
                toggleServiceSelection(serviceId, false);
            }
        });
        
        // Fix value on blur
        $('.csc-service-quantity').on('blur', function() {
            const $input = $(this);
            const serviceId = $input.data('service-id');
            let quantity = parseFloat($input.val());
            
            // If empty or invalid, default to 0
            if (isNaN(quantity) || $input.val() === '') {
                quantity = 0;
                $input.val('0');
                toggleServiceSelection(serviceId, false);
            } else if (quantity > 0) {
                // Check maximum if specified
                const max = parseFloat($input.data('max'));
                if (!isNaN(max) && quantity > max) {
                    quantity = max;
                    $input.val(quantity);
                }
                
                // Make sure service is selected and calculation updated
                toggleServiceSelection(serviceId, true);
            } else {
                // Ensure exact 0 display and deactivate service
                $input.val('0');
                toggleServiceSelection(serviceId, false);
            }
        });
    }

    /**
     * Initialize category tabs
     */
    function initCategoryTabs() {
        // Ensure only one category tab is active
        function ensureOnlyOneActiveTab() {
            // Check if multiple tabs are active (should not happen, but just in case)
            if ($('.csc-category-tab.active').length > 1) {
                // Keep only the first active tab
                $('.csc-category-tab.active:not(:first)').removeClass('active');
            }
            
            // Check if multiple content sections are active
            if ($('.csc-category-content.active').length > 1) {
                // Keep only the first active content
                $('.csc-category-content.active:not(:first)').removeClass('active');
            }
        }
        
        // Set first category as active if none is active
        if (!$('.csc-category-tab.active').length) {
            $('.csc-category-tab:first').addClass('active');
            $('.csc-category-content:first').addClass('active');
        } else {
            // Ensure only one is active
            ensureOnlyOneActiveTab();
        }
        
        // When on mobile, scroll active tab into view
        function scrollActiveCategoryIntoView() {
            if (window.innerWidth <= 767) {
                const activeTab = $('.csc-category-tab.active')[0];
                if (activeTab) {
                    // Scroll to tab with a slight offset to show it's part of a scrollable area
                    const tabsContainer = $('.csc-category-tabs')[0];
                    if (tabsContainer) {
                        tabsContainer.scrollLeft = activeTab.offsetLeft - 15;
                    }
                }
            }
        }
        
        // Scroll active tab into view on page load
        setTimeout(scrollActiveCategoryIntoView, 100);
        
        // Category tab click
        $('.csc-category-tab').on('click', function() {
            const categoryId = $(this).data('category');
            
            // Remove active class from all tabs and content
            $('.csc-category-tab').removeClass('active');
            $('.csc-category-content').removeClass('active');
            
            // Add active class to the clicked tab and its content
            $(this).addClass('active');
            $(`.csc-category-content[data-category="${categoryId}"]`).addClass('active');
            
            // Scroll active tab into view on mobile
            setTimeout(scrollActiveCategoryIntoView, 100);
        });
    }

    /**
     * Initialize show more/less functionality
     */
    function initShowMoreLess() {
        $('.csc-show-more').on('click', function() {
            const $showMoreBtn = $(this);
            const $container = $showMoreBtn.closest('.csc-category-content');
            
            // Check the current state of the button
            if ($showMoreBtn.text() === csc_vars.strings.show_less) {
                // We're currently showing extra items, so hide them
                const $itemsToHide = $container.find('.csc-service-item').slice(6);
                $itemsToHide.addClass('csc-hidden');
                $showMoreBtn.text(csc_vars.strings.show_more);
            } else {
                // We're currently hiding items, so show them
                const $hiddenItems = $container.find('.csc-service-item.csc-hidden');
                $hiddenItems.removeClass('csc-hidden');
                $showMoreBtn.text(csc_vars.strings.show_less);
            }
        });
    }

    /**
     * Initialize form steps
     */
    function initFormSteps() {
        // Go to next step
        $('.csc-next-step').on('click', function() {
            const $currentStep = $(this).closest('.csc-step');
            const $nextStep = $currentStep.next('.csc-step');
            
            $currentStep.removeClass('active');
            $nextStep.addClass('active');
            
            // Update progress indicators
            updateProgressIndicators();
            
            // Scroll to top of form
            $('html, body').animate({
                scrollTop: $('.csc-calculator-form').offset().top - 50
            }, 300);
        });
        
        // Go to previous step
        $('.csc-prev-step').on('click', function() {
            const $currentStep = $(this).closest('.csc-step');
            const $prevStep = $currentStep.prev('.csc-step');
            
            $currentStep.removeClass('active');
            $prevStep.addClass('active');
            
            // Update progress indicators
            updateProgressIndicators();
            
            // Scroll to top of form
            $('html, body').animate({
                scrollTop: $('.csc-calculator-form').offset().top - 50
            }, 300);
        });
        
        // Update progress indicators on load
        updateProgressIndicators();
    }

    /**
     * Update progress indicators
     */
    function updateProgressIndicators() {
        const totalSteps = $('.csc-step').length;
        const currentStepIndex = $('.csc-step.active').index('.csc-step') + 1;
        
        // Update progress bar
        const progressPercent = ((currentStepIndex - 1) / (totalSteps - 1)) * 100;
        $('.csc-progress-bar-inner').css('width', `${progressPercent}%`);
        
        // Update step numbers
        $('.csc-progress-step').removeClass('active completed');
        
        $('.csc-progress-step').each(function(index) {
            if (index + 1 < currentStepIndex) {
                $(this).addClass('completed');
            } else if (index + 1 === currentStepIndex) {
                $(this).addClass('active');
            }
        });
        
        // Update mobile progress indicator
        $('.csc-mobile-current-step').text(currentStepIndex);
        $('.csc-mobile-progress-dot').removeClass('active');
        $(`.csc-mobile-progress-dot[data-step="${currentStepIndex}"]`).addClass('active');
        
        // Show/hide previous button on first step
        if (currentStepIndex === 1) {
            $('.csc-prev-step').css('visibility', 'hidden');
        } else {
            $('.csc-prev-step').css('visibility', 'visible');
        }
    }

    /**
     * Initialize form submission
     */
    function initFormSubmission() {
        // Submit form
        $('.csc-calculator-form').on('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateForm()) {
                return;
            }
            
            // Get customer info
            const customerInfo = {
                name: $('#csc_customer_name').val(),
                email: $('#csc_customer_email').val(),
                phone: $('#csc_customer_phone').val(),
                message: $('#csc_customer_message').val()
            };
            
            // Show loading state
            const $submitBtn = $('.csc-submit-button');
            const originalBtnText = $submitBtn.text();
            $submitBtn.text(csc_vars.strings.submitting).prop('disabled', true);
            
            // Log for debugging
            console.log('Submitting inquiry:', {
                services: selectedServices,
                customer_info: customerInfo
            });
            
            // Submit inquiry via AJAX
            $.ajax({
                url: csc_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'csc_submit_inquiry',
                    nonce: csc_vars.nonce,
                    services: selectedServices,
                    customer_info: customerInfo
                },
                success: function(response) {
                    console.log('Submission response:', response);
                    
                    // Reset loading state
                    $submitBtn.text(originalBtnText).prop('disabled', false);
                    
                    if (response.success) {
                        // Show success message
                        showSuccessMessage(response.data.html_estimate);
                    } else {
                        // Show error message
                        showErrorMessage(response.data || csc_vars.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Submission error:', {xhr, status, error});
                    
                    // Reset loading state
                    $submitBtn.text(originalBtnText).prop('disabled', false);
                    
                    // Show error message
                    showErrorMessage(csc_vars.strings.error);
                }
            });
        });
    }

    /**
     * Initialize print/save functionality
     */
    function initPrintSave() {
        // Print button click
        $(document).on('click', '.csc-print-button', function() {
            window.print();
        });
        
        // Save as HTML button click
        $(document).on('click', '.csc-save-button', function() {
            const estimateHtml = $('.csc-estimate-result').html();
            const blob = new Blob([estimateHtml], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            
            a.href = url;
            a.download = 'construction-estimate.html';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    }

    /**
     * Toggle service selection
     *
     * @param {number} serviceId The service ID
     * @param {boolean} selected Whether to select or deselect the service
     */
    function toggleServiceSelection(serviceId, selected) {
        const $serviceItem = $(`.csc-service-item[data-service-id="${serviceId}"]`);
        
        if (selected) {
            // Add class to highlight the service item
            $serviceItem.addClass('csc-selected');
            
            // Add to selected services
            const existingIndex = selectedServices.findIndex(service => service.service_id === serviceId);
            if (existingIndex === -1) {
                // Get quantity
                const $quantityInput = $serviceItem.find('.csc-service-quantity');
                let quantity = parseFloat($quantityInput.val()) || 0;
                
                // Make sure quantity is valid (greater than 0)
                if (quantity <= 0) {
                    // Get minimum value (if specified, otherwise use 1)
                    quantity = parseFloat($quantityInput.data('min')) || 1;
                    $quantityInput.val(quantity);
                }
                
                // Add service to selection
                selectedServices.push({
                    service_id: serviceId,
                    quantity: quantity
                });
                
                // Calculate service cost
                calculateServiceCost(serviceId, quantity);
            }
        } else {
            // Remove highlight class
            $serviceItem.removeClass('csc-selected');
            
            // Remove from selected services
            removeServiceFromSelection(serviceId);
            
            // Clear the subtotal display
            $(`.csc-service-subtotal[data-service-id="${serviceId}"]`).empty();
        }
        
        // Update "Next" button status and selected count
        updateStep1Status();
    }

    /**
     * Update service quantity
     *
     * @param {number} serviceId The service ID
     * @param {number} quantity  The new quantity
     */
    function updateServiceQuantity(serviceId, quantity) {
        // Update quantity in selected services
        const existingIndex = selectedServices.findIndex(service => service.service_id === serviceId);
        
        if (existingIndex !== -1) {
            selectedServices[existingIndex].quantity = quantity;
            
            // Calculate service cost
            calculateServiceCost(serviceId, quantity);
        }
    }

    /**
     * Remove service from selection
     *
     * @param {number} serviceId The service ID
     */
    function removeServiceFromSelection(serviceId) {
        // Remove service from selection
        selectedServices = selectedServices.filter(service => service.service_id !== serviceId);
        
        // Update calculation results
        updateCalculationResults();
    }

    /**
     * Calculate service cost
     *
     * @param {number} serviceId The service ID
     * @param {number} quantity  The quantity
     */
    function calculateServiceCost(serviceId, quantity) {
        // Show loading state
        $(`.csc-service-subtotal[data-service-id="${serviceId}"]`).html('<span class="csc-loading">' + csc_vars.strings.calculating + '</span>');
        
        // Calculate via AJAX
        $.ajax({
            url: csc_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'csc_calculate',
                nonce: csc_vars.nonce,
                service_data: {
                    service_id: serviceId,
                    quantity: quantity
                }
            },
            success: function(response) {
                if (response.success) {
                    // Update service subtotal
                    $(`.csc-service-subtotal[data-service-id="${serviceId}"]`).text(response.data.subtotal_formatted);
                    
                    // Store calculation result
                    updateCalculationResults(response.data);
                } else {
                    // Show error
                    $(`.csc-service-subtotal[data-service-id="${serviceId}"]`).text('Error');
                }
            },
            error: function() {
                // Show error
                $(`.csc-service-subtotal[data-service-id="${serviceId}"]`).text('Error');
            }
        });
    }

    /**
     * Update calculation results
     *
     * @param {object} serviceResult The service calculation result
     */
    function updateCalculationResults(serviceResult) {
        // Calculate totals manually to avoid additional AJAX requests
        let subtotal = 0;
        let tax = 0;
        
        // If service result is provided, update it in the results
        if (serviceResult) {
            calculationResults[serviceResult.service_id] = serviceResult;
        }
        
        // Calculate totals based on currently selected services
        selectedServices.forEach(service => {
            if (calculationResults[service.service_id]) {
                subtotal += calculationResults[service.service_id].subtotal;
                tax += calculationResults[service.service_id].tax_amount;
            }
        });
        
        // Calculate grand total
        const total = subtotal + tax;
        
        // Format currency values
        const subtotalFormatted = formatCurrency(subtotal);
        const taxFormatted = formatCurrency(tax);
        const totalFormatted = formatCurrency(total);
        
        // Update summary values
        $('.csc-summary-subtotal').text(subtotalFormatted);
        $('.csc-summary-tax').text(taxFormatted);
        $('.csc-summary-total').text(totalFormatted);
        
        // Update selected service count
        updateSelectedCount();
        
        // Update review table in step 2
        updateReviewTable();
    }

    /**
     * Update step 1 status (enable/disable next button)
     */
    function updateStep1Status() {
        const $nextButton = $('.csc-step[data-step="1"] .csc-next-step');
        
        if (selectedServices.length > 0) {
            $nextButton.prop('disabled', false);
        } else {
            $nextButton.prop('disabled', true);
        }
        
        // Update selected service count
        updateSelectedCount();
    }

    /**
     * Update selected service count
     */
    function updateSelectedCount() {
        $('.csc-selected-count').text(selectedServices.length);
    }

    /**
     * Update review table in step 2
     */
    function updateReviewTable() {
        const $tbody = $('.csc-review-table tbody');
        
        // Clear existing rows
        $tbody.empty();
        
        // Add row for each selected service
        selectedServices.forEach(service => {
            if (calculationResults[service.service_id]) {
                const result = calculationResults[service.service_id];
                
                const row = `
                    <tr>
                        <td>${result.service_name}</td>
                        <td>${result.rate_formatted}</td>
                        <td>${result.quantity} ${result.unit_symbol}</td>
                        <td>${result.subtotal_formatted}</td>
                    </tr>
                `;
                
                $tbody.append(row);
            }
        });
    }

    /**
     * Format currency value
     *
     * @param {number} value The value to format
     * @return {string} The formatted currency string
     */
    function formatCurrency(value) {
        const formattedValue = value.toFixed(csc_vars.decimals);
        const parts = formattedValue.toString().split('.');
        
        // Format the integer part with thousand separator
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, csc_vars.thousand_separator);
        
        // Join with decimal separator
        const formatted = parts.join(csc_vars.decimal_separator);
        
        // Add currency symbol
        if (csc_vars.currency_position === 'before') {
            return csc_vars.currency_symbol + formatted;
        } else {
            return formatted + csc_vars.currency_symbol;
        }
    }

    /**
     * Validate the form
     *
     * @return {boolean} Whether the form is valid
     */
    function validateForm() {
        let isValid = true;
        let errorMessages = [];
        
        // Check if at least one service is selected
        if (selectedServices.length === 0) {
            errorMessages.push(csc_vars.strings.select_service);
            isValid = false;
        }
        
        // Validate required fields
        $('.csc-required').each(function() {
            const $field = $(this);
            const value = $field.val().trim();
            
            if (value === '') {
                $field.addClass('csc-error');
                // Add field-specific error message if needed
                errorMessages.push(csc_vars.strings.required_field);
                isValid = false;
            } else {
                $field.removeClass('csc-error');
            }
        });
        
        // Validate email format
        const $emailField = $('#csc_customer_email');
        if ($emailField.length && $emailField.val().trim() !== '') {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test($emailField.val().trim())) {
                $emailField.addClass('csc-error');
                errorMessages.push(csc_vars.strings.invalid_email);
                isValid = false;
            }
        }
        
        // Show the first error message if validation failed
        if (!isValid && errorMessages.length > 0) {
            showErrorMessage(errorMessages[0]);
            
            // Log all validation errors to console
            console.log('Form validation errors:', errorMessages);
        }
        
        return isValid;
    }

    /**
     * Show error message
     *
     * @param {string} message The error message
     */
    function showErrorMessage(message) {
        const $errorContainer = $('.csc-messages');
        
        $errorContainer.html(`<div class="csc-message csc-error-message">${message}</div>`);
        
        // Scroll to error message
        $('html, body').animate({
            scrollTop: $errorContainer.offset().top - 50
        }, 300);
        
        // Hide message after 5 seconds
        setTimeout(function() {
            $errorContainer.empty();
        }, 5000);
    }

    /**
     * Show success message
     *
     * @param {string} estimateHtml The HTML estimate
     */
    function showSuccessMessage(estimateHtml) {
        // Hide form
        $('.csc-calculator-form').hide();
        
        // Show success message
        const $successContainer = $('.csc-success-container');
        
        $successContainer.html(`
            <div class="csc-message csc-success-message">
                ${csc_vars.strings.success}
            </div>
            <div class="csc-estimate-result">
                ${estimateHtml}
            </div>
            <div class="csc-estimate-actions">
                <button type="button" class="csc-print-button">
                    <span class="csc-icon csc-icon-print"></span>
                    ${csc_vars.strings.print}
                </button>
                <button type="button" class="csc-save-button">
                    <span class="csc-icon csc-icon-save"></span>
                    ${csc_vars.strings.save_html}
                </button>
            </div>
        `);
        
        $successContainer.show();
        
        // Scroll to success message
        $('html, body').animate({
            scrollTop: $successContainer.offset().top - 50
        }, 300);
    }

    // Initialize on document ready
    $(document).ready(function() {
        initCalculator();
    });

})(jQuery);