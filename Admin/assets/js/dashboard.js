// assets/js/dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    
    sidebarCollapse.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        content.classList.toggle('active');
    });

    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        themeIcon.className = newTheme === 'dark' ? 'bi bi-moon' : 'bi bi-sun';
        
        // Save theme preference
        localStorage.setItem('theme', newTheme);
    });

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    themeIcon.className = savedTheme === 'dark' ? 'bi bi-moon' : 'bi bi-sun';

    // Auto-hide sidebar on mobile
    function handleResize() {
        if (window.innerWidth < 768) {
            sidebar.classList.add('active');
            content.classList.add('active');
        } else {
            sidebar.classList.remove('active');
            content.classList.remove('active');
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize(); // Initial check

    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Real-time updates simulation
    function updateStats() {
        // Simulate real-time data updates
        const stats = document.querySelectorAll('.stat-card h2');
        stats.forEach(stat => {
            const current = parseInt(stat.textContent.replace(/,/g, ''));
            const randomChange = Math.floor(Math.random() * 10) - 2; // -2 to +7
            const newValue = Math.max(0, current + randomChange);
            stat.textContent = newValue.toLocaleString();
        });
    }

    // Update stats every 30 seconds
    setInterval(updateStats, 30000);
});