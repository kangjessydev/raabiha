<?php
/**
 * Raabiha Theme — functions.php
 *
 * Hybrid Monolith Backend Interceptor.
 * Handles: UI masking, security hardening, WooCommerce CRUD via AJAX,
 * Yoast SEO meta injection, and Vue dashboard asset enqueue.
 *
 * @package RaabihaTheme
 * @version 1.0.0
 */

require_once get_template_directory() . '/inc/shortcodes.php';
require_once get_template_directory() . '/inc/woo-custom-meta.php';
defined( 'ABSPATH' ) || exit;

// ═══════════════════════════════════════════════════════════════
// § 1. SECURITY — Remove WP meta generator leakage
// ═══════════════════════════════════════════════════════════════

/**
 * Strip WordPress version from <head> and RSS feeds.
 * Prevents fingerprinting by bots/scanners.
 */
remove_action( 'wp_head', 'wp_generator' );

add_filter( 'the_generator', '__return_empty_string' );

// Remove WP version from scripts & styles
add_filter( 'style_loader_src',  'raabiha_remove_wp_version_query', 9999 );
add_filter( 'script_loader_src', 'raabiha_remove_wp_version_query', 9999 );

/**
 * Strip ?ver= query string from enqueued assets.
 *
 * @param string $src Asset URL.
 * @return string Cleaned URL.
 */
function raabiha_remove_wp_version_query( $src ) {
    if ( strpos( $src, 'ver=' ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}

// ═══════════════════════════════════════════════════════════════
// § 2. UI MASKING — Redirect non-admins away from WP backend
// ═══════════════════════════════════════════════════════════════

/**
 * Non-administrator users attempting to access wp-admin or wp-login.php
 * are silently redirected to the custom Vue dashboard at /dashboard.
 *
 * Exceptions:
 *  - DOING_AJAX: allow AJAX calls to pass through.
 *  - XMLRPC: excluded to avoid breaking REST consumers.
 */
add_action( 'init', 'raabiha_mask_wp_admin_ui', 1 );

function raabiha_mask_wp_admin_ui() {
    // Skip: AJAX requests
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }

    // Skip: WP-CLI
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        return;
    }

    // Skip: Cron jobs
    if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
        return;
    }

    // Skip: REST API requests
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    // Intercept logout requests: immediately logout and redirect to homepage to avoid confirmation page
    if ( isset( $_GET['action'] ) && 'logout' === $_GET['action'] ) {
        wp_logout();
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    // Detect custom login page slug set by WPS Hide Login plugin
    $custom_login_slug = get_option( 'whl_page', 'wpadmin-raabiha' );
    $request_uri       = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    $path              = rtrim( parse_url( $request_uri, PHP_URL_PATH ), '/' );

    // Normalize path by stripping site path prefix (supports WordPress in subdirectory)
    $site_path     = parse_url( home_url(), PHP_URL_PATH );
    $site_path     = $site_path ? rtrim( $site_path, '/' ) : '';
    $relative_path = $path;
    if ( ! empty( $site_path ) && strpos( $path, $site_path ) === 0 ) {
        $relative_path = substr( $path, strlen( $site_path ) );
    }
    $relative_path = '/' . ltrim( $relative_path, '/' );

    // 1. Force 404 for "/login"
    if ( $relative_path === '/login' ) {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        include get_query_template( '404' );
        exit;
    }

    // 2. Strict wp-admin and wp-login.php access control
    $pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
    if ( empty( $pagenow ) && isset( $GLOBALS['pagenow'] ) ) {
        $pagenow = $GLOBALS['pagenow'];
    }
    $is_admin_area = is_admin();
    $is_login_page = ( 'wp-login.php' === $pagenow );

    if ( $is_admin_area || $is_login_page ) {
        // Allow the custom login slug to pass through for administrators
        if ( ! empty( $custom_login_slug ) && false !== strpos( $request_uri, $custom_login_slug ) ) {
            return;
        }

        // If NOT logged in: immediately throw 404 Not Found
        if ( ! is_user_logged_in() ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            include get_query_template( '404' );
            exit;
        }

        // If logged in: ONLY allow roles 'administrator' and 'raabiha_designer' to access wp-admin or wp-login.php
        $current_user = wp_get_current_user();
        $allowed_admin_roles = array( 'administrator', 'raabiha_designer' );
        $has_admin_access = false;
        foreach ( $current_user->roles as $role ) {
            if ( in_array( $role, $allowed_admin_roles ) ) {
                $has_admin_access = true;
                break;
            }
        }

        if ( ! $has_admin_access ) {
            // Block all other roles (including owner, finance, cashier, blogger) from wp-admin and throw 404
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            include get_query_template( '404' );
            exit;
        }
    }
}

// ═══════════════════════════════════════════════════════════════
// § 2.5. CUSTOM STORE MANAGER LOGIN (/admin/login)
// ═══════════════════════════════════════════════════════════════

add_action( 'init', 'raabiha_handle_admin_login_route', 2 );

function raabiha_handle_admin_login_route() {
    // Skip: AJAX, Cron, CLI, REST
    if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }

    $request_uri   = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    $path          = rtrim( parse_url( $request_uri, PHP_URL_PATH ), '/' );

    // Normalize path by stripping site path prefix
    $site_path     = parse_url( home_url(), PHP_URL_PATH );
    $site_path     = $site_path ? rtrim( $site_path, '/' ) : '';
    $relative_path = $path;
    if ( ! empty( $site_path ) && strpos( $path, $site_path ) === 0 ) {
        $relative_path = substr( $path, strlen( $site_path ) );
    }
    $relative_path = '/' . ltrim( $relative_path, '/' );

    if ( $relative_path === '/admin/login' ) {
        // If already logged in, redirect to correct dashboard based on role
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            if ( in_array( 'administrator', $user->roles ) ) {
                wp_safe_redirect( admin_url(), 302 );
                exit;
            } elseif ( in_array( 'shop_manager', $user->roles ) ) {
                wp_safe_redirect( home_url( '/dashboard/' ), 302 );
                exit;
            } else {
                wp_safe_redirect( home_url( '/my-account/' ), 302 );
                exit;
            }
        }

        $error_message = '';

        // Process POST login credentials
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            if ( ! isset( $_POST['raabiha_login_nonce'] ) || ! wp_verify_nonce( $_POST['raabiha_login_nonce'], 'raabiha_store_login_action' ) ) {
                $error_message = 'Validasi keamanan kedaluwarsa. Silakan muat ulang halaman.';
            } else {
                $creds = array(
                    'user_login'    => isset( $_POST['log'] ) ? sanitize_text_field( $_POST['log'] ) : '',
                    'user_password' => isset( $_POST['pwd'] ) ? $_POST['pwd'] : '',
                    'remember'      => isset( $_POST['rememberme'] ) ? true : false,
                );

                $user = wp_signon( $creds, false );

                if ( is_wp_error( $user ) ) {
                    $error_message = 'Username atau password yang Anda masukkan salah.';
                } else {
                    // Success: check role and redirect
                    if ( in_array( 'administrator', $user->roles ) ) {
                        wp_safe_redirect( admin_url(), 302 );
                        exit;
                    } elseif ( in_array( 'shop_manager', $user->roles ) ) {
                        wp_safe_redirect( home_url( '/dashboard/' ), 302 );
                        exit;
                    } else {
                        // Redirect standard customer to My Account
                        wp_safe_redirect( home_url( '/my-account/' ), 302 );
                        exit;
                    }
                }
            }
        }

        raabiha_render_admin_login_page( $error_message );
        exit;
    }
}

