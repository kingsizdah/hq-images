<script>
(function () {
    document.addEventListener('click', function (event) {
        var button = event.target.closest('button[wire\\:click="logout"]');
        if (!button) {
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        window.location.href = '{{ route('zitadel.logout') }}';
    }, true);
})();
</script>
