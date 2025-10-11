/**
 * Use this script to add your feature for the dashboard section
 */

import './css/dashboard.scss';
import './elements/index.js'

const bodyElement = document.body;
const btnSlidebarToggler = document.getElementById('js-main-header-toggler');
const btnSwitcherToggler = document.getElementById('js-theme-switcher');
const btnCollapseToggler = document.getElementById('js-sidebar-collapse');

// Hide or show the sidebar (only on mobile devices)
// Sidebar toggle
const sidebarToggle = () => {
    bodyElement.classList.toggle('sidebar-open');
}

// Switch between day theme and the night theme
btnSlidebarToggler.addEventListener('click', sidebarToggle)
btnSwitcherToggler.addEventListener('click', () => {
    bodyElement.classList.toggle('day-theme');
})

// Collapse the sidebar
const sidebarCollapseToggle = () => {
    bodyElement.classList.toggle('sidebar-collapse');
}

btnCollapseToggler.addEventListener('click', sidebarCollapseToggle)