function raabiha_render_admin_login_page( $error_message = '' ) {
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <title>Masuk Store Manager — <?php bloginfo( 'name' ); ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Outfit', sans-serif;
                background-color: #0b0f19;
                color: #f1f5f9;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
                position: relative;
                overflow: hidden;
            }
            /* Background glow */
            .glow-1 {
                position: absolute;
                top: -10%;
                left: -10%;
                width: 40vw;
                height: 40vw;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
                pointer-events: none;
            }
            .glow-2 {
                position: absolute;
                bottom: -10%;
                right: -10%;
                width: 40vw;
                height: 40vw;
                background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
                pointer-events: none;
            }
            .login-card {
                background: linear-gradient(145deg, #131926 0%, #0f131f 100%);
                border: 1px solid rgba(255, 255, 255, 0.05);
                border-radius: 1.25rem;
                padding: 2.5rem 2.25rem;
                width: 100%;
                max-width: 420px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
                position: relative;
                z-index: 10;
            }
            .brand-header {
                text-align: center;
                margin-bottom: 2rem;
            }
            .brand-logo {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 3rem;
                height: 3rem;
                border-radius: 0.75rem;
                background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
                color: #ffffff;
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
                box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
            }
            .brand-title {
                font-size: 1.5rem;
                font-weight: 700;
                background: linear-gradient(135deg, #ffffff 40%, #a5b4fc 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .brand-subtitle {
                font-size: 0.85rem;
                color: #64748b;
                margin-top: 0.25rem;
            }
            .error-box {
                background-color: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.25);
                color: #f87171;
                font-size: 0.8rem;
                padding: 0.75rem 1rem;
                border-radius: 0.5rem;
                margin-bottom: 1.5rem;
                line-height: 1.4;
            }
            .form-group {
                margin-bottom: 1.25rem;
            }
            .form-label {
                display: block;
                font-size: 0.8rem;
                font-weight: 500;
                color: #94a3b8;
                margin-bottom: 0.5rem;
            }
            .form-input {
                width: 100%;
                background-color: #0b0f19;
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 0.625rem;
                padding: 0.75rem 1rem;
                color: #f1f5f9;
                font-family: inherit;
                font-size: 0.9rem;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            .form-input:focus {
                outline: none;
                border-color: #6366f1;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            }
            .form-options {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 1.5rem;
                margin-bottom: 1.75rem;
                font-size: 0.8rem;
            }
            .remember-me {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #94a3b8;
                cursor: pointer;
            }
            .remember-me input {
                accent-color: #6366f1;
            }
            .btn-submit {
                width: 100%;
                background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
                color: #ffffff;
                font-family: inherit;
                font-size: 0.9rem;
                font-weight: 600;
                padding: 0.8rem;
                border: none;
                border-radius: 0.625rem;
                cursor: pointer;
                transition: transform 0.2s, box-shadow 0.2s;
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
            }
            .btn-submit:hover {
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            }
            .btn-submit:active {
                transform: translateY(0);
            }
            .back-home {
                text-align: center;
                margin-top: 1.5rem;
                font-size: 0.8rem;
            }
            .back-home a {
                color: #64748b;
                text-decoration: none;
                transition: color 0.2s;
            }
            .back-home a:hover {
                color: #818cf8;
            }
        </style>
    </head>
    <body>
        <div class="glow-1"></div>
        <div class="glow-2"></div>

        <div class="login-card">
            <div class="brand-header">
                <div class="brand-logo">R</div>
                <h1 class="brand-title">Raabiha</h1>
                <p class="brand-subtitle">Masuk Akun Store Manager</p>
            </div>

            <?php if ( ! empty( $error_message ) ) : ?>
                <div class="error-box"><?php echo esc_html( $error_message ); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php wp_nonce_field( 'raabiha_store_login_action', 'raabiha_login_nonce' ); ?>
                
                <div class="form-group">
                    <label class="form-label" for="username">Username / Email</label>
                    <input class="form-input" type="text" name="log" id="username" required autocomplete="username" placeholder="Masukkan username Anda">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-input" type="password" name="pwd" id="password" required autocomplete="current-password" placeholder="••••••••">
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="rememberme" id="rememberme" value="forever">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">Masuk Dashboard</button>
            </form>

            <div class="back-home">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">← Kembali ke Beranda Toko</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// ═══════════════════════════════════════════════════════════════
// § 2.7. CUSTOMIZE MASTER WP-LOGIN & LOGOUT BRANDING
// ═══════════════════════════════════════════════════════════════

add_action( 'login_enqueue_scripts', 'raabiha_customize_wp_login_style' );
function raabiha_customize_wp_login_style() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style type="text/css">
        body.login {
            background-color: #0b0f19 !important;
            color: #f1f5f9 !important;
            font-family: 'Outfit', sans-serif !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 100vh !important;
            position: relative !important;
            overflow: hidden !important;
            padding: 0 !important;
        }
        body.login::before {
            content: "" !important;
            position: absolute !important;
            top: -10%;
            left: -10%;
            width: 45vw !important;
            height: 45vw !important;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, rgba(0, 0, 0, 0) 70%) !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }
        body.login::after {
            content: "" !important;
            position: absolute !important;
            bottom: -10%;
            right: -10%;
            width: 45vw !important;
            height: 45vw !important;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.12) 0%, rgba(0, 0, 0, 0) 70%) !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }
        #login {
            position: relative !important;
            z-index: 10 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 420px !important;
            margin: auto !important;
        }
        /* Replace WP Logo with Raabiha Brand Logo & Title */
        body.login h1 a {
            background-image: none !important;
            text-indent: 0 !important;
            width: 100% !important;
            height: auto !important;
            font-size: 0 !important;
            color: #ffffff !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.75rem !important;
            margin-bottom: 2rem !important;
            pointer-events: none !important;
        }
        body.login h1 a::before {
            content: "R" !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 3rem !important;
            height: 3rem !important;
            border-radius: 0.75rem !important;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            color: #ffffff !important;
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3) !important;
        }
        body.login h1 a::after {
            content: "Raabiha Admin" !important;
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            background: linear-gradient(135deg, #ffffff 40%, #a5b4fc 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }
        /* Style Login Form Container */
        body.login form {
            background: linear-gradient(145deg, #131926 0%, #0f131f 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            border-radius: 1.25rem !important;
            padding: 2.5rem 2.25rem !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5) !important;
            margin-top: 0 !important;
        }
        /* Label styling */
        body.login label {
            color: #94a3b8 !important;
            font-size: 0.8rem !important;
            font-weight: 500 !important;
            margin-bottom: 0.5rem !important;
        }
        /* Input fields styling */
        body.login .input {
            width: 100% !important;
            background-color: #0b0f19 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 0.625rem !important;
            padding: 0.75rem 1rem !important;
            color: #f1f5f9 !important;
            font-family: inherit !important;
            font-size: 0.9rem !important;
            box-shadow: none !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
        }
        body.login .input:focus {
            outline: none !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
        }
        /* Primary Login Button styling */
        body.login .button-primary {
            width: 100% !important;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            color: #ffffff !important;
            font-family: inherit !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            padding: 0.6rem 1rem !important;
            border: none !important;
            border-radius: 0.625rem !important;
            cursor: pointer !important;
            height: auto !important;
            line-height: normal !important;
            transition: transform 0.2s, box-shadow 0.2s !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25) !important;
            text-shadow: none !important;
            float: none !important;
            margin-top: 1rem !important;
        }
        body.login .button-primary:hover {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4) !important;
        }
        body.login .button-primary:active {
            transform: translateY(0) !important;
        }
        /* Navigation Links below form */
        body.login #nav, body.login #backtoblog {
            text-align: center !important;
            padding: 0 !important;
            margin: 1.25rem 0 0 0 !important;
            font-size: 0.8rem !important;
        }
        body.login #nav a, body.login #backtoblog a {
            color: #64748b !important;
            transition: color 0.2s !important;
        }
        body.login #nav a:hover, body.login #backtoblog a:hover {
            color: #818cf8 !important;
        }
        /* Hide Language switcher and privacy policy links */
        .language-switcher, .privacy-policy-page-link {
            display: none !important;
        }
        /* Alert message styling */
        body.login .message, body.login #login_error {
            background-color: rgba(239, 68, 68, 0.1) !important;
            border: 1px solid rgba(239, 68, 68, 0.25) !important;
            color: #f87171 !important;
            font-size: 0.8rem !important;
            padding: 0.75rem 1rem !important;
            border-radius: 0.5rem !important;
            box-shadow: none !important;
            margin-bottom: 1.5rem !important;
            float: none !important;
        }
        body.login .message {
            background-color: rgba(99, 102, 241, 0.1) !important;
            border-color: rgba(99, 102, 241, 0.25) !important;
            color: #a5b4fc !important;
        }
    </style>
    <?php
}

