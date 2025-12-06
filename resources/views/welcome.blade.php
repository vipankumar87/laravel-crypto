<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CryptoInvest') }} - Crypto Investment Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
            background-color: #050301;
        }

        .gradient-bg {
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.07) 0%, transparent 55%),
                radial-gradient(circle at bottom, rgba(255, 193, 7, 0.18) 0%, transparent 60%),
                radial-gradient(circle at 10% 90%, rgba(255, 215, 0, 0.22) 0%, transparent 55%),
                linear-gradient(135deg, #020308 0%, #050814 40%, #120a02 100%);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }

        .crypto-bg {
            background: linear-gradient(135deg, #3b2604 0%, #8c5a06 40%, #f4c542 100%);
        }

        .dark-bg {
            background: linear-gradient(135deg, #120a02 0%, #2a1804 40%, #5a3604 100%);
        }

        .gold-gradient {
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.35) 0%, transparent 45%),
                linear-gradient(135deg, #f4b41a 0%, #ffdd55 35%, #ffb300 70%, #8c5a06 100%);
        }

        .text-gradient {
            background: linear-gradient(135deg, #fff3b0 0%, #ffd54f 40%, #ffb300 80%, #b37a10 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f9a825 0%, #ffd54f 40%, #ffb300 80%, #b37a10 100%);
            color: #2a1804;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        .logo-img{
            width: 57px;
            vertical-align: middle;
        } 
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section {
            padding: 80px 0;
        }

        .feature-card {
            background: rgba(10, 6, 2, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            border: 1px solid rgba(255, 215, 0, 0.4);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .plan-card {
            background: rgba(255, 248, 225, 0.97);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            border: 1px solid rgba(179, 122, 16, 0.4);
            transition: all 0.3s ease;
            color: #3b2604;
        }

        .plan-card.featured {
            background: radial-gradient(circle at top, #fff9c4 0%, #ffd54f 35%, #ffb300 70%, #8c5a06 100%);
            color: #2a1804;
            transform: scale(1.05);
        }

        .plan-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .plan-card.featured:hover {
            transform: translateY(-10px) scale(1.07);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .stat-item {
            text-align: center;
            color: #3b2604;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #fff8e1;
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.35);
        }

        .hero {
            padding: 140px 0 120px;
            text-align: center;
            color: white;
        }

        .hero-panel {
            max-width: 760px;
            margin: 0 auto;
            padding: 52px 32px 56px;
            background:
                radial-gradient(circle at top, rgba(0, 0, 0, 0.65) 0%, rgba(0, 0, 0, 0.5) 35%, rgba(0, 0, 0, 0.35) 100%);
            border-radius: 32px;
            box-shadow: 0 26px 70px rgba(0, 0, 0, 0.7);
        }

        .hero-doge-img {
            width: 190px;
            height: 190px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 28px;
            display: block;
            box-shadow:
                0 0 0 4px rgba(255, 215, 0, 0.7),
                0 0 40px rgba(255, 215, 0, 0.65),
                0 26px 60px rgba(0, 0, 0, 0.8);
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 640px;
            margin-left: auto;
            margin-right: auto;
            color: #f9f6ee;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(15, 9, 3, 0.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 215, 0, 0.4);
            z-index: 1000;
            padding: 15px 0;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .footer {
            background: radial-gradient(circle at top, #3b2604 0%, #120a02 50%, #050301 100%);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer h3 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        .footer ul {
            list-style: none;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer ul li a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer ul li a:hover {
            color: white;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .nav-links {
                display: none;
            }

            .plans-grid,
            .features-grid {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 60px 0;
            }
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img"/> Doge Shaker
            </a>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#plans">Plans</a></li>
                <li><a href="#stats">Stats</a></li>
                @if (Route::has('login'))
                    @auth
                        <li><a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Login</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}" class="btn-primary">Get Started</a></li>
                        @endif
                    @endauth
                @endif
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="gradient-bg">
        <div class="hero">
            <div class="container">
                <div class="hero-panel animate-fade-in">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Doge Shaker" class="hero-doge-img">
                    <h1 class="animate-float">
                        Next-Gen Crypto <br>
                        <span class="text-gradient">Investment Platform</span>
                    </h1>
                    <p>
                        Maximize your crypto returns with our advanced AI-driven investment strategies.
                        Join thousands of investors earning consistent daily profits.
                    </p>
                    <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary">
                                <i class="fas fa-rocket"></i> Start Investing Now
                            </a>
                        @endif
                        <a href="#features" class="btn-secondary">
                            <i class="fas fa-chart-line"></i> Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section crypto-bg">
        <div class="container">
            <div class="text-center" style="color: white; margin-bottom: 50px;">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 20px;">
                    Why Choose Doge Shaker?
                </h2>
                <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">
                    Advanced features designed to maximize your cryptocurrency investment returns
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div style="background: radial-gradient(circle at top, #fff8e1 0%, #ffd54f 40%, #ffb300 75%, #8c5a06 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-robot" style="font-size: 2rem; color: white;"></i>
                    </div>
                    <h3 style="color: white; font-size: 1.5rem; margin-bottom: 15px; font-weight: 600;">AI-Powered Trading</h3>
                    <p style="color: rgba(255, 255, 255, 0.8);">
                        Advanced algorithms analyze market trends 24/7 to maximize your investment returns automatically.
                    </p>
                </div>

                <div class="feature-card">
                    <div style="background: linear-gradient(135deg, #f7931e 0%, #ffd700 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-shield-alt" style="font-size: 2rem; color: white;"></i>
                    </div>
                    <h3 style="color: white; font-size: 1.5rem; margin-bottom: 15px; font-weight: 600;">Bank-Level Security</h3>
                    <p style="color: rgba(255, 255, 255, 0.8);">
                        Military-grade encryption and cold storage protect your investments with maximum security.
                    </p>
                </div>

                <div class="feature-card">
                    <div style="background: linear-gradient(135deg, #00d4aa 0%, #00b894 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-users" style="font-size: 2rem; color: white;"></i>
                    </div>
                    <h3 style="color: white; font-size: 1.5rem; margin-bottom: 15px; font-weight: 600;">Referral Program</h3>
                    <p style="color: rgba(255, 255, 255, 0.8);">
                        Earn up to 10% commission by referring friends. Build your passive income network.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Investment Plans Section -->
    <section id="plans" class="section dark-bg">
        <div class="container">
            <div class="text-center" style="color: white; margin-bottom: 50px;">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 20px;">
                    Investment Plans
                </h2>
                <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">
                    Choose the perfect plan that matches your investment goals and risk appetite
                </p>
            </div>

            <div class="plans-grid">
                <div class="plan-card">
                    <div style="background: radial-gradient(circle at top, #fff8e1 0%, #ffe082 40%, #ffca28 70%, #b37a10 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-seedling" style="font-size: 1.5rem; color: white;"></i>
                    </div>
                    <h3 style="font-size: 1.8rem; margin-bottom: 10px; font-weight: 700;">Starter Plan</h3>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #b37a10; margin-bottom: 20px;">
                        2.5% <span style="font-size: 1rem; font-weight: 400;">daily</span>
                    </div>
                    <ul style="list-style: none; margin-bottom: 30px; text-align: left;">
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> Min: $50 - Max: $999</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> 30 Days Duration</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> Principal Included</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> 24/7 Support</li>
                    </ul>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary">Choose Plan</a>
                    @endif
                </div>

                <div class="plan-card featured">
                    <div style="background: rgba(255, 255, 255, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-star" style="font-size: 1.5rem; color: #ffd700;"></i>
                    </div>
                    <div style="background: #ffd700; color: #333; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-weight: 600; font-size: 0.9rem;">
                        MOST POPULAR
                    </div>
                    <h3 style="font-size: 1.8rem; margin-bottom: 10px; font-weight: 700;">Professional</h3>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #ffd700; margin-bottom: 20px;">
                        4.2% <span style="font-size: 1rem; font-weight: 400;">daily</span>
                    </div>
                    <ul style="list-style: none; margin-bottom: 30px; text-align: left;">
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #ffd700; margin-right: 10px;"></i> Min: $1,000 - Max: $9,999</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #ffd700; margin-right: 10px;"></i> 45 Days Duration</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #ffd700; margin-right: 10px;"></i> Principal Included</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #ffd700; margin-right: 10px;"></i> Priority Support</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #ffd700; margin-right: 10px;"></i> Bonus Referral Rate</li>
                    </ul>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-secondary">Choose Plan</a>
                    @endif
                </div>

                <div class="plan-card">
                    <div style="background: radial-gradient(circle at top, #fff8e1 0%, #ffecb3 35%, #ffca28 70%, #b37a10 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-crown" style="font-size: 1.5rem; color: white;"></i>
                    </div>
                    <h3 style="font-size: 1.8rem; margin-bottom: 10px; font-weight: 700;">VIP Elite</h3>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #b37a10; margin-bottom: 20px;">
                        6.8% <span style="font-size: 1rem; font-weight: 400;">daily</span>
                    </div>
                    <ul style="list-style: none; margin-bottom: 30px; text-align: left;">
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> Min: $10,000+</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> 60 Days Duration</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> Principal Included</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> VIP Support</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check" style="color: #00d4aa; margin-right: 10px;"></i> Personal Manager</li>
                    </ul>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary">Choose Plan</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="section gold-gradient">
        <div class="container">
            <div class="text-center" style="color: white; margin-bottom: 30px;">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 20px;">
                    Platform Statistics
                </h2>
                <p style="font-size: 1.2rem; opacity: 0.9;">
                    Join thousands of successful investors worldwide
                </p>
            </div>

            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">25,000+</div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Active Investors</h3>
                    <p style="opacity: 0.9;">Trusted by investors worldwide</p>
                </div>

                <div class="stat-item">
                    <div class="stat-number">$150M+</div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Total Invested</h3>
                    <p style="opacity: 0.9;">Successfully managed investments</p>
                </div>

                <div class="stat-item">
                    <div class="stat-number">98.7%</div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Success Rate</h3>
                    <p style="opacity: 0.9;">Consistent profitable returns</p>
                </div>

                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Market Analysis</h3>
                    <p style="opacity: 0.9;">Round-the-clock monitoring</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3><i class="fas fa-coins"></i> Doge Shaker</h3>
                    <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 20px;">
                        The world's most trusted cryptocurrency investment platform.
                        Start building your financial future today.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-telegram"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                    </div>
                </div>

                <div>
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#plans">Investment Plans</a></li>
                        @if (Route::has('login'))
                            <li><a href="{{ route('login') }}">Login</a></li>
                            @if (Route::has('register'))
                                <li><a href="{{ route('register') }}">Register</a></li>
                            @endif
                        @endif
                    </ul>
                </div>

                <div>
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#contact">Contact Us</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="#help">Help Center</a></li>
                        <li><a href="#terms">Terms of Service</a></li>
                        <li><a href="#privacy">Privacy Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Contact Info</h3>
                    <ul>
                        <li style="color: rgba(255, 255, 255, 0.7);">
                            <i class="fas fa-envelope" style="margin-right: 10px;"></i>
                            support@cryptoinvest.com
                        </li>
                        <li style="color: rgba(255, 255, 255, 0.7);">
                            <i class="fas fa-phone" style="margin-right: 10px;"></i>
                            +1 (555) 123-4567
                        </li>
                        <li style="color: rgba(255, 255, 255, 0.7);">
                            <i class="fas fa-clock" style="margin-right: 10px;"></i>
                            24/7 Support Available
                        </li>
                    </ul>
                </div>
            </div>

            <hr style="border: none; height: 1px; background: rgba(255, 255, 255, 0.2); margin: 40px 0 20px;">

            <div style="text-align: center; color: rgba(255, 255, 255, 0.7);">
                <p>&copy; {{ date('Y') }} Doge Shaker. All rights reserved. | Built with ❤️ for crypto investors</p>
            </div>
        </div>
    </footer>

    <!-- Smooth Scrolling Script -->
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all feature cards and plan cards
        document.querySelectorAll('.feature-card, .plan-card, .stat-item').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                // Solid dark brown with gold hint when scrolled
                navbar.style.background = 'rgba(15, 9, 3, 0.97)';
                navbar.style.borderBottom = '1px solid rgba(255, 215, 0, 0.6)';
            } else {
                // Slightly transparent to blend with hero gold gradient at top
                navbar.style.background = 'rgba(15, 9, 3, 0.85)';
                navbar.style.borderBottom = '1px solid rgba(255, 215, 0, 0.4)';
            }
        });
    </script>
</body>
</html>