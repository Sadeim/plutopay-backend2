<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlutoPay – Financial Infrastructure for Every Business</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #635bff;
            --primary-dark: #4b45c6;
            --primary-light: #7a73ff;
            --cyan: #00d4aa;
            --orange: #ff7a00;
            --pink: #ff49db;
            --blue: #0073e6;
            --bg: #ffffff;
            --bg-light: #f6f9fc;
            --bg-dark: #0a2540;
            --text: #0a2540;
            --text-muted: #425466;
            --text-light: #8898aa;
            --border: #e3e8ee;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Sora', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        .mono { font-family: 'JetBrains Mono', monospace; }

        /* ── Nav ── */
        nav {
            position: fixed; top: 0; width: 100%; z-index: 100;
            padding: 1rem 2rem;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        nav .inner {
            max-width: 1200px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
        }
        nav .logo-img { height: 28px; }
        nav .links { display: flex; gap: 2rem; align-items: center; }
        nav .links a {
            color: var(--text-muted); text-decoration: none;
            font-size: 0.88rem; font-weight: 500; transition: color 0.2s;
        }
        nav .links a:hover { color: var(--text); }
        .btn-nav { color: white !important;
            padding: 0.55rem 1.25rem; background: #0a2540; color: white;
            border: none; border-radius: 20px; font-size: 0.85rem; font-weight: 600;
            font-family: inherit; cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .btn-nav:hover { background: #000; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .btn-nav-outline {
            padding: 0.55rem 1.25rem; background: transparent; color: var(--text);
            border: none; font-size: 0.85rem; font-weight: 600;
            font-family: inherit; cursor: pointer; text-decoration: none;
        }

        /* ── Hero ── */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center;
            padding: 8rem 2rem 4rem;
            overflow: hidden;
        }
        .hero-gradient {
            position: absolute; top: -30%; right: -10%;
            width: 900px; height: 900px;
            background: conic-gradient(from 180deg, var(--primary), var(--pink), var(--orange), var(--cyan), var(--primary));
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.25;
            pointer-events: none;
        }
        .hero-gradient-2 {
            position: absolute; bottom: -20%; left: -15%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99,91,255,0.15), transparent 70%);
            pointer-events: none;
        }
        .hero h1 {
            font-size: clamp(2.8rem, 6vw, 4.5rem);
            font-weight: 800; letter-spacing: -2.5px;
            line-height: 1.08; max-width: 800px;
            position: relative;
            animation: fadeUp 0.8s ease both;
        }
        .hero h1 .highlight {
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero > p {
            margin-top: 1.5rem; font-size: 1.2rem;
            color: var(--text-muted); max-width: 540px;
            line-height: 1.7; position: relative;
            animation: fadeUp 0.8s 0.15s ease both;
        }
        .hero-actions {
            display: flex; gap: 0.75rem; margin-top: 2.5rem;
            position: relative; animation: fadeUp 0.8s 0.25s ease both;
        }
        .btn-hero {
            padding: 0.9rem 2rem; background: #0a2540; color: white;
            border: none; border-radius: 24px; font-size: 1rem; font-weight: 600;
            font-family: inherit; cursor: pointer; transition: all 0.3s; text-decoration: none;
        }
        .btn-hero:hover { background: #000; box-shadow: 0 8px 30px rgba(99,91,255,0.35); transform: translateY(-2px); }
        .btn-hero-alt {
            padding: 0.9rem 2rem; background: transparent; color: var(--text);
            border: 1px solid var(--border); border-radius: 24px; font-size: 1rem;
            font-weight: 500; font-family: inherit; cursor: pointer; transition: all 0.3s; text-decoration: none;
        }
        .btn-hero-alt:hover { border-color: #ccc; background: var(--bg-light); }
        .hero-sub-links {
            display: flex; gap: 2rem; margin-top: 1.5rem;
            animation: fadeUp 0.8s 0.35s ease both; position: relative;
        }
        .hero-sub-links a { color: var(--text-light); font-size: 0.82rem; text-decoration: none; display: flex; align-items: center; gap: 0.3rem; }
        .hero-sub-links a:hover { color: var(--primary); }
        .hero-sub-links a .arrow { transition: transform 0.2s; }
        .hero-sub-links a:hover .arrow { transform: translateX(3px); }

        /* ── Hero Visual ── */
        .hero-visual {
            margin-top: 4rem; position: relative;
            max-width: 1000px; width: 100%;
            animation: fadeUp 1s 0.4s ease both;
        }
        .hero-dashboard {
            background: white; border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 60px rgba(10,37,64,0.12), 0 0 0 1px rgba(0,0,0,0.02);
            overflow: hidden;
        }
        .dash-topbar {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg-light);
        }
        .dash-dot { width: 10px; height: 10px; border-radius: 50%; }
        .dash-dot:nth-child(1) { background: #ff5f57; }
        .dash-dot:nth-child(2) { background: #ffbd2e; }
        .dash-dot:nth-child(3) { background: #28c840; }
        .dash-url {
            flex: 1; text-align: center; font-size: 0.75rem;
            color: var(--text-light); font-family: 'JetBrains Mono', monospace;
        }
        .dash-body { display: grid; grid-template-columns: 180px 1fr; min-height: 340px; }
        .dash-sidebar {
            padding: 1.25rem; border-right: 1px solid var(--border);
            background: #fafbfd;
        }
        .dash-sidebar .si {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 0.6rem; border-radius: 8px;
            font-size: 0.78rem; color: var(--text-muted);
            margin-bottom: 0.25rem;
        }
        .dash-sidebar .si.active { background: rgba(99,91,255,0.08); color: var(--primary); font-weight: 600; }
        .dash-sidebar .si .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: 0.4; }
        .dash-sidebar .si.active .dot { opacity: 1; background: #0a2540; }
        .dash-main { padding: 1.5rem; }
        .dash-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-bottom: 1.25rem; }
        .dash-stat {
            padding: 1rem; border-radius: 10px;
            border: 1px solid var(--border); background: white;
        }
        .dash-stat .label { font-size: 0.7rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; }
        .dash-stat .val { font-size: 1.4rem; font-weight: 700; margin-top: 0.25rem; }
        .dash-stat .val.green { color: #0cad56; }
        .dash-chart {
            height: 120px; border-radius: 10px;
            border: 1px solid var(--border);
            background: linear-gradient(180deg, rgba(99,91,255,0.03) 0%, white 100%);
            position: relative; overflow: hidden;
        }
        .dash-chart svg { position: absolute; bottom: 0; left: 0; width: 100%; }

        /* ── Sections ── */
        section { padding: 7rem 2rem; }
        .section-inner { max-width: 1100px; margin: 0 auto; }
        .section-label {
            display: inline-flex; align-items: center; gap: 0.5rem;
            font-size: 0.8rem; font-weight: 700; letter-spacing: 0.5px;
            text-transform: uppercase; color: var(--primary);
            margin-bottom: 1rem;
        }
        .section-label .bar { width: 20px; height: 2px; background: #0a2540; border-radius: 2px; }
        .section-title {
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 700; letter-spacing: -1.5px; line-height: 1.15;
        }
        .section-desc { color: var(--text-muted); font-size: 1.05rem; line-height: 1.7; margin-top: 0.75rem; max-width: 520px; }

        /* ── Products Grid ── */
        .products-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 1.25rem; margin-top: 3rem; }
        .product-card {
            padding: 2.5rem;
            border-radius: 16px; border: 1px solid var(--border);
            background: white;
            transition: all 0.3s; position: relative; overflow: hidden;
        }
        .product-card:hover { box-shadow: 0 12px 40px rgba(10,37,64,0.08); transform: translateY(-3px); }
        .product-card .icon-box {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-bottom: 1.5rem;
        }
        .product-card h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .product-card p { color: var(--text-muted); font-size: 0.92rem; line-height: 1.65; }
        .product-card .learn {
            display: inline-flex; align-items: center; gap: 0.3rem;
            margin-top: 1.25rem; font-size: 0.85rem; font-weight: 600;
            color: var(--primary); text-decoration: none; transition: gap 0.2s;
        }
        .product-card .learn:hover { gap: 0.5rem; }

        /* ── Dark Band ── */
        .dark-band {
            background: var(--bg-dark); color: white;
            padding: 6rem 2rem;
        }
        .dark-band .section-label { color: var(--cyan); }
        .dark-band .section-label .bar { background: var(--cyan); }
        .dark-band .section-desc { color: rgba(255,255,255,0.6); }
        .dark-stats {
            display: grid; grid-template-columns: repeat(4,1fr);
            gap: 2rem; margin-top: 3.5rem; text-align: center;
        }
        .dark-stats .ds-item h3 {
            font-size: 3rem; font-weight: 800; letter-spacing: -2px;
            background: linear-gradient(135deg, white, rgba(255,255,255,0.7));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .dark-stats .ds-item p { color: rgba(255,255,255,0.5); font-size: 0.85rem; margin-top: 0.5rem; }

        /* ── Code Section ── */
        .code-section { background: var(--bg-light); }
        .code-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; margin-top: 3rem; }
        .code-block {
            background: var(--bg-dark); border-radius: 14px; padding: 2rem;
            box-shadow: 0 20px 50px rgba(10,37,64,0.2);
        }
        .code-header {
            display: flex; gap: 6px; margin-bottom: 1.25rem;
        }
        .code-header span { width: 10px; height: 10px; border-radius: 50%; }
        .code-header span:nth-child(1) { background: #ff5f57; }
        .code-header span:nth-child(2) { background: #ffbd2e; }
        .code-header span:nth-child(3) { background: #28c840; }
        .code-pre {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem; line-height: 1.9;
            color: rgba(255,255,255,0.7);
        }
        .code-pre .kw { color: #c792ea; }
        .code-pre .fn { color: #82aaff; }
        .code-pre .str { color: #c3e88d; }
        .code-pre .num { color: #f78c6c; }
        .code-pre .cm { color: #546e7a; }
        .sdk-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 2rem; }
        .sdk-tag {
            padding: 0.45rem 1rem; border-radius: 20px;
            font-size: 0.8rem; font-weight: 600;
            border: 1px solid var(--border); background: white;
            color: var(--text-muted);
        }

        /* ── Pricing ── */
        .pricing-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1.25rem; margin-top: 3rem; }
        .price-card {
            padding: 2.5rem 2rem; border-radius: 16px;
            border: 1px solid var(--border); background: white;
            transition: all 0.3s;
        }
        .price-card.pop {
            border-color: var(--primary);
            box-shadow: 0 0 0 1px var(--primary), 0 12px 40px rgba(99,91,255,0.15);
            position: relative;
        }
        .price-card.pop .pop-label {
            position: absolute; top: -11px; left: 50%; transform: translateX(-50%);
            padding: 0.25rem 1rem; background: #0a2540; color: white;
            font-size: 0.7rem; font-weight: 700; border-radius: 20px; letter-spacing: 0.5px;
        }
        .price-card h4 { font-size: 0.9rem; font-weight: 600; color: var(--text-muted); }
        .price-card .amount { font-size: 2.8rem; font-weight: 800; letter-spacing: -2px; margin: 0.5rem 0; }
        .price-card .amount span { font-size: 1rem; font-weight: 400; color: var(--text-light); }
        .price-card .subdesc { font-size: 0.85rem; color: var(--text-light); margin-bottom: 1.5rem; line-height: 1.5; }
        .price-card ul { list-style: none; margin-bottom: 2rem; }
        .price-card ul li {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.45rem 0; font-size: 0.88rem; color: var(--text-muted);
        }
        .price-card ul li::before { content: '✓'; color: var(--primary); font-weight: 700; font-size: 0.85rem; }
        .btn-price {
            width: 100%; padding: 0.85rem; border-radius: 10px;
            font-family: inherit; font-size: 0.9rem; font-weight: 600;
            cursor: pointer; transition: all 0.3s;
            border: 1px solid var(--border); background: white; color: var(--text);
        }
        .btn-price:hover { background: var(--bg-light); }
        .price-card.pop .btn-price {
            background: #0a2540; color: white; border-color: var(--primary);
        }
        .price-card.pop .btn-price:hover { background: #000; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }

        /* ── CTA ── */
        .cta-final {
            background: linear-gradient(135deg, #0a2540 0%, #1a365d 50%, #0a2540 100%);
            color: white; text-align: center; padding: 7rem 2rem;
            position: relative; overflow: hidden;
        }
        .cta-final::before {
            content: ''; position: absolute;
            top: -50%; right: -20%; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99,91,255,0.2), transparent 60%);
            pointer-events: none;
        }
        .cta-final::after {
            content: ''; position: absolute;
            bottom: -40%; left: -10%; width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(0,212,170,0.1), transparent 60%);
            pointer-events: none;
        }
        .cta-final h2 {
            font-size: clamp(2rem, 4vw, 3rem); font-weight: 700;
            letter-spacing: -1.5px; position: relative;
        }
        .cta-final p { color: rgba(255,255,255,0.6); margin: 1rem 0 2.5rem; font-size: 1.1rem; position: relative; }
        .btn-cta {
            padding: 1rem 2.5rem; background: white; color: var(--bg-dark);
            border: none; border-radius: 24px; font-size: 1rem; font-weight: 700;
            font-family: inherit; cursor: pointer; transition: all 0.3s;
            text-decoration: none; position: relative;
        }
        .btn-cta:hover { box-shadow: 0 8px 30px rgba(255,255,255,0.2); transform: translateY(-2px); }

        /* ── Footer ── */
        footer {
            padding: 3rem 2rem; border-top: 1px solid var(--border);
            text-align: center; color: var(--text-light); font-size: 0.85rem;
        }

        /* ── Animations ── */
        @keyframes fadeUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .fade-in { opacity: 0; transform: translateY(25px); transition: all 0.7s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .products-grid, .pricing-grid, .code-grid { grid-template-columns: 1fr; }
            .dark-stats { grid-template-columns: repeat(2,1fr); }
            nav .links { display: none; }
            .dash-body { grid-template-columns: 1fr; }
            .dash-sidebar { display: none; }
        }
    </style>
</head>
<body>

<!-- Nav -->
<nav>
    <div class="inner">
        <img src="/images/plutopay.svg" alt="PlutoPay" style="height:80px">
        <div class="links">
            <a href="#products">Products</a>
            <a href="#developers">Developers</a>
            <a href="#pricing">Pricing</a>
            <a href="/docs" class="btn-nav-outline">Documentation</a>
            <a href="/login" class="btn-nav">Sign in</a>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-gradient"></div>
    <div class="hero-gradient-2"></div>
    <h1>Financial infrastructure to <span class="highlight">grow your revenue</span></h1>
    <p>Accept payments, send payouts, and manage your business finances. From small shops to enterprise platforms.</p>
    <div class="hero-actions">
        <a href="/register" class="btn-hero">Start now →</a>
        <a href="/contact" class="btn-hero-alt">Contact sales</a>
    </div>
    <div class="hero-sub-links">
        <a href="/docs">Documentation <span class="arrow">→</span></a>
        <a href="#pricing">View pricing <span class="arrow">→</span></a>
    </div>

    <!-- Dashboard Mockup -->
    <div class="hero-visual">
        <div class="hero-dashboard">
            <div class="dash-topbar">
                <div class="dash-dot"></div>
                <div class="dash-dot"></div>
                <div class="dash-dot"></div>
                <div class="dash-url">plutopay.com/dashboard</div>
            </div>
            <div class="dash-body">
                <div class="dash-sidebar">
                    <div class="si active"><div class="dot"></div> Dashboard</div>
                    <div class="si"><div class="dot"></div> Transactions</div>
                    <div class="si"><div class="dot"></div> Customers</div>
                    <div class="si"><div class="dot"></div> Terminals</div>
                    <div class="si"><div class="dot"></div> Payouts</div>
                    <div class="si"><div class="dot"></div> POS</div>
                    <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border)">
                        <div class="si"><div class="dot"></div> API Keys</div>
                        <div class="si"><div class="dot"></div> Webhooks</div>
                        <div class="si"><div class="dot"></div> Settings</div>
                    </div>
                </div>
                <div class="dash-main">
                    <div class="dash-stats">
                        <div class="dash-stat">
                            <div class="label">Total Volume</div>
                            <div class="val">$84,291</div>
                        </div>
                        <div class="dash-stat">
                            <div class="label">Transactions</div>
                            <div class="val">1,847</div>
                        </div>
                        <div class="dash-stat">
                            <div class="label">Growth</div>
                            <div class="val green">+23.5%</div>
                        </div>
                    </div>
                    <div class="dash-chart">
                        <svg viewBox="0 0 600 120" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="cg" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.15"/>
                                    <stop offset="100%" stop-color="var(--primary)" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <path d="M0,100 C50,90 100,70 150,75 C200,80 250,40 300,45 C350,50 400,25 450,30 C500,35 550,10 600,15 L600,120 L0,120Z" fill="url(#cg)"/>
                            <path d="M0,100 C50,90 100,70 150,75 C200,80 250,40 300,45 C350,50 400,25 450,30 C500,35 550,10 600,15" fill="none" stroke="var(--primary)" stroke-width="2.5"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Products -->
<section id="products" style="background:var(--bg-light)">
    <div class="section-inner">
        <div class="fade-in">
            <div class="section-label"><div class="bar"></div> Products</div>
            <h2 class="section-title">The tools you need, all in one place</h2>
            <p class="section-desc">From in-store payments to online checkout, everything works together seamlessly.</p>
        </div>
        <div class="products-grid">
            <div class="product-card fade-in">
                <div class="icon-box" style="background:rgba(99,91,255,0.08)">💳</div>
                <h3>POS & Terminal Payments</h3>
                <p>Accept tap, chip, swipe, and QR payments in-store. Manage your terminal fleet, track status, and process payments in real-time.</p>
                <a href="#" class="learn">Learn more <span class="arrow">→</span></a>
            </div>
            <div class="product-card fade-in">
                <div class="icon-box" style="background:rgba(0,212,170,0.08)">🌐</div>
                <h3>Online Payments</h3>
                <p>Accept cards, wallets, and bank transfers. Apple Pay, Google Pay, and 50+ currencies supported out of the box.</p>
                <a href="#" class="learn">Learn more <span class="arrow">→</span></a>
            </div>
            <div class="product-card fade-in">
                <div class="icon-box" style="background:rgba(255,122,0,0.08)">🔗</div>
                <h3>Payment Links</h3>
                <p>Generate shareable payment links in seconds. Send via SMS, email, or WhatsApp. No code required.</p>
                <a href="#" class="learn">Learn more <span class="arrow">→</span></a>
            </div>
            <div class="product-card fade-in">
                <div class="icon-box" style="background:rgba(255,73,219,0.08)">📊</div>
                <h3>Analytics & Reporting</h3>
                <p>Real-time dashboards with revenue trends, transaction breakdowns by method, terminal, and location. Export to CSV anytime.</p>
                <a href="#" class="learn">Learn more <span class="arrow">→</span></a>
            </div>
        </div>
    </div>
</section>

<!-- Dark Stats Band -->
<div class="dark-band">
    <div class="section-inner" style="text-align:center">
        <div class="fade-in">
            <div class="section-label"><div class="bar"></div> The backbone of modern commerce</div>
            <h2 class="section-title" style="color:white;margin:0 auto;max-width:600px">Infrastructure you can rely on</h2>
        </div>
        <div class="dark-stats fade-in">
            <div class="ds-item"><h3>99.99%</h3><p>Uptime guarantee</p></div>
            <div class="ds-item"><h3>50+</h3><p>Supported currencies</p></div>
            <div class="ds-item"><h3>&lt;200ms</h3><p>Avg. API latency</p></div>
            <div class="ds-item"><h3>PCI-L1</h3><p>Compliance certified</p></div>
        </div>
    </div>
</div>

<!-- Developer Section -->
<section id="developers" class="code-section">
    <div class="section-inner">
        <div class="code-grid">
            <div class="fade-in">
                <div class="section-label"><div class="bar"></div> For Developers</div>
                <h2 class="section-title">Designed for developers</h2>
                <p class="section-desc">RESTful APIs, versioned endpoints, and SDKs for every major language. A sandbox environment that mirrors production exactly.</p>
                <div class="sdk-tags">
                    <span class="sdk-tag">Node.js</span>
                    <span class="sdk-tag">Python</span>
                    <span class="sdk-tag">PHP</span>
                    <span class="sdk-tag">Ruby</span>
                    <span class="sdk-tag">Go</span>
                    <span class="sdk-tag">Java</span>
                </div>
            </div>
            <div class="fade-in">
                <div class="code-block">
                    <div class="code-header"><span></span><span></span><span></span></div>
                    <pre class="code-pre"><span class="cm">// Create a payment</span>
<span class="kw">const</span> payment = <span class="kw">await</span> plutopay.payments.<span class="fn">create</span>({
  <span class="fn">amount</span>: <span class="num">5000</span>,
  <span class="fn">currency</span>: <span class="str">'usd'</span>,
  <span class="fn">payment_method</span>: <span class="str">'card_present'</span>,
  <span class="fn">terminal</span>: <span class="str">'tmr_nexo_001'</span>,
});

<span class="cm">// → status: "succeeded"</span>
<span class="cm">// → id: "pay_2xK9f3mNqR..."</span></pre>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing -->
<section id="pricing">
    <div class="section-inner" style="text-align:center">
        <div class="fade-in">
            <div class="section-label" style="justify-content:center"><div class="bar"></div> Pricing</div>
            <h2 class="section-title" style="margin:0 auto">Simple, transparent pricing</h2>
            <p class="section-desc" style="margin:0.75rem auto 0">Start free and scale as you grow. No hidden fees.</p>
        </div>
        <div class="pricing-grid fade-in">
            <div class="price-card">
                <h4>Starter</h4>
                <div class="amount">Free</div>
                <p class="subdesc">For small shops getting started.</p>
                <ul>
                    <li>1 POS terminal</li>
                    <li>Up to $10K/mo volume</li>
                    <li>Basic dashboard</li>
                    <li>Email support</li>
                    <li>3% per transaction</li>
                </ul>
                <button class="btn-price">Get started</button>
            </div>
            <div class="price-card pop">
                <div class="pop-label">POPULAR</div>
                <h4>Business</h4>
                <div class="amount">$49 <span>/mo</span></div>
                <p class="subdesc">For growing businesses.</p>
                <ul>
                    <li>Unlimited terminals</li>
                    <li>Unlimited volume</li>
                    <li>Advanced analytics</li>
                    <li>API + Webhooks</li>
                    <li>2.5% per transaction</li>
                </ul>
                <button class="btn-price">Start free trial</button>
            </div>
            <div class="price-card">
                <h4>Enterprise</h4>
                <div class="amount">Custom</div>
                <p class="subdesc">For large-scale operations.</p>
                <ul>
                    <li>Everything in Business</li>
                    <li>Custom rates</li>
                    <li>Dedicated manager</li>
                    <li>99.99% SLA</li>
                    <li>Custom integrations</li>
                </ul>
                <button class="btn-price">Contact sales</button>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-final">
    <h2>Ready to get started?</h2>
    <p>Create your account in minutes. No contracts, no setup fees.</p>
    <a href="/register" class="btn-cta">Start now →</a>
</section>

<!-- Footer -->
<footer>
    <p>© 2026 PlutoPay by Sadeim Inc. All rights reserved.</p>
</footer>

<script>
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
</script>
</body>
</html>
