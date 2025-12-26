<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <div class="logo-icon">
                        <svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 10 L20 16 L12 22 Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="logo-text">AdNetwork</span>
                </div>
                <p class="footer-description">
                    Professional advertising network connecting advertisers with publishers worldwide.
                </p>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('website.home') }}">Home</a></li>
                    <li><a href="{{ route('website.advertiser') }}">Advertiser</a></li>
                    <li><a href="{{ route('website.publisher') }}">Publisher</a></li>
                    <li><a href="{{ route('website.faq') }}">FAQ</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">Legal</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('website.report-abuse') }}">Report Abuse</a></li>
                    <li><a href="{{ route('website.report-dmca') }}">Report DMCA</a></li>
                    <li><a href="{{ route('website.contact') }}">Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-title">Connect</h4>
                <ul class="footer-links">
                    <li><a href="#" target="_blank" rel="noopener noreferrer">Privacy Policy</a></li>
                    <li><a href="#" target="_blank" rel="noopener noreferrer">Terms of Service</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="copyright">
                &copy; {{ date('Y') }} {{ config('app.name', 'AdNetwork') }}. All rights reserved.
            </p>
        </div>
    </div>
</footer>




