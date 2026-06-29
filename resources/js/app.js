import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebarMenu');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.add('show');
            overlay.classList.add('show');
        });
    }

    const closeSidebar = () => {
        if (sidebar) sidebar.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
    };

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Toggle menu-arrow rotations for collapsible menus
    const collapses = document.querySelectorAll('.sidebar .collapse');
    collapses.forEach(collapse => {
        const parentLink = document.querySelector(`[href="#${collapse.id}"]`);
        if (parentLink) {
            collapse.addEventListener('show.bs.collapse', () => {
                parentLink.classList.remove('collapsed');
            });
            collapse.addEventListener('hide.bs.collapse', () => {
                parentLink.classList.add('collapsed');
            });
        }
    });
});
