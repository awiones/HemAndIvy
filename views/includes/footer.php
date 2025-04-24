<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-container">
            <!-- Column 1: About -->
            <div class="footer-column">
                <a href="#" class="footer-logo">
                    Hem <span>&</span> Ivy
                </a>
                <p class="footer-about">
                    Curated fashion auctions featuring vintage, designer, and sustainable clothing pieces. 
                    Every item is authenticated and carefully selected for quality and style.
                </p>
                <div class="footer-social">
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-pinterest"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-column">
                <h3 class="footer-heading">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Current Auctions</a></li>
                    <li><a href="#">How It Works</a></li>
                    <li><a href="#">Seller Information</a></li>
                    <li><a href="#">Authentication Process</a></li>
                    <li><a href="#">FAQs</a></li>
                </ul>
            </div>

            <!-- Column 3: Categories -->
            <div class="footer-column">
                <h3 class="footer-heading">Categories</h3>
                <ul class="footer-links">
                    <li><a href="#">Vintage</a></li>
                    <li><a href="#">Designer</a></li>
                    <li><a href="#">Accessories</a></li>
                    <li><a href="#">Men's Collection</a></li>
                    <li><a href="#">Luxury</a></li>
                    <li><a href="#">Sustainable</a></li>
                </ul>
            </div>

            <!-- Column 4: Contact -->
            <div class="footer-column">
                <h3 class="footer-heading">Contact Us</h3>
                <div class="footer-contact">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>123 Fashion Avenue, Suite 500<br>New York, NY 10001</div>
                </div>
                <div class="footer-contact">
                    <i class="fas fa-envelope"></i>
                    <div>support@hemandivy.com</div>
                </div>
                <div class="footer-contact">
                    <i class="fas fa-phone"></i>
                    <div>+1 (212) 555-7890</div>
                </div>
            </div>
        </div>

        <!-- Trust Badges -->
        <div class="trust-badges">
            <div class="trust-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Secure Bidding</span>
            </div>
            <div class="trust-badge">
                <i class="fas fa-certificate"></i>
                <span>Authenticated Items</span>
            </div>
            <div class="trust-badge">
                <i class="fas fa-truck"></i>
                <span>Insured Shipping</span>
            </div>
            <div class="trust-badge">
                <i class="fas fa-undo"></i>
                <span>Easy Returns</span>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; 2025 Hem & Ivy. All rights reserved. | <a href="#" style="color: rgba(255, 255, 255, 0.5);">Privacy Policy</a> | <a href="#" style="color: rgba(255, 255, 255, 0.5);">Terms of Service</a></p>
        </div>
    </div>
</footer>

<style>
    /* Footer */
    .footer {
        background-color: var(--charcoal-velvet);
        color: white;
        padding: 80px 0 40px;
    }

    .footer-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 40px;
    }

    .footer-logo {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin-bottom: 20px;
        display: block;
    }

    .footer-logo span {
        color: var(--aged-gold);
    }

    .footer-about {
        margin-bottom: 20px;
        font-size: 14px;
        line-height: 1.8;
        color: rgba(255, 255, 255, 0.7);
    }

    .footer-social {
        display: flex;
        gap: 15px;
    }

    .social-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        transition: all 0.3s ease;
    }

    .social-icon:hover {
        background-color: var(--aged-gold);
        transform: translateY(-3px);
    }

    .footer-heading {
        font-size: 18px;
        margin-bottom: 25px;
        color: white;
    }

    .footer-links {
        list-style: none;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: color 0.3s ease;
        font-size: 14px;
    }

    .footer-links a:hover {
        color: var(--aged-gold);
    }

    .footer-contact {
        margin-bottom: 15px;
        display: flex;
        align-items: flex-start;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7);
    }

    .footer-contact i {
        margin-right: 10px;
        margin-top: 5px;
    }

    .footer-bottom {
        margin-top: 60px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.5);
    }

    .trust-badges {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 20px;
    }

    .trust-badge {
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
    }

    .trust-badge i {
        margin-right: 8px;
        color: var(--aged-gold);
        font-size: 16px;
    }
</style>