// Redirect Logo URL to Site Homepage
add_filter( 'login_headerurl', 'raabiha_login_logo_url' );
function raabiha_login_logo_url() {
    return home_url();
}

// Custom Title text for Logo
add_filter( 'login_headertext', 'raabiha_login_logo_title' );
function raabiha_login_logo_title() {
    return get_bloginfo( 'name' );
}

// Clean redirect on Logout: redirect back to home page
add_action( 'wp_logout', 'raabiha_redirect_after_logout' );
function raabiha_redirect_after_logout() {
    wp_safe_redirect( home_url( '/' ) );
    exit;
}

// ═══════════════════════════════════════════════════════════════
// § 3. THEME SETUP
// ═══════════════════════════════════════════════════════════════

add_action( 'after_setup_theme', 'raabiha_theme_setup' );

function raabiha_theme_setup() {
    // Enable WooCommerce support
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    add_theme_support( 'post-thumbnails' );
    
    // Fix blurry gallery thumbnails by forcing higher resolution size
    add_filter( 'woocommerce_gallery_thumbnail_size', function() {
        return 'woocommerce_thumbnail';
    } );

    // Title tag managed by WordPress
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-logo' );
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'raabiha' ),
        'footer' => __( 'Footer Menu', 'raabiha' )
    ) );

    // HTML5 output
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style',
    ) );

    load_theme_textdomain( 'raabiha-theme', get_template_directory() . '/languages' );
}

// add_action( 'rest_api_init', 'raabiha_remove_password_reset_endpoint' );
// ═══════════════════════════════════════════════════════════════
// 10. CUSTOM SETTINGS REST API FOR VUE DASHBOARD
// ═══════════════════════════════════════════════════════════════

add_action( 'rest_api_init', function () {
    register_rest_route( 'raabiha/v1', '/settings', array(
        array(
            'methods'             => 'GET',
            'callback'            => 'raabiha_get_settings',
            'permission_callback' => 'raabiha_verify_rest_access',
        ),
        array(
            'methods'             => 'POST',
            'callback'            => 'raabiha_update_settings',
            'permission_callback' => 'raabiha_verify_rest_access',
        )
    ));
} );

function raabiha_get_settings() {
    return rest_ensure_response( array(
        'store_name'       => get_option( 'blogname' ),
        'store_email'      => get_option( 'admin_email' ),
        'footer_about'     => get_option( 'raabiha_footer_about', 'Architectural modesty for the modern intellectual.' ),
        'footer_phone'     => get_option( 'raabiha_footer_phone', '+62 812-3456-7890' ),
        'social_ig'        => get_option( 'raabiha_social_ig', '' ),
        'social_tiktok'    => get_option( 'raabiha_social_tiktok', '' ),
        'enable_xendit'    => get_option( 'raabiha_enable_xendit', 'no' ),
        'enable_rajaongkir'=> get_option( 'raabiha_enable_rajaongkir', 'no' ),
    ) );
}

function raabiha_update_settings( WP_REST_Request $request ) {
    $params = $request->get_json_params();

    if ( isset( $params['store_name'] ) ) update_option( 'blogname', sanitize_text_field( $params['store_name'] ) );
    if ( isset( $params['store_email'] ) ) update_option( 'admin_email', sanitize_email( $params['store_email'] ) );
    if ( isset( $params['footer_about'] ) ) update_option( 'raabiha_footer_about', sanitize_textarea_field( $params['footer_about'] ) );
    if ( isset( $params['footer_phone'] ) ) update_option( 'raabiha_footer_phone', sanitize_text_field( $params['footer_phone'] ) );
    if ( isset( $params['social_ig'] ) ) update_option( 'raabiha_social_ig', sanitize_url( $params['social_ig'] ) );
    if ( isset( $params['social_tiktok'] ) ) update_option( 'raabiha_social_tiktok', sanitize_url( $params['social_tiktok'] ) );
    if ( isset( $params['enable_xendit'] ) ) update_option( 'raabiha_enable_xendit', sanitize_text_field( $params['enable_xendit'] ) );
    if ( isset( $params['enable_rajaongkir'] ) ) update_option( 'raabiha_enable_rajaongkir', sanitize_text_field( $params['enable_rajaongkir'] ) );

    return rest_ensure_response( array( 'success' => true, 'message' => 'Settings saved successfully' ) );
}

