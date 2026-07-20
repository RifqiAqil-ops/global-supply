<footer class="footer py-3 mt-auto border-top" style="background-color: var(--color-bg-sidebar); border-color: var(--color-border) !important;">
    <div class="container-fluid px-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small text-muted">
            <div>
                &copy; {{ date('Y') }} <strong>Waypoint</strong>. Global Supply Chain Intelligence.
            </div>
            <div>
                Version 1.0.0 (Laravel {{ app()->version() }})
            </div>
        </div>
    </div>
</footer>
