<?php

namespace App\Core;

class View {
    public static function render(string $view, array $params = [], string $layout = 'layouts/main'): void {
        $layoutContent = self::renderLayout($layout, $params);
        $viewContent = self::renderOnlyView($view, $params);
        echo str_replace('{{content}}', $viewContent, $layoutContent);
    }

    public static function renderRaw(string $view, array $params = []): void {
        echo self::renderOnlyView($view, $params);
    }

    protected static function renderLayout(string $layout, array $params): string {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        ob_start();
        $layoutFile = VIEW_PATH . '/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            include VIEW_PATH . '/layouts/main.php';
        }
        return ob_get_clean();
    }

    protected static function renderOnlyView(string $view, array $params): string {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        ob_start();
        $viewFile = VIEW_PATH . '/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<div class='alert alert-danger'>View file [{$view}] not found.</div>";
        }
        return ob_get_clean();
    }
}