add_action( 'rest_api_init', 'raabiha_register_rest_routes' );
function raabiha_register_rest_routes() {
    // Get General Statistics
    register_rest_route( 'raabiha/v1', '/stats', array(
        'methods'             => 'GET',
        'callback'            => 'raabiha_rest_get_stats',
        'permission_callback' => 'raabiha_verify_rest_access',
    ) );

    // Get Web Settings (Header & Footer)
    register_rest_route( 'raabiha/v1', '/web-settings', array(
        'methods'             => 'GET',
        'callback'            => 'raabiha_rest_get_web_settings',
        'permission_callback' => 'raabiha_verify_rest_access',
    ) );

    // Save Web Settings (Header & Footer)
    register_rest_route( 'raabiha/v1', '/web-settings', array(
        'methods'             => 'POST',
        'callback'            => 'raabiha_rest_save_web_settings',
        'permission_callback' => 'raabiha_verify_rest_access',
    ) );
}

/**
 * Get Web Settings
 */
function raabiha_rest_get_web_settings() {
    $settings = array(
        'header_promo_text' => get_option( 'raabiha_header_promo_text', 'FREE SHIPPING ON ORDERS OVER RP 500.000' ),
        'footer_about'      => get_option( 'raabiha_footer_about', 'Raabiha adalah destinasi fashion modest premium.' ),
        'footer_email'      => get_option( 'raabiha_footer_email', 'hello@raabiha.com' ),
        'footer_phone'      => get_option( 'raabiha_footer_phone', '+62 812-3456-7890' ),
        'social_ig'         => get_option( 'raabiha_social_ig', 'https://instagram.com/raabiha' ),
        'social_tiktok'     => get_option( 'raabiha_social_tiktok', 'https://tiktok.com/@raabiha' ),
    );
    return rest_ensure_response( $settings );
}

/**
 * Save Web Settings
 */
function raabiha_rest_save_web_settings( $request ) {
    $params = $request->get_json_params();

    if ( isset( $params['header_promo_text'] ) ) {
        update_option( 'raabiha_header_promo_text', sanitize_text_field( $params['header_promo_text'] ) );
    }
    if ( isset( $params['footer_about'] ) ) {
        update_option( 'raabiha_footer_about', sanitize_textarea_field( $params['footer_about'] ) );
    }
    if ( isset( $params['footer_email'] ) ) {
        update_option( 'raabiha_footer_email', sanitize_email( $params['footer_email'] ) );
    }
    if ( isset( $params['footer_phone'] ) ) {
        update_option( 'raabiha_footer_phone', sanitize_text_field( $params['footer_phone'] ) );
    }
    if ( isset( $params['social_ig'] ) ) {
        update_option( 'raabiha_social_ig', esc_url_raw( $params['social_ig'] ) );
    }
    if ( isset( $params['social_tiktok'] ) ) {
        update_option( 'raabiha_social_tiktok', esc_url_raw( $params['social_tiktok'] ) );
    }

    return rest_ensure_response( array( 'message' => 'Settings saved successfully!' ) );
}

/**
 * Verify REST Access
 */
function raabiha_verify_rest_access() {
    $user = wp_get_current_user();
    $allowed_roles = array( 'administrator', 'raabiha_owner' );
    if ( array_intersect( $allowed_roles, $user->roles ) ) {
        return true;
    }
    return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view or edit these settings.', 'my-text-domain' ), array( 'status' => 401 ) );
}



// ═══════════════════════════════════════════════════════════════
// § 4. ASSET ENQUEUE — Dashboard Vue SPA
// ═══════════════════════════════════════════════════════════════

add_action( 'wp_enqueue_scripts', 'raabiha_enqueue_dashboard_assets' );

function raabiha_enqueue_dashboard_assets() {
    // Fonts globally
    wp_enqueue_style( 'raabiha-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Hanken+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap', array(), null );

    $theme_uri     = get_template_directory_uri();
    $build_dir     = get_template_directory() . '/assets/js';
    $build_uri     = $theme_uri . '/assets/js';
    $manifest_path = get_template_directory() . '/assets/.vite/manifest.json';

    $handle = 'raabiha-dashboard';
    $script_enqueued = false;


    // Load from Vite manifest if it exists (production build)
    if ( file_exists( $manifest_path ) ) {
        $manifest = json_decode( file_get_contents( $manifest_path ), true );

        if ( isset( $manifest['src/main.js']['file'] ) ) {
            $js_file = $manifest['src/main.js']['file'];
            if ( is_page_template( 'page-dashboard.php' ) ) {
                wp_enqueue_script(
                    $handle,
                    $theme_uri . '/assets/' . $js_file,
                    array(),
                    filemtime( get_template_directory() . '/assets/' . $js_file ),
                    true
                );
            }
            $script_enqueued = true;
        }

        if ( isset( $manifest['src/main.js']['css'] ) ) {
            foreach ( $manifest['src/main.js']['css'] as $index => $css_file ) {
                wp_enqueue_style(
                    'raabiha-dashboard-css-' . $index,
                    $theme_uri . '/assets/' . $css_file,
                    array(),
                    null
                );
            }
        }
    } else {
        // Fallback: load pre-built bundle directly (development / missing manifest)
        $js_bundle = $build_dir . '/raabiha-dashboard.iife.js';
        if ( file_exists( $js_bundle ) && is_page_template( 'page-dashboard.php' ) ) {
            wp_enqueue_script(
                $handle,
                $build_uri . '/raabiha-dashboard.iife.js',
                array(),
                '1.0.0',
                true
            );
            $script_enqueued = true;
        }
    }

    // Inject configuration data safely using wp_localize_script to avoid race conditions
    if ( $script_enqueued ) {
        $raabiha_config = raabiha_get_dashboard_config();
        wp_localize_script( $handle, 'RaabihaConfig', $raabiha_config );
    }

    // Google Fonts — Hanken Grotesk & JetBrains Mono (Fallback for Vue if raabiha-fonts isn't loaded)
    wp_enqueue_style(
        'raabiha-dashboard-fonts',
        'https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap',
        array(),
        null
    );
}

