<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- GLOBAL LAYOUT STYLES -->
        <style>
            :root {
                --primary-orange: #dc2626;
                --primary-dark-orange: #991b1b;
                --success-green: #059669;
                --text-primary: #1e293b;
                --text-secondary: #64748b;
                --border-color: #e2e8f0;
                --bg-light: #f8fafc;
                --error-red: #dc2626;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            html {
                font-size: 12px;
                height: 100%;
                width: 100%;
            }

            body {
                height: 100%;
                width: 100%;
                margin: 0;
                padding: 0;
                font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                line-height: 1.6;
                background-color: #f8fafc;
                color: #333;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }

            /* Flexbox container for sticky footer */
            #app-container {
                display: flex;
                flex-direction: column;
                flex: 1;
                height: 100%;
                width: 100%;
            }

            /* Header already has sticky positioning in header.blade.php */
            header {
                flex-shrink: 0;
                z-index: 100;
            }

            /* Optional: Page heading section */
            .page-header {
                background-color: #ffffff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                padding: 1.5rem 1rem;
                flex-shrink: 0;
            }

            .page-header-content {
                max-width: 1280px;
                margin: 0 auto;
            }

            /* Main content container - standard padding */
            .container {
                max-width: 1280px;
                margin: 0 auto;
                padding: 2rem;
                width: 100%;
            }

            /* Main content grows to fill available space */
            main {
                flex: 1;
                padding: 0;
                margin: 0;
                width: 100%;
            }

            /* Footer always at bottom with no gaps */
            footer {
                flex-shrink: 0;
                width: 100%;
                margin: 0;
                padding: 0;
            }

            /* Section Styles - Standard spacing */
            section {
                padding: 2rem 2rem 0.5rem 2rem;
                flex-shrink: 0;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                html {
                    font-size: 12px;
                }

                main {
                    padding: 0;
                }

                .page-header {
                    padding: 1rem;
                }

                section {
                    padding: 1.5rem 1rem 0.5rem 1rem;
                }

                .container {
                    padding: 1.5rem;
                }
            }
        </style>
    </head>

    <body style="margin: 0; padding: 0; height: 100%;">
        <div id="app-container">
            <!-- HEADER (sticky at top) -->
            @include('components.header')

            <!-- OPTIONAL: Page Heading Section -->
            @isset($header)
                <div class="page-header">
                    <div class="page-header-content">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <!-- MAIN CONTENT (grows to fill space) -->
            <main>
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>

            <!-- FOOTER (always at bottom) -->
            @include('components.footer')
        </div>
    </body>
</html>
