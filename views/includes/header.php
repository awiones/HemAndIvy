<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav class="navbar">
    <div class="container navbar-container">
        <a href="/home" class="logo">
            <i class="fas fa-leaf logo-icon"></i>
            Hem <span>&</span> Ivy
        </a>
        <div class="nav-right">
            <ul class="nav-links">
                <li><a href="/home"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="/auction"><i class="fas fa-gavel"></i> Auctions</a></li>
                <li><a href="#"><i class="fas fa-tshirt"></i> Collections</a></li>
                <li><a href="/about"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="#"><i class="fas fa-heart"></i> Favorites</a></li>
            </ul>
            <?php if (empty($_SESSION['user'])): ?>
                <a href="/login" class="btn btn-primary nav-signin-btn">Sign In</a>
            <?php else: ?>
                <a href="/logout" class="btn btn-primary nav-signin-btn">Sign Out</a>
            <?php endif; ?>
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</nav>

<style>
    /* Navigation */
    .navbar {
        padding: 20px 0;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        transition: all 0.4s ease;
        background-color: rgba(245, 243, 239, 0.95);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    /* Navbar scroll animation */
    .navbar.scrolled {
        padding: 10px 0;
        background-color: rgba(255, 255, 255, 0.98);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .navbar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .nav-links {
        display: flex;
        list-style: none;
        align-items: center;
        margin: 0;
        padding: 0;
        gap: 25px;
    }

    .nav-links li {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 0.3s ease, transform 0.4s ease;
    }

    .nav-signin-btn {
        font-weight: 700;
        font-size: 16px;
        padding: 12px 32px;
        border: 2px solid var(--imperial-purple);
        box-shadow: 0 0 10px 2px rgba(75, 40, 109, 0.10);
        background: linear-gradient(90deg, var(--imperial-purple) 70%, var(--aged-gold) 100%);
        color: #fff !important;
        letter-spacing: 1px;
        transition: background 0.3s, box-shadow 0.3s, border 0.3s, transform 0.3s;
    }

    .nav-signin-btn:hover {
        background: linear-gradient(90deg, #5d3485 70%, var(--aged-gold) 100%);
        color: #fff;
        border-color: #5d3485;
        box-shadow: 0 0 16px 4px rgba(75, 40, 109, 0.18);
        transform: translateY(-2px) scale(1.04);
    }

    .logo {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--imperial-purple);
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .logo:hover {
        transform: scale(1.05);
    }

    .logo span {
        color: var(--aged-gold);
    }

    .logo-icon {
        margin-right: 10px;
        font-size: 24px;
        transition: transform 0.4s ease;
    }

    .logo:hover .logo-icon {
        transform: rotate(15deg);
    }

    .nav-links a {
        text-decoration: none;
        color: var(--charcoal-velvet);
        font-weight: 500;
        font-size: 15px;
        transition: color 0.3s ease, transform 0.3s ease;
        display: flex;
        align-items: center;
        position: relative;
    }

    .nav-links a:after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -5px;
        left: 0;
        background: linear-gradient(90deg, var(--imperial-purple), var(--aged-gold));
        transition: width 0.3s ease;
    }

    .nav-links a:hover:after {
        width: 100%;
    }

    .nav-links a:hover {
        color: var(--imperial-purple);
        transform: translateY(-2px);
    }

    .nav-links a i {
        margin-right: 6px;
        font-size: 16px;
        transition: transform 0.4s ease;
    }

    .nav-links a:hover i {
        transform: scale(1.2);
    }

    .mobile-menu-toggle {
        display: none;
        flex-direction: column;
        cursor: pointer;
        width: 30px;
        height: 24px;
        justify-content: space-between;
        position: relative;
        z-index: 1001;
    }

    .mobile-menu-toggle span {
        height: 2px;
        width: 100%;
        background-color: var(--imperial-purple);
        border-radius: 2px;
        transition: all 0.4s ease;
    }

    /* Mobile Navigation */
    @media (max-width: 992px) {
        .mobile-menu-toggle {
            display: flex;
        }

        .nav-links {
            position: fixed;
            top: 0;
            right: -100%;
            height: 100vh;
            width: 270px;
            background-color: white;
            flex-direction: column;
            gap: 30px;
            padding: 100px 40px;
            transition: right 0.5s cubic-bezier(0.77, 0, 0.175, 1);
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            justify-content: flex-start;
            align-items: flex-start;
        }

        .nav-links.active {
            right: 0;
        }

        .nav-links li {
            opacity: 0;
            transform: translateX(30px);
        }

        .nav-links.active li {
            opacity: 1;
            transform: translateX(0);
        }

        /* Staggered animation for nav items */
        .nav-links.active li:nth-child(1) { transition-delay: 0.2s; }
        .nav-links.active li:nth-child(2) { transition-delay: 0.3s; }
        .nav-links.active li:nth-child(3) { transition-delay: 0.4s; }
        .nav-links.active li:nth-child(4) { transition-delay: 0.5s; }
        .nav-links.active li:nth-child(5) { transition-delay: 0.6s; }

        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease, visibility 0.4s ease;
            z-index: 999;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .nav-signin-btn {
            display: none;
        }

        .nav-links li {
            width: 100%;
        }

        .nav-links a {
            width: 100%;
            padding: 5px 0;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll animation for navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        
        // Create overlay element for mobile menu
        const overlay = document.createElement('div');
        overlay.classList.add('overlay');
        document.body.appendChild(overlay);

        // Toggle menu
        menuToggle.addEventListener('click', function() {
            menuToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
        });

        // Close menu when clicking outside
        overlay.addEventListener('click', function() {
            menuToggle.classList.remove('active');
            navLinks.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        // Add animation delay to nav items
        const navItems = document.querySelectorAll('.nav-links li');
        navItems.forEach((item, index) => {
            item.style.transitionDelay = `${0.1 + index * 0.1}s`;
        });
    });
</script>