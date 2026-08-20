<?php
require_once __DIR__ . '/auth.php';

// If already logged in, redirect to dashboard
if (currentUser()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShieldOS - Cyber Security Password Management Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Landing Page Specific Styles */
        .landing-navbar {
            background: linear-gradient(135deg, #f0f6ff 0%, #ffffff 100%);
        }

        .landing-hero {
            min-height: 90vh;
            display: flex;
            align-items: center;
            padding: 80px 0 60px;
            background: linear-gradient(135deg, #f0f6ff 0%, #ffffff 100%);
            position: relative;
            overflow: hidden;
        }

        .landing-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0, 102, 204, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .landing-hero-content {
            position: relative;
            z-index: 1;
        }

        .landing-hero h1 {
            font-size: clamp(2.5rem, 8vw, 4.5rem);
            font-weight: 900;
            line-height: 1.15;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .landing-hero .lead {
            font-size: 1.3rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .landing-cta-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .landing-cta-buttons .btn {
            font-weight: 600;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary-landing {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-light));
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
        }

        .btn-primary-landing:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.4);
            background: linear-gradient(135deg, var(--primary-blue-dark), var(--primary-blue));
            color: white;
        }

        .btn-secondary-landing {
            background: white;
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
        }

        .btn-secondary-landing:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-2px);
        }

        .hero-image {
            max-width: 100%;
            height: auto;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: #ffffff;
        }

        .section-title {
            font-size: 3rem;
            font-weight: 900;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--text-dark);
        }

        .feature-card {
            background: #f8f9fa;
            border: none;
            border-radius: 12px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 102, 204, 0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }

        .feature-card h3 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .feature-card p {
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Benefits Section */
        .benefits-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f0f6ff 0%, #ffffff 100%);
        }

        .benefit-item {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
            align-items: flex-start;
        }

        .benefit-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }

        .benefit-content h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .benefit-content p {
            color: var(--text-muted);
            margin: 0;
        }

        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark));
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .cta-btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-btn {
            padding: 0.9rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
        }

        .cta-btn-primary {
            background: white;
            color: var(--primary-blue);
        }

        .cta-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .cta-btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .cta-btn-secondary:hover {
            background: white;
            color: var(--primary-blue);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
                margin-bottom: 2rem;
            }

            .landing-hero h1 {
                font-size: 2rem;
            }

            .landing-hero .lead {
                font-size: 1.1rem;
            }

            .landing-cta-buttons {
                flex-direction: column;
            }

            .landing-cta-buttons .btn {
                width: 100%;
            }

            .benefit-item {
                margin-bottom: 1.5rem;
            }

            .cta-section h2 {
                font-size: 1.8rem;
            }

            .cta-btn-group {
                flex-direction: column;
            }

            .cta-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light landing-navbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#home">
                <i class="fa-solid fa-shield me-2"></i>ShieldOS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#benefits">Benefits</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="login.php" class="btn btn-outline-primary btn-sm me-2">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="signup.php" class="btn btn-primary btn-sm">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="landing-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 landing-hero-content mb-5 mb-lg-0">
                    <h1>Secure Your Digital Life</h1>
                    <p class="lead">
                        Master your passwords with ShieldOS. Advanced security audit, secure vault storage, and intelligent password generation all in one platform.
                    </p>
                    <div class="landing-cta-buttons">
                        <a href="signup.php" class="btn btn-primary-landing">
                            Get Started Free <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <a href="login.php" class="btn btn-secondary-landing">
                            Already Have Account? Login
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div style="text-align: center;">
                        <div style="width: 100%; height: 400px; background: linear-gradient(135deg, rgba(0, 102, 204, 0.1), rgba(0, 128, 255, 0.05)); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-lock" style="font-size: 150px; color: rgba(0, 102, 204, 0.3);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">Powerful Features</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Password Audit</h3>
                        <p>Analyze your passwords for weaknesses. Get real-time security scores and detailed vulnerability reports to strengthen your digital defense.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-vault"></i>
                        </div>
                        <h3>Secure Vault</h3>
                        <p>Store passwords and sensitive information in an encrypted vault. Access them anytime, anywhere with military-grade encryption.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-wand-magic-sparkles"></i>
                        </div>
                        <h3>Smart Generator</h3>
                        <p>Generate strong, unique passwords in seconds. Customize length, complexity, and character types to match any requirement.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="benefits-section">
        <div class="container">
            <h2 class="section-title">Why Choose ShieldOS?</h2>
            <div class="row">
                <div class="col-lg-6">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Military-Grade Encryption</h4>
                            <p>Your passwords are protected with the same encryption technology used by governments and financial institutions.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>No Server Storage</h4>
                            <p>Your sensitive data never leaves your control. Everything is encrypted client-side for maximum privacy.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Easy to Use</h4>
                            <p>Intuitive interface designed for everyone. No technical knowledge required to manage your digital security.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Real-Time Monitoring</h4>
                            <p>Get instant alerts about weak passwords and suspicious activities on your accounts.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Unlimited Storage</h4>
                            <p>Store as many passwords as you need. Organize them with custom categories and tags.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>24/7 Support</h4>
                            <p>Our security experts are always available to help you secure your digital life.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Secure Your Passwords?</h2>
            <p>Join thousands of users who trust ShieldOS to protect their digital identity.</p>
            <div class="cta-btn-group">
                <a href="signup.php" class="cta-btn cta-btn-primary">Create Free Account</a>
                <a href="#features" class="cta-btn cta-btn-secondary">Learn More</a>
            </div>
        </div>
    </section>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
