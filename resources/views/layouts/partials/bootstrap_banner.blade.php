@if(!empty($isSystemInitializing))
<div class="alert alert-info border-0 rounded-0 text-center py-2 px-3 mb-0 font-monospace small d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 50%, #1e3a8a 100%); color: #ffffff; z-index: 1060;">
    <span class="spinner-border spinner-border-sm text-light" role="status"></span>
    <span><strong>Initial System Synchronization in Progress:</strong> Waypoint is automatically populating master datasets in the background...</span>
</div>
@endif
