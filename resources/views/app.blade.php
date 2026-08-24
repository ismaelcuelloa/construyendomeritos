<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="font-size: 16px;" class="sizes customelements history pointerevents postmessage webgl websockets cssanimations csscolumns csscolumns-width csscolumns-span csscolumns-fill csscolumns-gap csscolumns-rule csscolumns-rulecolor csscolumns-rulestyle csscolumns-rulewidth csscolumns-breakbefore csscolumns-breakafter csscolumns-breakinside flexbox picture srcset webworkers">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="color-scheme" content="light only">
        
        <!-- SEO Meta Tags -->
        <meta name="description" content="Material de estudio para la Procuraduría General de la Nación 2026 - Construyendo Méritos con Excelencia. Simulacros, guías y recursos actualizados para alcanzar tu cargo público ideal en la Procuraduría.">
        <meta name="keywords" content="Procuraduría General Nación 2026, concurso Procuraduría, material estudio Procuraduría, simulacros Procuraduría, guías Procuraduría, Construyendo Méritos con Excelencia, cargo público Procuraduría">
        <meta name="author" content="Construyendo Méritos con Excelencia">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <meta name="theme-color" content="#ffffff">
        <meta name="googlebot" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Construyendo Méritos con Excelencia">
        <meta property="og:title" content="Materiales de estudio - Procuraduría General de la Nación 2026 | Construyendo Méritos con Excelencia">
        <meta property="og:description" content="Materiales de estudio para la Procuraduría General de la Nación 2026 - Construyendo Méritos con Excelencia. Simulacros y guías actualizadas para tu preparación.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('favicon.svg') }}">
        <meta property="og:image:secure_url" content="{{ secure_asset('favicon.svg') }}">
        <meta property="og:image:width" content="512">
        <meta property="og:image:height" content="512">
        <meta property="og:image:alt" content="Logo de Construyendo Méritos con Excelencia">
        <meta property="og:locale" content="es_CO">
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@construyendomeritos">
        <meta name="twitter:title" content="Materiales de estudio - Procuraduría General de la Nación 2026 | Construyendo Méritos con Excelencia">
        <meta name="twitter:description" content="Materiales de estudio, simulacros y guías para la Procuraduría General de la Nación 2026 - Construyendo Méritos con Excelencia.">
        <meta name="twitter:image" content="{{ asset('favicon.svg') }}">
        <meta name="twitter:image:alt" content="Logo de Construyendo Méritos con Excelencia">
        
        <!-- Verificación (agregar cuando estén disponibles) -->
        <!-- <meta name="google-site-verification" content="YOUR_VERIFICATION_CODE"> -->
        <!-- <meta name="facebook-domain-verification" content="YOUR_VERIFICATION_CODE"> -->
        
        <!-- Structured Data (JSON-LD) para Google -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "EducationalOrganization",
            "name": "Construyendo Méritos con Excelencia",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('favicon.svg') }}",
            "description": "Material de estudio especializado para la Procuraduría General de la Nación 2026. Simulacros, guías y recursos actualizados de Construyendo Méritos con Excelencia.",
            "sameAs": [
                "https://www.facebook.com/guiasysimulacros",
                "https://twitter.com/guiasysimulacros",
                "https://www.instagram.com/guiasysimulacros"
            ],
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "CO"
            },
            "potentialAction": {
                "@type": "SearchAction",
                "target": "{{ url('/') }}/buscar?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {

            }

            html.dark {

            }
        </style>

        <title inertia>{{ config('app.name', 'Construyendo Méritos con Excelencia') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

        <!-- CSS
    	============================================ -->
        <!-- Critical CSS: Bootstrap and main styles -->
        <link rel="stylesheet" href="{{ asset('assets/css/vendor/bootstrap.min.css') }}">
        
        <!-- Non-critical CSS: Load with lower priority -->
        <link rel="stylesheet" href="{{ asset('assets/css/vendor/slick.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/vendor/slick-theme.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/sal.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/feather.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/fontawesome.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/euclid-circulara.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/swiper.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/odometer.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/animation.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-select.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/jquery-ui.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/magnigy-popup.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/plyr.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/jodit.min.css') }}" media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="{{ asset('assets/css/vendor/slick.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/vendor/slick-theme.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/sal.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/feather.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/fontawesome.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/euclid-circulara.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/swiper.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/odometer.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/animation.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-select.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/jquery-ui.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/magnigy-popup.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/plyr.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/plugins/jodit.min.css') }}">
        </noscript>

        <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="rbt-header-sticky">
        @inertia




        <!-- JS
    ============================================ -->
        <!-- Critical JS -->
        <script src="{{ asset('assets/js/vendor/modernizr.min.js') }}"></script>
        <script src="{{ asset('assets/js/vendor/jquery.js') }}"></script>
        <script src="{{ asset('assets/js/vendor/bootstrap.min.js') }}"></script>
        
        <!-- Deferred JS -->
        <script defer src="{{ asset('assets/js/vendor/sal.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/js.cookie.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/jquery.style.switcher.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/swiper.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/jquery-appear.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/odometer.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/backtotop.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/isotop.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/imageloaded.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/wow.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/waypoint.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/easypie.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/jquery-one-page-nav.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/bootstrap-select.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/jquery-ui.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/magnify-popup.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/paralax-scroll.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/paralax.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/countdown.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/plyr.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/jodit.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/vendor/Sortable.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/main.js') }}"></script>
        <script defer src="https://checkout.epayco.co/checkout.js"></script>
        <script src="https://checkout.wompi.co/widget.js"></script>
    </body>


</html>
