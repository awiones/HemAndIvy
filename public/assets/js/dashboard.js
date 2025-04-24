document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle functionality
    const toggleSidebarBtn = document.createElement('button');
    toggleSidebarBtn.className = 'toggle-sidebar-btn';
    toggleSidebarBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    `;
    
    const adminHeader = document.querySelector('.admin-header');
    if (adminHeader) {
        adminHeader.insertBefore(toggleSidebarBtn, adminHeader.firstChild);
    }
    
    toggleSidebarBtn.addEventListener('click', function() {
        const sidebar = document.querySelector('.admin-sidebar');
        if (sidebar) sidebar.classList.toggle('show');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.admin-sidebar');
        if (!sidebar) return;
        if (
            window.innerWidth <= 576 && 
            !event.target.closest('.admin-sidebar') && 
            !event.target.closest('.toggle-sidebar-btn') &&
            sidebar.classList.contains('show')
        ) {
            sidebar.classList.remove('show');
        }
    });
    
    // Add styles for toggle button
    const style = document.createElement('style');
    style.textContent = `
        .toggle-sidebar-btn {
            display: none;
            background: none;
            border: none;
            color: var(--imperial-purple);
            cursor: pointer;
            padding: 5px;
            margin-right: 15px;
        }
        @media (max-width: 576px) {
            .toggle-sidebar-btn {
                display: block;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Add subtle animations to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Simple notification system
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            alert('Notification feature coming soon!');
        });
    }
});
