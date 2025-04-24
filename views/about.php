<?php
// Optionally include config if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Hem & Ivy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about Hem & Ivy - a curated fashion auction platform for discerning collectors and style enthusiasts.">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/about.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* About Page Styles */

    /* Hero Section */
    .about-hero {
        background: #f5f3ef;
        padding: 100px 0 60px;
        text-align: center;
    }

    .about-hero .container {
        max-width: 800px;
        margin: 0 auto;
    }

    .hero-title {
        font-family: 'Playfair Display', serif;
        font-size: 3rem;
        margin-bottom: 20px;
        color: #333;
    }

    .hero-tagline {
        font-size: 1.3rem;
        color: #6c757d;
        font-weight: 300;
    }

    /* Content Section */
    .about-content {
        padding: 60px 0;
    }

    .about-content .container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .about-section {
        margin-bottom: 60px;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .about-section.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .about-section h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        margin-bottom: 25px;
        position: relative;
        padding-bottom: 15px;
    }

    .about-section h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background-color: #d4af37;
    }

    .about-section p {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #444;
        margin-bottom: 20px;
    }

    /* Values Section */
    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .value-card {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .value-card:hover {
        transform: translateY(-10px);
    }

    .value-card i {
        font-size: 2.5rem;
        color: #d4af37;
        margin-bottom: 15px;
    }

    .value-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        margin-bottom: 12px;
    }

    .value-card p {
        font-size: 1rem;
        color: #6c757d;
    }

    /* Contact Section */
    .contact-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .contact-method {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .contact-method i {
        font-size: 1.4rem;
        color: #d4af37;
    }

    .contact-method p {
        margin: 0;
        font-size: 1rem;
    }

    .contact-method a {
        color: #d4af37;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .contact-method a:hover {
        color: #b28c00;
        text-decoration: underline;
    }

    /* Team Section placeholder */
    .team {
        text-align: center;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.4rem;
        }
        
        .hero-tagline {
            font-size: 1.1rem;
        }
        
        .about-section h2 {
            font-size: 1.8rem;
        }
        
        .values-grid {
            grid-template-columns: 1fr;
        }
        
        .contact-methods {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .about-hero {
            padding: 70px 0 40px;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .about-section h2 {
            font-size: 1.6rem;
        }
    }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <section class="about-hero">
            <div class="container">
                <h1 class="hero-title">About Hem & Ivy</h1>
                <p class="hero-tagline">Curated fashion auctions for the discerning collector and style enthusiast.</p>
            </div>
        </section>

        <section class="about-content">
            <div class="container">
                <article class="about-section">
                    <h2>Our Story</h2>
                    <p>
                        Hem & Ivy was founded by our CEO, Al Ghozali Ramadhan from Indonesia, who has been passionate about timeless style and the stories behind every piece of clothing. Since 2025, we have brought together a community of collectors, sellers, and fashion lovers to discover, bid on, and own unique vintage, designer, and sustainable pieces.
                    </p>
                    <p>
                        Our journey began in 2025 when Al Ghozali Ramadhan, an avid vintage collector himself, recognized the need for a trusted platform that combines the excitement of auctions with the assurance of authenticity in the fashion space.
                    </p>
                </article>

                <article class="about-section">
                    <h2>Our Mission</h2>
                    <p>
                        To make high-quality, authenticated fashion accessible to all, while promoting sustainability and the joy of discovery. Every auction is carefully curated to ensure authenticity, quality, and style.
                    </p>
                    <p>
                        We believe in the power of circular fashion to reduce waste and celebrate craftsmanship that stands the test of time.
                    </p>
                </article>

                <article class="about-section values">
                    <h2>What We Value</h2>
                    <div class="values-grid">
                        <div class="value-card">
                            <i class="fas fa-certificate"></i>
                            <h3>Authenticity</h3>
                            <p>Every item is meticulously verified for quality and provenance by our team of experts.</p>
                        </div>
                        <div class="value-card">
                            <i class="fas fa-leaf"></i>
                            <h3>Sustainability</h3>
                            <p>We champion circular fashion and conscious consumption through our curated selections.</p>
                        </div>
                        <div class="value-card">
                            <i class="fas fa-users"></i>
                            <h3>Community</h3>
                            <p>Connecting buyers and sellers who share a love for unique style and fashion history.</p>
                        </div>
                        <div class="value-card">
                            <i class="fas fa-glasses"></i>
                            <h3>Transparency</h3>
                            <p>Clear auction processes, honest descriptions, and open communication throughout.</p>
                        </div>
                    </div>
                </article>

                <article class="about-section team">
                    <h2>Meet Our Team</h2>
                    <p>
                        Our dedicated team brings together expertise from fashion, authentication, e-commerce, and curation to ensure the best possible experience for our community.
                    </p>
                    <!-- Team members could be added here with photos and brief bios -->
                </article>

                <article class="about-section contact">
                    <h2>Contact Us</h2>
                    <p>
                        Have questions or want to collaborate? We'd love to hear from you.
                    </p>
                    <div class="contact-methods">
                        <div class="contact-method">
                            <i class="fas fa-envelope"></i>
                            <p>Email us at <a href="mailto:support@hemandivy.com">support@hemandivy.com</a></p>
                        </div>
                        <div class="contact-method">
                            <i class="fas fa-phone"></i>
                            <p>Call us at <a href="tel:+18005551234">1-800-555-1234</a></p>
                        </div>
                        <div class="contact-method">
                            <i class="fas fa-map-marker-alt"></i>
                            <p>Visit our showroom at 123 Fashion Avenue, New York, NY 10001</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        // Optional: Add any page-specific JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Animation for sections when they come into view
            const sections = document.querySelectorAll('.about-section');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {threshold: 0.1});
            
            sections.forEach(section => {
                observer.observe(section);
            });
        });
    </script>
</body>
</html>