<?php
/**
 * TrafficLens AI — Custom Obsidian Dark Traffic Enforcement Portal
 */
require_once __DIR__ . '/../config/session.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TrafficLens AI — Next-Gen Automated Traffic Violation & Fine Management">
    <title>TrafficLens AI — Automated Traffic Violation & Fine Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dark-portal-body">

    <!-- Top Dark Header Navigation Bar -->
    <header class="dark-header">
        <div class="dark-header-left">
            <a href="#" class="dark-brand">
                <i class="fas fa-video"></i>
                <div>Traffic<span>Lens</span> AI</div>
            </a>
            <nav class="dark-nav">
                <a href="#features">Features</a>
            </nav>
        </div>

        <div class="dark-header-right">
            <a href="../driver/index.php" class="btn-dark-secondary" style="font-size: 13px; padding: 7px 16px; border-color: rgba(152, 255, 56, 0.4); color: #98ff38;">
                <i class="fas fa-credit-card"></i> Pay Challan Online
            </a>
            <button class="btn-dark-text" onclick="openLoginModal()">Sign In</button>
            <button class="btn-cyan-glow" onclick="fillAndOpenModal()">
                <i class="fas fa-shield-halved"></i>
                <span>Console Portal</span>
            </button>
        </div>
    </header>

    <!-- Main Dark Landing Page Content -->
    <main class="dark-main-container">

        <!-- Hero Section -->
        <section class="dark-hero" id="overview">
            <div class="dark-hero-left">
                <div class="dark-badge-pill">
                    <span class="pill-dot"></span>
                    <span>AI-POWERED ANPR TRAFFIC SURVEILLANCE v2.4</span>
                </div>

                <h1 class="dark-hero-title">
                    Next-Gen Automated <span>Traffic Violation & Fine</span> Management
                </h1>

                <p class="dark-hero-description">
                    Real-time AI license plate recognition, automatic e-challan generation, driver penalty point tracking, and seamless digital fine settlements for modern smart cities.
                </p>

                <div class="dark-hero-cta-group" style="margin-bottom: 24px;">
                    <button class="btn-cyan-glow" onclick="openLoginModal()">
                        <i class="fas fa-lock"></i>
                        <span>Sign In to Officer Console</span>
                    </button>

                    <button class="btn-dark-secondary" onclick="fillAndOpenModal()">
                        <i class="fas fa-bolt" style="color: #98ff38;"></i>
                        <span>Auto-fill Demo Credentials</span>
                    </button>
                </div>

                <!-- TrafficLens AI System Capabilities Accordion (Below Console Login Buttons) -->
                <div class="hero-accordion-details" id="features">
                    <div class="hero-accordion-header active" id="heroAccordionHeader" onclick="toggleHeroAccordion()">
                        <span>TrafficLens AI System Capabilities</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="hero-accordion-body" id="heroAccordionBody">
                        <div class="hero-accordion-item">
                            <i class="fas fa-plus"></i>
                            <div><strong>ANPR Camera Stream:</strong> Real-time junction plate recognition & timestamped evidence capture.</div>
                        </div>
                        <div class="hero-accordion-item">
                            <i class="fas fa-plus"></i>
                            <div><strong>Smart E-Challan Engine:</strong> Automated violation classification, penalty points & instant SMS notices.</div>
                        </div>
                        <div class="hero-accordion-item">
                            <i class="fas fa-plus"></i>
                            <div><strong>Revenue & Audit Portal:</strong> Real-time fine collection analytics & officer deployment reporting.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dark-hero-graphic">
                <!-- Custom Dark High-Tech Vector Illustration -->
                <svg class="dark-svg-illustration" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="500" height="400" rx="24" fill="#141A26" fill-opacity="0.6"/>
                    <rect x="20" y="20" width="460" height="360" rx="16" stroke="rgba(0, 229, 255, 0.2)" stroke-width="1.5"/>
                    <!-- Radar grid circle -->
                    <circle cx="250" cy="200" r="120" stroke="rgba(0, 229, 255, 0.15)" stroke-width="1.5" stroke-dasharray="6 6"/>
                    <circle cx="250" cy="200" r="70" stroke="rgba(0, 229, 255, 0.25)" stroke-width="1.5"/>
                    <circle cx="250" cy="200" r="6" fill="#00E5FF"/>
                    <!-- Clockwise Rotating Radar Needle & Backward Trailing Light Sweep -->
                    <g class="radar-sweep-group">
                        <!-- Trailing Light Sweep Cone (Behind Needle) -->
                        <path d="M250 200 L350 100 A141 141 0 0 0 287 63 Z" fill="url(#laserGrad)" opacity="0.6"/>
                        <!-- Leading Needle Line (Clockwise Front Edge) -->
                        <line x1="250" y1="200" x2="350" y2="100" stroke="#00E5FF" stroke-width="2.5" stroke-linecap="round"/>
                    </g>
                    <!-- Camera Node -->
                    <rect x="80" y="80" width="100" height="60" rx="10" fill="#10141D" stroke="#00E5FF" stroke-width="1.5"/>
                    <circle cx="130" cy="110" r="16" fill="#0A0D14" stroke="#98FF38" stroke-width="2"/>
                    <circle cx="130" cy="110" r="6" fill="#98FF38" class="camera-pulse-dot"/>
                    <!-- Vehicle License Plate Scanning Node -->
                    <rect x="280" y="260" width="140" height="70" rx="12" fill="#10141D" stroke="#00E5FF" stroke-width="1.5"/>
                    <rect x="295" y="275" width="110" height="24" rx="4" fill="#0A0D14" stroke="#D4AF37" stroke-width="1"/>
                    <text x="350" y="291" fill="#D4AF37" font-family="monospace" font-size="11" font-weight="bold" text-anchor="middle">MH 12 AB 4321</text>
                    <text x="350" y="318" fill="#98FF38" font-family="monospace" font-size="10" text-anchor="middle">● ANPR DETECTED</text>
                    <!-- Gradient definitions (Bright at needle line x1,y1 -> Fades out at trailing edge x2,y2) -->
                    <defs>
                        <linearGradient id="laserGrad" x1="350" y1="100" x2="287" y2="63" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#00E5FF" stop-opacity="0.85"/>
                            <stop offset="100%" stop-color="#00E5FF" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </section>
    </main>

    <!-- Dark Footer -->
    <footer class="dark-footer">
        <div>© <?php echo date('Y'); ?> TrafficLens AI. All Rights Reserved.</div>
    </footer>

    <!-- Interactive Dark Sign In Modal Dialog -->
    <div class="dark-modal-overlay <?php echo $error ? 'active' : ''; ?>" id="loginModal">
        <div class="dark-modal-card">
            <button type="button" class="dark-modal-close" onclick="closeLoginModal()">&times;</button>
            
            <div class="dark-modal-brand">
                <div class="dark-modal-logo">
                    <i class="fas fa-video"></i>
                </div>
                <h2>Officer Console Login</h2>
                <p>Sign in to access TrafficLens AI Console</p>
            </div>

            <?php if ($error): ?>
                <div class="login-error mb-16">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="authenticate.php" method="POST" class="dark-modal-form" id="loginForm">
                <div class="dark-field-group">
                    <label for="username">Officer Username</label>
                    <input type="text" id="username" name="username" class="dark-field-input" placeholder="Enter username" required autocomplete="username">
                </div>

                <div class="dark-field-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="dark-field-input" placeholder="Enter password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-cyan-glow" style="width: 100%; justify-content: center; padding: 14px; margin-top: 8px;">
                    <span>Sign In to Console</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="dark-demo-helper">
                <div>Default Credentials: <code>admin</code> / <code>admin123</code></div>
                <button type="button" class="btn-autofill-cyan" onclick="fillDemoCredentials()">Auto-fill</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Interactions -->
    <script>
    function toggleHeroAccordion() {
        const body = document.getElementById('heroAccordionBody');
        const header = document.getElementById('heroAccordionHeader');
        if (body.style.display === 'none') {
            body.style.display = 'flex';
            header.classList.add('active');
        } else {
            body.style.display = 'none';
            header.classList.remove('active');
        }
    }

    function openLoginModal() {
        document.getElementById('loginModal').classList.add('active');
        document.getElementById('username').focus();
    }

    function closeLoginModal() {
        document.getElementById('loginModal').classList.remove('active');
    }

    function fillDemoCredentials() {
        document.getElementById('username').value = 'admin';
        document.getElementById('password').value = 'admin123';
    }

    function fillAndOpenModal() {
        fillDemoCredentials();
        openLoginModal();
    }

    // Close modal on escape key or backdrop click
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLoginModal();
    });

    document.getElementById('loginModal').addEventListener('click', function(e) {
        if (e.target === this) closeLoginModal();
    });
    </script>
    <script src="../assets/js/app.js"></script>
</body>
</html>


