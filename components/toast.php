<!-- Toast Notification Component -->
<div id="toast-container" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

<style>
    .toast-enter {
        transform: translateX(100%);
        opacity: 0;
    }

    .toast-enter-active {
        transform: translateX(0);
        opacity: 1;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .toast-leave-active {
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s ease-in;
    }
</style>

<script>
    /**
     * Show a toast notification
     * @param {string} message - The message to display
     * @param {string} type - 'success' or 'error' (default: 'success')
     * @param {number} duration - Duration in ms (default: 3000)
     */
    function showToast(message, type = 'success', duration = 3000) {
        const container = document.getElementById('toast-container');

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `
            pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium min-w-[300px] max-w-sm toast-enter
            ${type === 'success'
                ? 'bg-white border-green-100 text-green-800'
                : 'bg-white border-red-100 text-red-800'}
        `;

        // Icon
        const icon = type === 'success'
            ? '<svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            : '<svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

        toast.innerHTML = `
            ${icon}
            <div class="flex-1">${message}</div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-enter-active');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('toast-enter-active');
            toast.classList.add('toast-leave-active');

            toast.addEventListener('transitionend', () => {
                toast.remove();
            });
        }, duration);
    }
</script>