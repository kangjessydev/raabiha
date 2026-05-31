<?php
/**
 * Template Name: Raabiha Dashboard
 *
 * Isolated full-page template for the Vue.js 3 admin dashboard.
 * This template bypasses the WordPress theme header/footer completely
 * and mounts the Vue SPA directly.
 *
 * Security: Non-logged-in users are redirected to the WP login page.
 * The Vue app communicates with WP backend via wp_ajax_* endpoints.
 *
 * @package RaabihaTheme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// ── Session Protection ────────────────────────────────────────────
// Redirect unauthenticated users to the WPS Hide Login custom URL.
// We avoid auth_redirect() here because it points to wp-login.php
// which is blocked/renamed by WPS Hide Login — causing a redirect loop.
// ── Session Protection ────────────────────────────────────────────
// Redirect unauthenticated users to the WPS Hide Login custom URL.
// We avoid auth_redirect() here because it points to wp-login.php
// which is blocked/renamed by WPS Hide Login — causing a redirect loop.
if ( ! is_user_logged_in() ) {
    $redirect_to  = urlencode( home_url( '/dashboard/' ) );
    $login_url    = home_url( '/admin/login/?redirect_to=' . $redirect_to );
    wp_safe_redirect( $login_url, 302 );
    exit;
}

// Security Check: Only allow administrator, raabiha_owner, shop_manager, raabiha_cashier, and raabiha_blogger roles in the custom admin shell
$current_user = wp_get_current_user();
$allowed_dashboard_roles = array( 'administrator', 'raabiha_owner', 'shop_manager', 'raabiha_cashier', 'raabiha_blogger' );
$is_allowed_dashboard = false;
foreach ( $current_user->roles as $role ) {
    if ( in_array( $role, $allowed_dashboard_roles ) ) {
        $is_allowed_dashboard = true;
        break;
    }
}
if ( ! $is_allowed_dashboard ) {
    $my_account_url = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
    if ( ! $my_account_url ) {
        $my_account_url = home_url( '/my-account/' );
    }
    wp_safe_redirect( $my_account_url, 302 );
    exit;
}

// ── Enqueue Dashboard Assets (via wp_head hook) ───────────────────
// wp_enqueue_scripts already conditionally loads based on template
// We still call wp_head() here only to process enqueued assets.
// No theme output is generated — we use our own HTML shell.
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="raabiha-dashboard-root">

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Raabiha Store Dashboard — Kelola produk, pesanan, dan SEO dalam satu tampilan.">
    <title>Dashboard — <?php bloginfo( 'name' ); ?></title>

    <!-- Preconnect for fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>

    <?php
    // This outputs all wp_enqueue_style() calls registered in functions.php
    wp_head();
    ?>

    <!-- Dashboard Inline Styles (critical path, no FOUC) -->
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --color-primary:    #6366f1;
            --color-secondary:  #8b5cf6;
            --color-accent:     #06b6d4;
            --color-surface:    #0f172a;
            --color-surface-2:  #1e293b;
            --color-surface-3:  #334155;
            --color-text:       #f1f5f9;
            --color-text-muted: #94a3b8;
            --color-border:     #334155;
            --color-success:    #10b981;
            --color-warning:    #f59e0b;
            --color-danger:     #ef4444;
            --font-sans:        'Poppins', system-ui, sans-serif;
        }
        html, body {
            height: 100%;
            background: #FAF7F0;
            color: #222523;
            -webkit-font-smoothing: antialiased;
        }
        #app {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .raabiha-loader {
            position: fixed;
            inset: 0;
            display: flex;
            background: #FAF7F0;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }
        .raabiha-loader-sidebar {
            width: 16rem; /* 64 */
            background-color: white;
            border-right: 1px solid #e5e5e5;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .raabiha-loader-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>

<body class="raabiha-dashboard-body">

    <!-- Pre-mount loading indicator (hidden once Vue mounts) -->
    <div class="raabiha-loader" id="raabiha-loader">
        <aside class="raabiha-loader-sidebar">
            <div style="padding: 1.5rem 1.5rem; border-bottom: 1px solid #e5e5e5;">
                <div class="animate-pulse" style="height: 2rem; width: 8rem; background-color: #E5E1D8; border-radius: 0.25rem;"></div>
            </div>
            <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="animate-pulse" style="height: 1.5rem; width: 60%; background-color: #E5E1D8; border-radius: 0.25rem;"></div>
                <div class="animate-pulse" style="height: 1.5rem; width: 80%; background-color: #E5E1D8; border-radius: 0.25rem;"></div>
                <div class="animate-pulse" style="height: 1.5rem; width: 70%; background-color: #E5E1D8; border-radius: 0.25rem;"></div>
                <div class="animate-pulse" style="height: 1.5rem; width: 50%; background-color: #E5E1D8; border-radius: 0.25rem;"></div>
                <div class="animate-pulse" style="height: 1.5rem; width: 75%; background-color: #E5E1D8; border-radius: 0.25rem;"></div>
            </div>
        </aside>
        <main class="raabiha-loader-main">
            <header style="height: 5rem; background-color: white; border-bottom: 1px solid #e5e5e5; display: flex; align-items: center; padding: 0 2rem; justify-content: flex-end;">
                <div class="animate-pulse" style="height: 2.5rem; width: 2.5rem; border-radius: 9999px; background-color: #E5E1D8;"></div>
            </header>
            <div style="padding: 3rem 2.5rem; flex: 1; overflow-y: auto;">
                <div class="animate-pulse" style="height: 3rem; width: 16rem; background-color: #E5E1D8; border-radius: 0.25rem; margin-bottom: 2rem;"></div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 3rem;">
                    <div class="animate-pulse" style="height: 8rem; background-color: white; border: 1px solid #e5e5e5; border-radius: 0.25rem;"></div>
                    <div class="animate-pulse" style="height: 8rem; background-color: white; border: 1px solid #e5e5e5; border-radius: 0.25rem;"></div>
                    <div class="animate-pulse" style="height: 8rem; background-color: white; border: 1px solid #e5e5e5; border-radius: 0.25rem;"></div>
                    <div class="animate-pulse" style="height: 8rem; background-color: white; border: 1px solid #e5e5e5; border-radius: 0.25rem;"></div>
                </div>
                <div class="animate-pulse" style="height: 24rem; background-color: white; border: 1px solid #e5e5e5; border-radius: 0.25rem;"></div>
            </div>
        </main>
    </div>

    <!-- Vue 3 SPA Mount Point -->
    <div id="app"></div>

    <?php
    // Output all wp_enqueue_script() calls (including raabiha-dashboard)
    wp_footer();
    ?>

    <script>
        // Hide loader once Vue finishes mounting
        document.addEventListener('DOMContentLoaded', function () {
            // Vue will dispatch this custom event from App.vue mounted() hook
            window.addEventListener('raabiha:app-mounted', function () {
                var loader = document.getElementById('raabiha-loader');
                if (loader) {
                    loader.style.opacity = '0';
                    setTimeout(function () {
                        loader.remove();
                    }, 300);
                }
            });

            // Fallback: remove loader after 4s in case Vue event fires before listener
            setTimeout(function () {
                var loader = document.getElementById('raabiha-loader');
                if (loader) loader.remove();
            }, 4000);
        });
    </script>
</body>
</html>