// ═══════════════════════════════════════════════════════════════
// § 5. BLOCK EDITOR ASSETS
// ═══════════════════════════════════════════════════════════════

add_action( 'enqueue_block_editor_assets', 'raabiha_enqueue_block_editor_assets' );
function raabiha_enqueue_block_editor_assets() {
    $theme_uri     = get_template_directory_uri();
    $manifest_path = get_template_directory() . '/assets/.vite/manifest.json';

    if ( file_exists( $manifest_path ) ) {
        $manifest = json_decode( file_get_contents( $manifest_path ), true );
        if ( isset( $manifest['src/main.js']['css'] ) ) {
            foreach ( $manifest['src/main.js']['css'] as $index => $css_file ) {
                wp_enqueue_style(
                    'raabiha-block-editor-css-' . $index,
                    $theme_uri . '/assets/' . $css_file,
                    array(),
                    null
                );
            }
        }
    }

    // Google Fonts for Block Editor
    wp_enqueue_style(
        'raabiha-block-editor-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap',
        array(),
        null
    );
}
/**
 * Get dashboard configuration values to expose to Vue.
 *
 * @return array Configuration array.
 */
function raabiha_get_dashboard_config() {
    $current_user = wp_get_current_user();
    return array(
        'ajax_url'    => admin_url( 'admin-ajax.php' ),
        'nonce'       => raabiha_get_product_nonce(),
        'rest_url'    => esc_url_raw( rest_url() ),
        'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
        'theme_url'   => get_template_directory_uri(),
        'site_url'    => get_site_url(),
        'user'        => array(
            'id'         => $current_user->ID,
            'name'       => $current_user->display_name,
            'email'      => $current_user->user_email,
            'avatar'     => get_avatar_url( $current_user->ID, array( 'size' => 64 ) ),
            'can_manage' => current_user_can( 'manage_woocommerce' ),
            'logout_url' => esc_url_raw( wp_logout_url( home_url( '/' ) ) ),
            'role'       => ! empty( $current_user->roles ) ? reset( $current_user->roles ) : 'customer',
        ),
    );
}

// Hide Admin Bar on the frontend for all users except Administrators
add_filter( 'show_admin_bar', 'raabiha_filter_admin_bar_visibility' );
function raabiha_filter_admin_bar_visibility( $show ) {
    if ( ! current_user_can( 'administrator' ) ) {
        return false;
    }
    return $show;
}


// ═══════════════════════════════════════════════════════════════
// § 5. AJAX ENDPOINT — raabiha_save_product
// ═══════════════════════════════════════════════════════════════

// Register for logged-in users
add_action( 'wp_ajax_raabiha_save_product', 'raabiha_handle_save_product' );

/**
 * Handle the AJAX request from the Vue ProductForm component.
 *
 * Expected POST fields:
 *  - nonce         (string)  WP Nonce for 'raabiha_product_nonce'
 *  - product_name  (string)  Product title
 *  - price         (float)   Regular price
 *  - description   (string)  Product description (HTML allowed)
 *  - focus_keyword (string)  Yoast SEO focus keyphrase
 *  - meta_desc     (string)  Yoast SEO meta description (120–160 chars recommended)
 *  - product_id    (int)     Optional — if set, update existing product
 *
 * @return void  Outputs JSON and dies.
 */
function raabiha_handle_save_product() {

    // ── 5a. Nonce Verification ────────────────────────────────
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

    if ( ! wp_verify_nonce( $nonce, 'raabiha_product_nonce' ) ) {
        wp_send_json_error( array(
            'code'    => 'invalid_nonce',
            'message' => __( 'Security check failed. Please refresh the page.', 'raabiha-theme' ),
        ), 403 );
    }

    // ── 5b. Capability Check ──────────────────────────────────
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array(
            'code'    => 'insufficient_permissions',
            'message' => __( 'You do not have permission to create or edit products.', 'raabiha-theme' ),
        ), 403 );
    }

    // ── 5c. Input Sanitization & Validation ──────────────────
    $product_name  = isset( $_POST['product_name'] )  ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) )  : '';
    $price_raw     = isset( $_POST['price'] )          ? $_POST['price']                                               : '';
    $description   = isset( $_POST['description'] )    ? wp_kses_post( wp_unslash( $_POST['description'] ) )          : '';
    $focus_keyword = isset( $_POST['focus_keyword'] )  ? sanitize_text_field( wp_unslash( $_POST['focus_keyword'] ) ) : '';
    $meta_desc     = isset( $_POST['meta_desc'] )      ? sanitize_textarea_field( wp_unslash( $_POST['meta_desc'] ) ) : '';
    $product_id    = isset( $_POST['product_id'] )     ? absint( $_POST['product_id'] )                               : 0;

    // Validate required fields
    $errors = array();

    if ( empty( $product_name ) ) {
        $errors[] = __( 'Product name is required.', 'raabiha-theme' );
    }

    $price = filter_var( $price_raw, FILTER_VALIDATE_FLOAT );
    if ( false === $price || $price < 0 ) {
        $errors[] = __( 'Price must be a valid positive number.', 'raabiha-theme' );
    }

    if ( strlen( $meta_desc ) > 0 && ( strlen( $meta_desc ) < 50 || strlen( $meta_desc ) > 320 ) ) {
        $errors[] = __( 'Meta description should be between 50 and 320 characters.', 'raabiha-theme' );
    }

    if ( ! empty( $errors ) ) {
        wp_send_json_error( array(
            'code'    => 'validation_error',
            'message' => implode( ' | ', $errors ),
        ), 422 );
    }

    // ── 5d. WooCommerce CRUD ──────────────────────────────────
    if ( ! class_exists( 'WC_Product_Simple' ) ) {
        wp_send_json_error( array(
            'code'    => 'woocommerce_missing',
            'message' => __( 'WooCommerce is not active. Please activate WooCommerce.', 'raabiha-theme' ),
        ), 500 );
    }

    try {
        // Load existing product for update, or create new
        if ( $product_id > 0 ) {
            $product = wc_get_product( $product_id );
            if ( ! $product || 'simple' !== $product->get_type() ) {
                $product = new WC_Product_Simple();
            }
        } else {
            $product = new WC_Product_Simple();
        }

        // Set core product data
        $product->set_name( $product_name );
        $product->set_description( $description );
        $product->set_regular_price( (string) $price );
        $product->set_status( 'publish' );
        $product->set_catalog_visibility( 'visible' );

        // Persist to database — returns the new/updated product ID
        $saved_id = $product->save();

        if ( is_wp_error( $saved_id ) || ! $saved_id ) {
            throw new \Exception( __( 'Failed to save product to the database.', 'raabiha-theme' ) );
        }

        // ── 5e. Yoast SEO Meta Injection ─────────────────────
        // These post-meta keys are the official Yoast SEO storage keys.
        if ( ! empty( $focus_keyword ) ) {
            update_post_meta( $saved_id, '_yoast_wpseo_focuskw', $focus_keyword );
        }

        if ( ! empty( $meta_desc ) ) {
            update_post_meta( $saved_id, '_yoast_wpseo_metadesc', $meta_desc );
        }

        // ── 5f. Successful Response ───────────────────────────
        wp_send_json_success( array(
            'product_id'   => $saved_id,
            'product_name' => $product->get_name(),
            'edit_url'     => get_edit_post_link( $saved_id, 'raw' ),
            'permalink'    => get_permalink( $saved_id ),
            'message'      => sprintf(
                /* translators: %s: product name */
                __( 'Product "%s" saved successfully.', 'raabiha-theme' ),
                $product->get_name()
            ),
        ) );

    } catch ( \Exception $e ) {
        wp_send_json_error( array(
            'code'    => 'server_error',
            'message' => $e->getMessage(),
        ), 500 );
    }
}

