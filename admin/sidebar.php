<?php
// Get the current path
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!-- Sidebar Navigation -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h1 class="sidebar-logo">Hem <span>&</span> Ivy</h1>
        <p class="sidebar-tagline">Admin Portal</p>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item <?= $currentPath === '/admin/dashboard' ? 'active' : '' ?>">
                <a href="/admin/dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9"></rect>
                        <rect x="14" y="3" width="7" height="5"></rect>
                        <rect x="14" y="12" width="7" height="9"></rect>
                        <rect x="3" y="16" width="7" height="5"></rect>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li class="nav-item <?= $currentPath === '/admin/auctions' ? 'active' : '' ?>">
                <a href="/admin/auctions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                    Auctions
                </a>
            </li>
            <li class="nav-item <?= $currentPath === '/admin/categories' ? 'active' : '' ?>">
                <a href="/admin/categories">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    Categories
                </a>
            </li>
            <li class="nav-item <?= $currentPath === '/admin/users' ? 'active' : '' ?>">
                <a href="/admin/users">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Users
                </a>
            </li>
            <li class="nav-item <?= $currentPath === '/admin/reports' ? 'active' : '' ?>">
                <a href="/admin/reports">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Reports
                </a>
            </li>
            <li class="nav-item <?= $currentPath === '/admin/settings' ? 'active' : '' ?>">
                <a href="/admin/settings">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Settings
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="/logout" class="logout-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Sign Out
        </a>
    </div>
</aside>

<style>
    /* Sidebar Styles */
    .admin-sidebar {
        width: 250px;
        background-color: var(--imperial-purple);
        color: white;
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        box-shadow: var(--shadow-md);
        z-index: 100;
    }

    .sidebar-header {
        padding: 25px 20px;
        text-align: center;
        background-color: var(--imperial-purple-dark);
        border-bottom: 4px solid var(--aged-gold);
    }

    .sidebar-logo {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: 1px;
    }

    .sidebar-logo span {
        color: var(--aged-gold);
        font-style: italic;
    }

    .sidebar-tagline {
        font-size: 12px;
        opacity: 0.8;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .sidebar-nav {
        flex: 1;
        padding: 20px 0;
        overflow-y: auto;
    }

    .sidebar-nav ul {
        list-style-type: none;
    }

    .nav-item {
        margin-bottom: 2px;
    }

    .nav-item a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .nav-item a svg {
        margin-right: 12px;
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .nav-item a:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .nav-item a:hover svg {
        opacity: 1;
    }

    .nav-item.active a {
        background-color: rgba(255, 255, 255, 0.15);
        color: white;
        border-left: 3px solid var(--aged-gold);
    }

    .nav-item.active a svg {
        opacity: 1;
    }

    .sidebar-footer {
        padding: 15px 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 15px;
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: var(--radius-md);
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .logout-btn svg {
        margin-right: 8px;
    }

    .logout-btn:hover {
        background-color: rgba(0, 0, 0, 0.3);
    }
</style>