<?php
/**
 * EDUNEX Router - maps routes to controllers with middleware roles
 *
 * Route pattern: app/<module>/<controller>.php  ->  controller exposes run()
 * Registered via Router::get / Router::post.
 */

class Router {
    private static array $routes = [];
    private static array $views = [];
    private static array $data = [];
    private static string $view = '';

    /** Register page route (GET+POST both hit it; controller checks method) */
    public static function page(string $path, string $controllerFile, array|string $role = '*', ?string $perm = null, ?string $license = null): void {
        self::$routes[$path] = ['file' => $controllerFile, 'role' => $role, 'perm' => $perm, 'license' => $license];
    }

    /** Register pure view template */
    public static function view(string $path, string $template): void {
        self::$views[$path] = $template;
    }

    /** Render a template inside app shell */
    public static function render(string $template, array $data = []): void {
        self::$view = $template;
        self::$data = $data;
    }

    public static function dispatch(string $route): void {
        if (isset(self::$views[$route])) {
            self::$view = self::$views[$route];
            self::$data = [];
            return;
        }
        if (isset(self::$routes[$route])) {
            $r = self::$routes[$route];
            if ($r['role'] !== '*' && $r['role'] !== 'guest') {
                require_role(...(is_array($r['role']) ? $r['role'] : [$r['role']]));
            }
            if ($r['perm'] && !can($r['perm'])) {
                flash('danger', 'You do not have permission to access that page.');
                redirect('dashboard');
            }
            if (!empty($r['license'])) {
                require_license($r['license']);
            }
            $before = get_declared_classes();
            require_once APP_PATH . '/' . $r['file'];
            $after = get_declared_classes();
            $new = array_values(array_diff($after, $before));
            if (!$new) return; // procedural controller (API) already emitted output
            $base = basename($r['file'], '.php');
            $dir = str_replace(['/', '-', '.'], '_', dirname($r['file']));
            $cls = 'Ctl_' . $dir . '_' . $base;
            if (!class_exists($cls)) $cls = 'Ctl_' . $base;
            if (!class_exists($cls)) {
                $rdir = str_replace(['/', '-', '.'], '_', trim(dirname($route), '/.'));
                if ($rdir !== '') $cls = 'Ctl_' . $rdir;
            }
            if (!class_exists($cls) && count($new) === 1) $cls = $new[0];
            if (!class_exists($cls)) {
                http_response_code(500);
                echo 'Controller not found for: ' . e($route);
                return;
            }
            (new $cls())->run();
            return;
        }
        http_response_code(404);
        self::$view = 'errors/404';
        self::$data = ['title' => 'Not Found'];
    }

    /** Output: full page or JSON (if controller set headers/json) */
    public static function respond(): void {
        if (!self::$view) return; // controller already emitted (json/redirect)
        self::$view = ltrim(self::$view, '/');
        $data = self::$data;
        $data['__view'] = self::$view;
        $viewFile = BASE_PATH . '/app/views/' . self::$view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'Missing template: ' . e($viewFile);
            return;
        }
        // extract data for templates
        extract($data, EXTR_SKIP);
        // Partial render (AJAX swaps): view only, no layout
        if (($_GET['partial'] ?? '') === '1') {
            require $viewFile;
            return;
        }
        // App shell layout
        $isApp = str_starts_with(self::$view, 'app/');
        $isLanding = str_starts_with(self::$view, 'landing');
        $isAuth = str_starts_with(self::$view, 'auth');
        $isPublic = str_starts_with(self::$view, 'public') || str_starts_with(self::$view, 'errors');
        if ($isApp) {
            require BASE_PATH . '/app/views/layouts/app.php';
        } elseif ($isLanding) {
            require BASE_PATH . '/app/views/layouts/landing.php';
        } elseif ($isAuth) {
            require BASE_PATH . '/app/views/layouts/auth.php';
        } else {
            require BASE_PATH . '/app/views/layouts/plain.php';
        }
    }

    public static function route(string $path): string { return url('index.php?r=' . $path); }
}