// ═══════════════════════════════════════════════════════════════
// § 6. CLEAN UP — Remove unnecessary WP head bloat
// ═══════════════════════════════════════════════════════════════

// Remove RSD link (Really Simple Discovery)
remove_action( 'wp_head', 'rsd_link' );

// Remove Windows Live Writer manifest link
remove_action( 'wp_head', 'wlwmanifest_link' );

// Remove shortlink
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

// Remove REST API link from head (still accessible, just not advertised)
remove_action( 'wp_head', 'rest_output_link_wp_head' );

// Disable emoji scripts (performance)
remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles',     'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles',  'print_emoji_styles' );

// ═══════════════════════════════════════════════════════════════
// § 7. HELPER — Expose nonce endpoint for non-enqueue context
// ═══════════════════════════════════════════════════════════════

/**
 * Generate a fresh nonce for the product form.
 * Called by page-dashboard.php via inline script.
 *
 * @return string WP nonce value.
 */
/**
 * Generate a fresh nonce for the product form.
 * Called by page-dashboard.php via inline script.
 *
 * @return string WP nonce value.
 */
function raabiha_get_product_nonce() {
    return wp_create_nonce( 'raabiha_product_nonce' );
}

// ═══════════════════════════════════════════════════════════════
// § 8. AJAX ENDPOINT — raabiha_get_stats
// ═══════════════════════════════════════════════════════════════

add_action( 'wp_ajax_raabiha_get_stats', 'raabiha_handle_get_stats' );

/**
 * Handle AJAX request for dashboard statistics and recent products list.
 *
 * @return void Outputs JSON and dies.
 */
function raabiha_handle_get_stats() {
    // ── Nonce Verification ────────────────────────────────
    $nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( $_GET['nonce'] ) : '';
    if ( ! wp_verify_nonce( $nonce, 'raabiha_product_nonce' ) ) {
        wp_send_json_error( array(
            'code'    => 'invalid_nonce',
            'message' => __( 'Security check failed. Please refresh the page.', 'raabiha-theme' ),
        ), 403 );
    }

    // ── Capability Check ──────────────────────────────────
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array(
            'code'    => 'insufficient_permissions',
            'message' => __( 'You do not have permission to view dashboard statistics.', 'raabiha-theme' ),
        ), 403 );
    }

    // ── Fetch Product Count ───────────────────────────────
    $product_counts = wp_count_posts( 'product' );
    $total_products = isset( $product_counts->publish ) ? (int) $product_counts->publish : 0;

    // ── Fetch Today's Orders & Month's Revenue ────────────
    $today_start = date( 'Y-m-d 00:00:00' );
    $today_end   = date( 'Y-m-d 23:59:59' );

    $month_start = date( 'Y-m-01 00:00:00' );
    $month_end   = date( 'Y-m-t 23:59:59' );

    // Query today's orders
    $orders_today = wc_get_orders( array(
        'limit'        => -1,
        'status'       => array( 'wc-processing', 'wc-completed', 'wc-on-hold' ),
        'date_created' => $today_start . '...' . $today_end,
    ) );
    $total_orders_today = count( $orders_today );

    // Query month's orders for revenue
    $orders_month = wc_get_orders( array(
        'limit'        => -1,
        'status'       => array( 'wc-processing', 'wc-completed' ),
        'date_created' => $month_start . '...' . $month_end,
    ) );

    $total_revenue_month = 0;
    foreach ( $orders_month as $order ) {
        $total_revenue_month += (float) $order->get_total();
    }

    // ── Fetch Recent Products (Last 5) ────────────────────
    $recent_products_query = wc_get_products( array(
        'limit'   => 5,
        'status'  => 'publish',
        'orderby' => 'date',
        'order'   => 'DESC',
    ) );

    $recent_products = array();
    foreach ( $recent_products_query as $prod ) {
        $recent_products[] = array(
            'id'         => $prod->get_id(),
            'name'       => $prod->get_name(),
            'price'      => (float) $prod->get_regular_price(),
            'formatted'  => html_entity_decode( strip_tags( wc_price( $prod->get_regular_price() ) ) ),
            'permalink'  => get_permalink( $prod->get_id() ),
            'date'       => $prod->get_date_created()->date_i18n( 'd M Y' ),
        );
    }

    // Query total customers
    $customer_count = count( get_users( array( 'role' => 'customer' ) ) );

    // ── Output Stats ──────────────────────────────────────
    wp_send_json_success( array(
        'stats' => array(
            'total_products'  => $total_products,
            'orders_today'    => $total_orders_today,
            'revenue_month'   => $total_revenue_month,
            'formatted_rev'   => html_entity_decode( strip_tags( wc_price( $total_revenue_month ) ) ),
            'total_customers' => $customer_count,
        ),
        'recent_products' => $recent_products,
    ) );
}

/**
 * Register custom roles for the Raabiha ecosystem.
 */
