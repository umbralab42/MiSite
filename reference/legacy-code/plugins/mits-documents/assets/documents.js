/* global document, window */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('mits-doc-modal');
        if (!modal) return;

        var overlay  = modal.querySelector('.mits-doc-modal__overlay');
        var closeBtn = modal.querySelector('.mits-doc-modal__close');
        var titleEl  = modal.querySelector('.mits-doc-modal__title');
        var frame    = modal.querySelector('.mits-doc-modal__frame');

        function openModal(url, title) {
            frame.src            = url;
            titleEl.textContent  = title || '';
            modal.hidden         = false;
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeModal() {
            modal.hidden         = true;
            frame.src            = '';
            document.body.style.overflow = '';
        }

        // Open via "Переглянути" buttons
        document.querySelectorAll('.mits-doc-btn--view').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openModal(btn.dataset.file, btn.dataset.title);
            });
        });

        // Close via overlay click
        overlay.addEventListener('click', closeModal);

        // Close via × button
        closeBtn.addEventListener('click', closeModal);

        // Close via Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    });
}());
