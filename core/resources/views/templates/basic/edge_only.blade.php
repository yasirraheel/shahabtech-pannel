<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ gs()->siteName('Microsoft Edge Required') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #070a12;
            color: #cbd5e1;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Glow Background */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(0, 120, 215, 0.15) 0%, rgba(99, 102, 241, 0.08) 50%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 0;
        }

        .edge-card {
            background: rgba(17, 24, 39, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 45px 35px;
            max-width: 560px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(16px);
            position: relative;
            z-index: 1;
        }

        .logo-container {
            margin-bottom: 30px;
        }

        .logo-container img {
            max-height: 48px;
            object-fit: contain;
        }

        .browser-icon-wrapper {
            position: relative;
            width: 96px;
            height: 96px;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .browser-icon-wrapper::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: conic-gradient(from 180deg, #0078d4, #00bcf2, #0078d4);
            opacity: 0.35;
            filter: blur(8px);
            animation: pulse-ring 3s ease-in-out infinite;
        }

        @keyframes pulse-ring {
            0%, 100% { transform: scale(1); opacity: 0.35; }
            50% { transform: scale(1.08); opacity: 0.6; }
        }

        .browser-icon-inner {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: #111827;
            border: 2px solid rgba(0, 120, 212, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .edge-svg {
            width: 58px;
            height: 58px;
        }

        h2.title {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 14px;
            letter-spacing: -0.5px;
        }

        p.subtitle {
            color: #94a3b8;
            font-size: 14.5px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .alert-box {
            background: rgba(0, 120, 212, 0.08);
            border: 1px solid rgba(0, 120, 212, 0.25);
            border-left: 4px solid #0078d4;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 30px;
            text-align: left;
            font-size: 13.5px;
            color: #e2e8f0;
        }

        .alert-box i {
            color: #0078d4;
            font-size: 18px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .btn-edge-primary {
            background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);
            color: #ffffff !important;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 10px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 120, 212, 0.35);
            text-decoration: none;
        }

        .btn-edge-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 120, 212, 0.5);
            color: #ffffff !important;
        }

        .btn-edge-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1 !important;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            font-size: 14.5px;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-top: 12px;
            cursor: pointer;
        }

        .btn-edge-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff !important;
        }

        .copy-toast {
            display: none;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #10b981;
            color: #ffffff;
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
            z-index: 999;
        }
    </style>
</head>

<body>
    <div class="edge-card">
        <!-- Logo -->
        <div class="logo-container">
            <img src="{{ siteLogo() }}" alt="{{ gs('site_name') }}">
        </div>

        <!-- Microsoft Edge Icon -->
        <div class="browser-icon-wrapper">
            <div class="browser-icon-inner">
                <!-- Official Microsoft Edge SVG Logo -->
                <svg class="edge-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 0C22.3858 0 0 22.3858 0 50C0 77.6142 22.3858 100 50 100C77.6142 100 100 77.6142 100 50C100 22.3858 77.6142 0 50 0Z" fill="transparent"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M50.407 19.98C49.93 19.98 49.457 20.007 48.989 20.06C32.32 21.946 19.789 36.19 19.789 53.475C19.789 72.106 34.894 87.211 53.525 87.211C63.811 87.211 72.973 82.597 79.112 75.335C77.408 75.877 75.59 76.17 73.702 76.17C60.297 76.17 49.431 65.304 49.431 51.899C49.431 46.126 51.448 40.82 54.821 36.657C53.398 36.425 51.921 36.303 50.407 36.303C42.062 36.303 35.298 43.067 35.298 51.412C35.298 59.757 42.062 66.521 50.407 66.521C53.791 66.521 56.91 65.405 59.436 63.527C55.086 67.587 49.255 70.087 42.846 70.087C29.627 70.087 18.91 59.37 18.91 46.151C18.91 33.42 28.847 23.01 41.385 22.253C44.254 22.079 47.262 22.518 50.407 23.57C64.045 28.131 73.189 41.258 73.189 56.402C73.189 60.106 72.483 63.649 71.196 66.906C78.472 61.272 83.189 52.486 83.189 42.607C83.189 24.621 68.393 19.98 50.407 19.98Z" fill="url(#paint0_linear)"/>
                    <defs>
                        <linearGradient id="paint0_linear" x1="18.91" y1="19.98" x2="83.189" y2="87.211" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#0078D4"/>
                            <stop offset="0.5" stop-color="#00BCF2"/>
                            <stop offset="1" stop-color="#24C17A"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Heading -->
        <h2 class="title">@lang('Microsoft Edge Required')</h2>
        
        <p class="subtitle">
            @lang('This platform is exclusively restricted to and optimized for') <strong class="text-white">Microsoft Edge</strong>. @lang('To access your tools and account securely, please open this site in Microsoft Edge.')
        </p>

        <!-- Alert info -->
        <div class="alert-box">
            <i class="las la-shield-alt"></i>
            <span>@lang('Our access extension and secure session engine require Microsoft Edge to deliver uninterrupted service.')</span>
        </div>

        <!-- Action buttons -->
        <div class="actions">
            <!-- Direct Protocol Launcher for Edge on Windows -->
            <a href="microsoft-edge:{{ url()->current() == route('edge.only') ? url('/') : url()->current() }}" class="btn-edge-primary">
                <i class="fab fa-edge"></i> @lang('Open Directly in Microsoft Edge')
            </a>

            <!-- Download Button -->
            <a href="https://www.microsoft.com/edge" target="_blank" class="btn-edge-secondary">
                <i class="las la-download"></i> @lang('Download Microsoft Edge')
            </a>

            <!-- Copy Link Button -->
            <button type="button" class="btn-edge-secondary" onclick="copyPortalUrl()">
                <i class="las la-copy"></i> <span id="copyBtnText">@lang('Copy Website Link')</span>
            </button>
        </div>
    </div>

    <div id="copyToast" class="copy-toast">
        <i class="las la-check-circle me-1"></i> @lang('Link copied to clipboard! Paste it into Microsoft Edge.')
    </div>

    <script>
        function copyPortalUrl() {
            var url = "{{ url('/') }}";
            navigator.clipboard.writeText(url).then(function() {
                var toast = document.getElementById('copyToast');
                toast.style.display = 'block';
                document.getElementById('copyBtnText').innerText = '@lang("Copied!")';
                setTimeout(function() {
                    toast.style.display = 'none';
                    document.getElementById('copyBtnText').innerText = '@lang("Copy Website Link")';
                }, 3000);
            }).catch(function() {
                prompt('@lang("Copy this link and open it in Microsoft Edge:")', url);
            });
        }
    </script>
</body>

</html>
