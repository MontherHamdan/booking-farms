/**
 * Common JavaScript functions for admin panel
 */

/**
 * Show a confirmation dialog before performing delete operations
 * @param {string} formId - The ID of the form to submit after confirmation
 * @param {object} options - Optional configuration parameters
 * @param {string} options.title - Dialog title
 * @param {string} options.text - Dialog message
 * @param {string} options.icon - Dialog icon (warning, error, success, info, question)
 * @param {string} options.confirmButtonText - Text for confirm button
 * @param {string} options.cancelButtonText - Text for cancel button
 * @param {string} options.confirmButtonColor - Color for confirm button
 * @param {string} options.cancelButtonColor - Color for cancel button
 */
function confirmAction(formId, options = {}) {
    // Default options
    const defaults = {
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    };

    // Merge provided options with defaults
    const settings = { ...defaults, ...options };

    // Show SweetAlert confirmation
    Swal.fire({
        title: settings.title,
        text: settings.text,
        icon: settings.icon,
        showCancelButton: settings.showCancelButton,
        confirmButtonColor: settings.confirmButtonColor,
        cancelButtonColor: settings.cancelButtonColor,
        confirmButtonText: settings.confirmButtonText,
        cancelButtonText: settings.cancelButtonText
    }).then((result) => {
        if (result.isConfirmed) {
            // If confirmed, submit the form
            document.getElementById(formId).submit();
        }
    });
}

/**
 * Confirm delete operation with default delete settings
 * @param {string} formId - The ID of the form to submit after confirmation
 */
function confirmDelete(formId) {
    confirmAction(formId);
}