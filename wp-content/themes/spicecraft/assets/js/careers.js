/**
 * SpiceCraft - Careers & Recruitment Interactions
 *
 * Provides:
 * 1. Instant client-side job filtering & search with live count updates
 * 2. File upload drag-and-drop & metadata inspection (size, extension)
 * 3. Secure AJAX candidate application submission with double-submit locking
 * 4. Accessible error/success feedback
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initJobFiltering();
        initResumeDropzone();
        initApplicationForm();
        initScrollTriggers();
    });

    /**
     * 1. Instant Client-Side Job Filtering
     */
    function initJobFiltering() {
        var searchInput = document.getElementById('sc-job-search');
        var deptSelect  = document.getElementById('sc-job-dept');
        var locSelect   = document.getElementById('sc-job-loc');
        var typeSelect  = document.getElementById('sc-job-type');
        var jobGrid     = document.getElementById('sc-jobs-listings');
        var countText   = document.getElementById('sc-jobs-count-text');

        if (!jobGrid) {
            return;
        }

        var cards = jobGrid.querySelectorAll('.sc-job-card');
        var emptyState = document.getElementById('sc-careers-no-results');

        function applyFilters() {
            var searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
            var deptVal   = deptSelect ? deptSelect.value.toLowerCase().trim() : '';
            var locVal    = locSelect ? locSelect.value.toLowerCase().trim() : '';
            var typeVal   = typeSelect ? typeSelect.value.toLowerCase().trim() : '';

            var visibleCount = 0;

            cards.forEach(function (card) {
                var cardTitle = (card.querySelector('.sc-job-card__title') || {}).textContent || '';
                var cardExcerpt = (card.querySelector('.sc-job-card__excerpt') || {}).textContent || '';
                var cardSkills = (card.querySelector('.sc-job-card__skills') || {}).textContent || '';
                var allText = (cardTitle + ' ' + cardExcerpt + ' ' + cardSkills).toLowerCase();

                var cardDept = (card.getAttribute('data-department') || '').toLowerCase();
                var cardLoc  = (card.getAttribute('data-location') || '').toLowerCase();
                var cardType = (card.getAttribute('data-type') || '').toLowerCase();

                // Check criteria
                var matchesSearch = !searchVal || allText.indexOf(searchVal) !== -1;
                var matchesDept   = !deptVal || cardDept.indexOf(deptVal) !== -1;
                var matchesLoc    = !locVal || cardLoc.indexOf(locVal) !== -1;
                var matchesType   = !typeVal || cardType === typeVal;

                if (matchesSearch && matchesDept && matchesLoc && matchesType) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Handle empty state visibility
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            // Update live count banner
            if (countText) {
                if (visibleCount === 0) {
                    countText.textContent = 'No positions match your selected criteria.';
                } else if (visibleCount === 1) {
                    countText.textContent = 'Showing 1 open position';
                } else {
                    countText.textContent = 'Showing ' + visibleCount + ' open positions';
                }
            }
        }

        // Event listeners for interactive instant filtering
        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }
        if (deptSelect) {
            deptSelect.addEventListener('change', applyFilters);
        }
        if (locSelect) {
            locSelect.addEventListener('change', applyFilters);
        }
        if (typeSelect) {
            typeSelect.addEventListener('change', applyFilters);
        }

        // Run once on load to establish counts
        applyFilters();
    }

    /**
     * 2. Resume File Dropzone & Validation
     */
    function initResumeDropzone() {
        var dropzone    = document.getElementById('sc-resume-dropzone');
        var fileInput   = document.getElementById('sc_app_resume');
        var selectedBox = document.getElementById('sc-resume-selected');
        var nameDisplay = document.getElementById('sc-file-name-display');
        var sizeDisplay = document.getElementById('sc-file-size-display');
        var clearBtn    = document.getElementById('sc-file-clear-btn');
        var promptBox   = dropzone ? dropzone.querySelector('.sc-file-dropzone__inner') : null;

        if (!dropzone || !fileInput) {
            return;
        }

        var allowedExtensions = ['pdf', 'doc', 'docx'];
        var maxBytes = 10 * 1024 * 1024; // 10MB

        function handleFile(file) {
            if (!file) return;

            var ext = file.name.split('.').pop().toLowerCase();

            // Validate Extension
            if (allowedExtensions.indexOf(ext) === -1) {
                alert('Invalid file format. Only PDF, DOC, and DOCX documents are accepted.');
                fileInput.value = '';
                return;
            }

            // Validate Size
            if (file.size > maxBytes) {
                alert('The selected file exceeds the 10MB maximum limit. Please upload a smaller file.');
                fileInput.value = '';
                return;
            }

            // Format size
            var formattedSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            if (file.size < 1024 * 1024) {
                formattedSize = (file.size / 1024).toFixed(0) + ' KB';
            }

            if (nameDisplay) nameDisplay.textContent = file.name;
            if (sizeDisplay) sizeDisplay.textContent = formattedSize;

            if (promptBox) promptBox.style.display = 'none';
            if (selectedBox) selectedBox.style.display = 'flex';
        }

        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files[0]) {
                handleFile(fileInput.files[0]);
            }
        });

        // Drag & Drop visual highlights
        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('is-dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('is-dragover');
            }, false);
        });

        dropzone.addEventListener('drop', function (e) {
            var dt = e.dataTransfer;
            var files = dt.files;
            if (files && files[0]) {
                fileInput.files = files;
                handleFile(files[0]);
            }
        });

        // Clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                fileInput.value = '';
                if (selectedBox) selectedBox.style.display = 'none';
                if (promptBox) promptBox.style.display = '';
            });
        }
    }

    /**
     * 3. Accessible AJAX Application Form Submission
     */
    function initApplicationForm() {
        var form        = document.getElementById('sc-careers-application-form');
        var submitBtn   = document.getElementById('sc-app-submit-btn');
        var errorBox    = document.getElementById('sc-app-error-box');
        var errorMsg    = document.getElementById('sc-app-error-message');
        var successBox  = document.getElementById('sc-app-success-box');
        var successMsg  = document.getElementById('sc-app-success-message');
        var container   = document.getElementById('sc-application-container');

        if (!form || !submitBtn) {
            return;
        }

        var btnText    = submitBtn.querySelector('.sc-btn__text');
        var btnSpinner = submitBtn.querySelector('.sc-btn__spinner');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Reset alerts
            if (errorBox) errorBox.style.display = 'none';

            // Client-side quick validation
            var fullName = (form.querySelector('input[name="full_name"]') || {}).value || '';
            var email    = (form.querySelector('input[name="email"]') || {}).value || '';
            var phone    = (form.querySelector('input[name="phone"]') || {}).value || '';
            var cover    = (form.querySelector('textarea[name="cover_message"]') || {}).value || '';
            var consent  = (form.querySelector('input[name="consent"]') || {}).checked;
            var fileInp  = form.querySelector('input[name="resume"]');

            if (!fullName.trim() || fullName.trim().length < 2) {
                showError('Please provide your full legal name.');
                form.querySelector('input[name="full_name"]').focus();
                return;
            }

            if (!email.trim() || email.indexOf('@') === -1 || email.indexOf('.') === -1) {
                showError('Please provide a valid email address.');
                form.querySelector('input[name="email"]').focus();
                return;
            }

            if (!phone.trim() || phone.replace(/\D/g, '').length < 8) {
                showError('Please provide a valid telephone number.');
                form.querySelector('input[name="phone"]').focus();
                return;
            }

            if (!fileInp || !fileInp.files || !fileInp.files[0]) {
                showError('Please attach your CV/resume document (PDF, DOC, or DOCX).');
                return;
            }

            if (!cover.trim() || cover.trim().length < 10) {
                showError('Please enter a brief cover message introducing yourself.');
                form.querySelector('textarea[name="cover_message"]').focus();
                return;
            }

            if (!consent) {
                showError('You must agree to the recruitment communication consent.');
                form.querySelector('input[name="consent"]').focus();
                return;
            }

            // Lock submit button to prevent double-click
            submitBtn.disabled = true;
            if (btnSpinner) btnSpinner.style.display = 'inline-block';
            if (btnText) btnText.textContent = 'Submitting Application...';

            var formData = new FormData(form);

            // Determine endpoint
            var endpoint = (window.spicecraftCareersConfig && window.spicecraftCareersConfig.ajaxUrl)
                ? window.spicecraftCareersConfig.ajaxUrl
                : ((window.spicecraftConfig && window.spicecraftConfig.ajaxUrl) ? window.spicecraftConfig.ajaxUrl : form.getAttribute('action'));

            fetch(endpoint, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (res) {
                if (res && res.success) {
                    // Success!
                    form.style.display = 'none';
                    if (successBox) {
                        successBox.style.display = 'flex';
                        if (res.data && res.data.message && successMsg) {
                            successMsg.innerHTML = res.data.message;
                        }
                        successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    var msg = (res && res.data && res.data.message) ? res.data.message : 'Something went wrong while submitting your application. Please try again.';
                    showError(msg);
                    unlockButton();
                }
            })
            .catch(function (err) {
                console.error('Careers application submission error:', err);
                showError('Something went wrong while submitting your application. Please try again.');
                unlockButton();
            });
        });

        function showError(msg) {
            if (errorBox && errorMsg) {
                errorMsg.textContent = msg;
                errorBox.style.display = 'flex';
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                alert(msg);
            }
        }

        function unlockButton() {
            submitBtn.disabled = false;
            if (btnSpinner) btnSpinner.style.display = 'none';
            if (btnText) btnText.textContent = 'Submit Application';
        }
    }

    /**
     * 4. Smooth Scrolling Triggers
     */
    function initScrollTriggers() {
        var triggers = document.querySelectorAll('a[href^="#"]');
        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                var targetId = trigger.getAttribute('href');
                if (targetId && targetId !== '#') {
                    var targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        e.preventDefault();
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        // Update focus for accessibility
                        targetEl.setAttribute('tabindex', '-1');
                        targetEl.focus();
                    }
                }
            });
        });
    }

})();
