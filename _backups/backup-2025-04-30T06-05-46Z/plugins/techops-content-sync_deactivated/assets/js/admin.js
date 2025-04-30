jQuery(document).ready(function($) {
    $('#git-repo-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const $statusMessage = $('#status-message');
        
        // Disable submit button and show loading state
        $submitButton.prop('disabled', true).text('Processing...');
        $statusMessage.html('<div class="notice notice-info"><p>Processing your request...</p></div>');
        
        // Get form data
        const formData = new FormData($form[0]);
        formData.append('action', 'techops_git_download');
        formData.append('techops_git_nonce', $('#techops_git_nonce').val());
        
        // Send AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $statusMessage.html(
                        '<div class="notice notice-success">' +
                        '<p>' + response.data.message + '</p>' +
                        '</div>'
                    );
                } else {
                    $statusMessage.html(
                        '<div class="notice notice-error">' +
                        '<p>Error: ' + response.data.message + '</p>' +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                $statusMessage.html(
                    '<div class="notice notice-error">' +
                    '<p>Error: ' + error + '</p>' +
                    '</div>'
                );
            },
            complete: function() {
                // Re-enable submit button
                $submitButton.prop('disabled', false).text('Download and Install');
            }
        });
    });

    // Logs functionality
    function refreshLogs() {
        $.ajax({
            url: techopsContentSync.restUrl + 'techops-content-sync/v1/logs',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', techopsContentSync.nonce);
            },
            success: function(response) {
                const logsContainer = $('.logs-container');
                logsContainer.empty();

                if (response.logs && response.logs.length > 0) {
                    response.logs.forEach(function(log) {
                        logsContainer.append($('<div class="log-entry">').text(log));
                    });
                } else {
                    logsContainer.append($('<p class="no-logs">').text('No logs available.'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching logs:', error);
                $('.logs-container').html('<p class="no-logs">Error loading logs. Please try again.</p>');
            }
        });
    }

    function clearLogs() {
        $.ajax({
            url: techopsContentSync.restUrl + 'techops-content-sync/v1/logs',
            method: 'DELETE',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', techopsContentSync.nonce);
            },
            success: function() {
                refreshLogs();
            },
            error: function(xhr, status, error) {
                console.error('Error clearing logs:', error);
                alert('Failed to clear logs. Please try again.');
            }
        });
    }

    // Event handlers for logs section
    $('#refresh-logs').on('click', refreshLogs);
    $('#clear-logs').on('click', clearLogs);

    // Initial logs load
    refreshLogs();
}); 