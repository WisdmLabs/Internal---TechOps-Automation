jQuery(document).ready(function($) {
    const gitForm = $('#techops-git-form');
    const repoUrlInput = $('#repo-url');
    const folderPathInput = $('#folder-path');
    const branchTagSelect = $('#branch-tag');
    const typeSelect = $('#install-type');
    const submitButton = $('#git-submit');
    const progressBar = $('#git-progress');
    const statusMessage = $('#git-status');
    const historyList = $('#git-history');

    // Initialize form
    function initForm() {
        // Hide progress bar initially
        progressBar.hide();
        
        // Load installation history
        loadInstallationHistory();
    }

    // Load installation history
    function loadInstallationHistory() {
        $.ajax({
            url: techopsGit.ajax_url,
            type: 'POST',
            data: {
                action: 'techops_get_git_history',
                nonce: techopsGit.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateHistoryList(response.data);
                }
            }
        });
    }

    // Update history list
    function updateHistoryList(history) {
        historyList.empty();
        
        if (history.length === 0) {
            historyList.append('<li class="no-history">No installation history available</li>');
            return;
        }

        history.forEach(function(item) {
            const listItem = $('<li>').addClass('history-item');
            const info = $('<div>').addClass('history-info');
            const actions = $('<div>').addClass('history-actions');

            info.append(`
                <span class="repo-name">${item.repo_name}</span>
                <span class="install-date">${item.install_date}</span>
                <span class="install-type">${item.type}</span>
            `);

            actions.append(`
                <button class="button refresh" data-repo="${item.repo_url}" data-path="${item.folder_path}">
                    Refresh
                </button>
                <button class="button delete" data-id="${item.id}">
                    Delete
                </button>
            `);

            listItem.append(info).append(actions);
            historyList.append(listItem);
        });
    }

    // Get repository branches and tags
    function getBranchesAndTags(repoUrl) {
        return $.ajax({
            url: techopsGit.rest_url + 'git/branches-tags',
            type: 'POST',
            headers: {
                'X-WP-Nonce': techopsGit.rest_nonce
            },
            data: {
                repo_url: repoUrl
            }
        });
    }

    // Update branch/tag select
    function updateBranchTagSelect(repoUrl) {
        branchTagSelect.prop('disabled', true);
        branchTagSelect.html('<option value="">Loading...</option>');

        getBranchesAndTags(repoUrl)
            .done(function(response) {
                branchTagSelect.empty();
                branchTagSelect.append('<option value="">Select branch or tag</option>');

                if (response.data.branches) {
                    branchTagSelect.append('<optgroup label="Branches">');
                    response.data.branches.forEach(function(branch) {
                        branchTagSelect.append(`<option value="${branch}">${branch}</option>`);
                    });
                }

                if (response.data.tags) {
                    branchTagSelect.append('<optgroup label="Tags">');
                    response.data.tags.forEach(function(tag) {
                        branchTagSelect.append(`<option value="${tag}">${tag}</option>`);
                    });
                }
            })
            .fail(function(error) {
                branchTagSelect.html('<option value="">Error loading branches/tags</option>');
                showError('Failed to load branches and tags: ' + error.responseJSON.message);
            })
            .always(function() {
                branchTagSelect.prop('disabled', false);
            });
    }

    // Show error message
    function showError(message) {
        statusMessage
            .removeClass('success')
            .addClass('error')
            .html(message)
            .show();
    }

    // Show success message
    function showSuccess(message) {
        statusMessage
            .removeClass('error')
            .addClass('success')
            .html(message)
            .show();
    }

    // Update progress
    function updateProgress(percent, message) {
        progressBar.find('.progress-bar').css('width', percent + '%');
        progressBar.find('.progress-text').text(message);
    }

    // Handle form submission
    gitForm.on('submit', function(e) {
        e.preventDefault();

        const repoUrl = repoUrlInput.val();
        const folderPath = folderPathInput.val();
        const branchTag = branchTagSelect.val();
        const type = typeSelect.val();

        if (!repoUrl || !folderPath) {
            showError('Repository URL and folder path are required');
            return;
        }

        // Disable form
        submitButton.prop('disabled', true);
        progressBar.show();
        statusMessage.hide();

        // Show initial progress
        updateProgress(0, 'Preparing to download...');

        // Submit the request
        $.ajax({
            url: techopsGit.rest_url + 'git/download',
            type: 'POST',
            headers: {
                'X-WP-Nonce': techopsGit.rest_nonce
            },
            data: {
                repo_url: repoUrl,
                folder_path: folderPath,
                branch_or_tag: branchTag,
                type: type
            },
            success: function(response) {
                updateProgress(100, 'Installation complete!');
                showSuccess(response.message);
                loadInstallationHistory();
            },
            error: function(xhr) {
                const error = xhr.responseJSON || { message: 'An unknown error occurred' };
                showError(error.message);
            },
            complete: function() {
                submitButton.prop('disabled', false);
                setTimeout(function() {
                    progressBar.hide();
                }, 2000);
            }
        });
    });

    // Handle repository URL change
    repoUrlInput.on('change', function() {
        const repoUrl = $(this).val();
        if (repoUrl) {
            updateBranchTagSelect(repoUrl);
        }
    });

    // Handle history actions
    historyList.on('click', '.refresh', function() {
        const button = $(this);
        const repoUrl = button.data('repo');
        const folderPath = button.data('path');

        repoUrlInput.val(repoUrl);
        folderPathInput.val(folderPath);
        updateBranchTagSelect(repoUrl);
    });

    historyList.on('click', '.delete', function() {
        const button = $(this);
        const id = button.data('id');

        if (confirm('Are you sure you want to delete this installation record?')) {
            $.ajax({
                url: techopsGit.ajax_url,
                type: 'POST',
                data: {
                    action: 'techops_delete_git_history',
                    id: id,
                    nonce: techopsGit.nonce
                },
                success: function(response) {
                    if (response.success) {
                        loadInstallationHistory();
                    } else {
                        showError('Failed to delete history record');
                    }
                }
            });
        }
    });

    // Initialize the form
    initForm();
}); 