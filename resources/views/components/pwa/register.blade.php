<script>
    if ('serviceWorker' in navigator && (window.isSecureContext || ['localhost', '127.0.0.1'].includes(window.location.hostname))) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch((error) => {
                console.warn('Service worker registration failed:', error);
            });
        });
    }
</script>
