<?php
/**
 * Kravyo - Core View Template Renderer
 */

class View {

    /**
     * Render view file inside specified master layout
     */
    public static function render(string $viewPath, array $data = [], string $layout = 'main'): void {
        // Extract array keys into variables for view context
        extract($data);

        $file = VIEWS_PATH . '/' . $viewPath . '.php';

        if (!file_exists($file)) {
            die("View template [{$viewPath}] not found at {$file}");
        }

        // Capture view output buffer
        ob_start();
        require $file;
        $content = ob_get_clean();

        // Render layout wrapping view content
        if ($layout) {
            $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Include sub-view partial (e.g. headers, footers, alerts)
     */
    public static function partial(string $partialPath, array $data = []): void {
        extract($data);
        $file = VIEWS_PATH . '/partials/' . $partialPath . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