add_action( 'init', 'raabiha_register_custom_roles', 10 );
function raabiha_register_custom_roles() {
    // 1. Owner role (custom role)
    if ( ! get_role( 'raabiha_owner' ) ) {
        // Copy administrator capabilities
        $admin_role = get_role( 'administrator' );
        $owner_caps = $admin_role ? $admin_role->capabilities : array();

        // Strip theme/plugin management caps to restrict access
        $caps_to_strip = array(
            'switch_themes',
            'edit_themes',
            'install_themes',
            'update_themes',
            'delete_themes',
            'activate_plugins',
            'edit_plugins',
            'install_plugins',
            'update_plugins',
            'delete_plugins',
            'update_core',
        );
        foreach ( $caps_to_strip as $cap ) {
            unset( $owner_caps[ $cap ] );
        }

        add_role( 'raabiha_owner', 'Owner', $owner_caps );
    }

    // 2. Cashier role (kasir)
    if ( ! get_role( 'raabiha_cashier' ) ) {
        add_role( 'raabiha_cashier', 'Admin Kasir', array(
            'read'         => true,
            'edit_posts'   => false,
            'publish_posts'=> false,
            'edit_pages'   => false,
        ) );
    }

    // 3. Blogger role
    if ( ! get_role( 'raabiha_blogger' ) ) {
        add_role( 'raabiha_blogger', 'Blogger', array(
            'read'         => true,
            'edit_posts'   => true,
            'publish_posts'=> true,
            'delete_posts' => true,
            'upload_files' => true,
        ) );
    }

    // 4. Co-Administrator role (Admin without plugin/theme/core update caps)
    if ( ! get_role( 'raabiha_co_admin' ) ) {
        $admin_role = get_role( 'administrator' );
        $co_admin_caps = $admin_role ? $admin_role->capabilities : array();

        $caps_to_strip = array(
            'switch_themes',
            'edit_themes',
            'install_themes',
            'update_themes',
            'delete_themes',
            'activate_plugins',
            'edit_plugins',
            'install_plugins',
            'update_plugins',
            'delete_plugins',
            'update_core',
        );
        foreach ( $caps_to_strip as $cap ) {
            unset( $co_admin_caps[ $cap ] );
        }

        add_role( 'raabiha_co_admin', 'Co-Administrator', $co_admin_caps );
    }
}

add_filter('show_admin_bar', '__return_false');

/**
 * Restrict Gutenberg blocks if opened via Raabiha Dashboard iframe context
 */
function raabiha_dashboard_allowed_block_types( $allowed_blocks, $editor_context ) {
    if ( isset( $_GET['context'] ) && $_GET['context'] === 'raabiha_dashboard' && !empty($editor_context->post) ) {
        $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $lazy_blocks_only = array();
        
        foreach ( $registered_blocks as $name => $block ) {
            if ( strpos( $name, 'lazyblock/' ) === 0 ) {
                $lazy_blocks_only[] = $name;
            }
        }
        
        // Always allow paragraph for typing basic text
        $lazy_blocks_only[] = 'core/paragraph';
        
        return $lazy_blocks_only;
    }

    return $allowed_blocks;
}
add_filter( 'allowed_block_types_all', 'raabiha_dashboard_allowed_block_types', 10, 2 );

/**
 * Hide WP Admin sidebar and top bar when in Raabiha Dashboard context
 */
function raabiha_dashboard_admin_styles() {
    if ( isset( $_GET['context'] ) && $_GET['context'] === 'raabiha_dashboard' ) {
        echo '<style>
            #adminmenumain, #wpadminbar { display: none !important; }
            html.wp-toolbar { padding-top: 0 !important; }
            #wpcontent, #wpfooter { margin-left: 0 !important; }
        </style>';
    }
}
add_action('admin_head', 'raabiha_dashboard_admin_styles');

/**
 * --------------------------------------------------------------------------
 * RAABIHA EXTREME WHITE-LABELING
 * --------------------------------------------------------------------------
 */

// 1. Ganti teks Footer "Terima kasih telah menggunakan WordPress"
add_filter('admin_footer_text', function() {
    echo 'Powered by <span style="font-weight:bold; color:#0B4E26; letter-spacing:1px;">RAABIHA ENGINE</span> &copy; ' . date('Y');
});

// 2. Ganti logo di halaman Login
add_action('login_enqueue_scripts', function() {
    echo '<style type="text/css">
        @import url("https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;800&display=swap");
        body.login { 
            background: #FAF7F0 !important; 
            font-family: "Hanken Grotesk", sans-serif !important;
        }
        #login h1 a, .login h1 a {
            background-image: none !important;
            height: auto !important;
            width: auto !important;
            text-indent: 0 !important;
            font-weight: 800 !important;
            font-size: 36px !important;
            letter-spacing: 0.15em;
            color: #222523 !important;
            text-transform: uppercase;
        }
        #login h1 a::after {
            content: "AESTHETE";
        }
        .login form { 
            border-radius: 8px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important; 
            border: 1px solid #e5e5e5;
        }
        .wp-core-ui .button-primary { 
            background: #0B4E26 !important; 
            border-color: #083B1D !important; 
            text-shadow: none !important; 
            box-shadow: none !important;
            border-radius: 4px;
        }
    </style>';
});

// 3. Inject CSS Kustom ke WP-Admin untuk mengubah Font, Warna, dan Sembunyikan Logo WP
add_action('admin_head', function() {
    echo '<style>
        /* Import Google Fonts */
        @import url("https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap");

        /* Timpa font bawaan WordPress */
        body.wp-admin, .wp-core-ui, #adminmenu, #wpadminbar, .wrap h1, .wrap h2 {
            font-family: "Hanken Grotesk", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }

        /* Sembunyikan Logo WP di Admin Bar */
        #wp-admin-bar-wp-logo { display: none !important; }

        /* Ubah warna Admin Menu (Sidebar) */
        #adminmenu, #adminmenuback, #adminmenuwrap {
            background-color: #222523 !important; /* Charcoal khas Aesthete */
        }
        #adminmenu .wp-has-current-submenu .wp-submenu, 
        #adminmenu .wp-has-current-submenu .wp-submenu.sub-open, 
        #adminmenu .wp-has-current-submenu.opensub .wp-submenu, 
        #adminmenu a.wp-has-current-submenu:focus+.wp-submenu {
            background-color: #1a1c1a !important; 
        }
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, 
        #adminmenu li.current a.menu-top, 
        #adminmenu li.wp-has-current-submenu .wp-menu-arrow, 
        #adminmenu li.wp-has-current-submenu .wp-menu-arrow div {
            background: #0B4E26 !important; /* Hijau khas Aesthete saat aktif */
        }
        
        #wpadminbar { background-color: #222523 !important; }

        /* Tombol Primary */
        .wp-core-ui .button-primary {
            background: #0B4E26 !important;
            border-color: #083B1D !important;
            color: #fff !important;
            border-radius: 4px;
            text-shadow: none;
            box-shadow: none;
        }
        .wp-core-ui .button-primary:hover {
            background: #083B1D !important;
        }
    </style>';
});


