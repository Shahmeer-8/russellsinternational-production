@php
    $timeoutMs = max(1, (int) config('admin.idle_timeout_minutes', 5)) * 60 * 1000;
@endphp

<script>
    (() => {
        const timeoutMs = @json($timeoutMs);
        const adminBasePath = '/admin';
        const loginPath = `${adminBasePath}/login`;
        const lockPath = `${adminBasePath}/lock`;

        if (!window.location.pathname.startsWith(adminBasePath) || window.location.pathname === loginPath) {
            return;
        }

        let timerId = null;

        const lockAdmin = () => {
            const intended = encodeURIComponent(window.location.href);
            window.location.assign(`${lockPath}?intended=${intended}`);
        };

        const resetTimer = () => {
            window.clearTimeout(timerId);
            timerId = window.setTimeout(lockAdmin, timeoutMs);
        };

        ['click', 'keydown', 'mousedown', 'mousemove', 'scroll', 'touchstart'].forEach((eventName) => {
            window.addEventListener(eventName, resetTimer, { passive: true });
        });

        resetTimer();
    })();
</script>