// 5. Inject CSS Khusus ke Gutenberg Editor agar serasi dengan Vue Dashboard
add_action('admin_head', function() {
    $custom_css = '
        /* Ubah tombol biru Gutenberg menjadi Hijau Aesthete */
        .components-button.is-primary {
            background-color: #0B4E26 !important;
            color: #fff !important;
        }
        .components-button.is-primary:hover {
            background-color: #083B1D !important;
        }
        .components-button.is-primary:focus {
            box-shadow: 0 0 0 1.5px #fff, 0 0 0 3px #0B4E26 !important;
        }
        
        /* Ganti font Gutenberg menjadi Hanken Grotesk */
        .editor-styles-wrapper, .edit-post-header, .block-editor-block-list__layout {
            font-family: "Hanken Grotesk", sans-serif !important;
        }
    ';
    echo '<style>' . $custom_css . '</style>';
});
require_once get_template_directory() . '/inc/api-ui-settings.php';
require_once get_template_directory() . '/inc/class-raabiha-nav-walker.php';

// ═══════════════════════════════════════════════════════════════

// ═══════════════════════════════════════════════════════════════
// § 3.0. AUTOMATIC WEBP CONVERSION & COMPRESSION
// ═══════════════════════════════════════════════════════════════

/**
 * Allow WebP as an uploadable MIME type.
 */
add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
} );

/**
 * Fix WordPress MIME type check for WebP.
 */
add_filter( 'wp_check_filetype_and_ext', function( $data, $file, $filename, $mimes ) {
    if ( ! $data['type'] ) {
        $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
        if ( $ext === 'webp' ) {
            $data['ext']  = 'webp';
            $data['type'] = 'image/webp';
        }
    }
    return $data;
}, 10, 4 );

/**
/**
 * Automatically convert uploaded JPG/PNG images to WebP during upload.
 */
add_filter( 'wp_handle_upload', 'raabiha_handle_upload_webp_conversion' );
function raabiha_handle_upload_webp_conversion( $upload ) {
    try {
        // Only process JPG/PNG images
        if ( ! in_array( $upload['type'], array( 'image/jpeg', 'image/png' ), true ) ) {
            return $upload;
        }

        $file_path = $upload['file'];
        if ( ! file_exists( $file_path ) ) {
            return $upload;
        }

        // Increase memory limit to prevent memory exhaustion on large images
        @ini_set( 'memory_limit', '1024M' );

        // Check image dimensions first to avoid memory exhaustion
        $img_size = @getimagesize( $file_path );
        if ( $img_size ) {
            $width = $img_size[0];
            $height = $img_size[1];
            // If image is larger than 4000x4000, skip conversion to prevent out of memory
            if ( $width > 4000 || $height > 4000 ) {
                error_log( '[Raabiha WebP] Image too large for conversion: ' . $width . 'x' . $height );
                return $upload;
            }
        }

        $image = wp_get_image_editor( $file_path );
        if ( is_wp_error( $image ) ) {
            error_log( '[Raabiha WebP] Editor error: ' . $image->get_error_message() );
            return $upload;
        }

        $webp_path = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $file_path );
        $image->set_quality( 80 );
        $saved = $image->save( $webp_path, 'image/webp' );

        if ( is_wp_error( $saved ) ) {
            error_log( '[Raabiha WebP] Save error: ' . $saved->get_error_message() );
            return $upload;
        }

        // Delete original to save storage ONLY if a new file was actually created
        if ( $file_path !== $saved['path'] && file_exists( $saved['path'] ) ) {
            @unlink( $file_path );
        }

        // Update upload details using the actual saved path
        $upload['file'] = $saved['path'];
        $upload['url']  = str_replace( wp_basename( $upload['url'] ), wp_basename( $saved['path'] ), $upload['url'] );
        $upload['type'] = $saved['mime-type'];
    } catch ( Throwable $t ) {
        error_log( '[Raabiha WebP] Throwable caught: ' . $t->getMessage() );
    } catch ( Exception $e ) {
        error_log( '[Raabiha WebP] Exception caught: ' . $e->getMessage() );
    }
    return $upload;
}


/**
 * Ensure sub-sizes (thumbnails) are also generated as WebP.
 */
add_filter( 'image_editor_output_format', function( $formats ) {
    $formats['image/jpeg'] = 'image/webp';
    $formats['image/png']  = 'image/webp';
    return $formats;
} );

/**
 * Set WebP compression quality (80% is optimal for balance).
 */
add_filter( 'wp_editor_set_quality', function( $quality, $mime_type ) {
    if ( 'image/webp' === $mime_type ) {
        return 80;
    }
    return $quality;
}, 10, 2 );
// Custom Mini Cart Item Thumbnail & Name
add_filter( 'woocommerce_cart_item_name', 'raabiha_mini_cart_item_name', 10, 3 );
function raabiha_mini_cart_item_name( $product_name, $cart_item, $cart_item_key ) {
    if ( is_cart() || is_checkout() ) return $product_name;
    // In mini cart, the default name is wrapped in <a> tag along with the thumbnail.
    return '<span class="raabiha-item-title">' . $product_name . '</span>';
}

// Custom Mini Cart Quantity & Price Layout
add_filter( 'woocommerce_widget_cart_item_quantity', 'raabiha_mini_cart_item_quantity', 10, 3 );
function raabiha_mini_cart_item_quantity( $html, $cart_item, $cart_item_key ) {
    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $product_price = WC()->cart->get_product_price( $_product );
    
    $qty = $cart_item['quantity'];
    
    // We output two spans directly so they can be grid items
    return sprintf( 
        '<span class="r-qty">%s<span class="x">x</span></span><span class="r-price">%s</span>', 
        $qty, 
        $product_price 
    );
}

// ═══════════════════════════════════════════════════════════════
// § 16. WOOCOMMERCE MISC
// ═══════════════════════════════════════════════════════════════

/**
 * Update custom cart count badge via WooCommerce AJAX fragments
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'raabiha_cart_count_fragments', 10, 1 );
function raabiha_cart_count_fragments( $fragments ) {
    ob_start();
    ?>
    <span class="raabiha-cart-count-badge absolute -top-2 -right-2 bg-[#064e3b] text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center <?php echo (WC()->cart->get_cart_contents_count() > 0) ? '' : 'hidden'; ?>">
        <?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?>
    </span>
    <?php
    $fragments['span.raabiha-cart-count-badge'] = ob_get_clean();
    return $fragments;
}

/**
 * Format Variable Product Price to "Start from"
 */
add_filter( 'woocommerce_variable_sale_price_html', 'raabiha_custom_variable_price', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'raabiha_custom_variable_price', 10, 2 );

function raabiha_custom_variable_price( $price, $product ) {
    $min_price = $product->get_variation_price( 'min', true );
    $max_price = $product->get_variation_price( 'max', true );
    
    if ( $min_price !== $max_price ) {
        // Output a clean range without screen reader text
        $price = wc_price( $min_price ) . ' - ' . wc_price( $max_price );
    }
    
    return $price;
}

