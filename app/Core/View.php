<?php
declare(strict_types=1);
namespace App\Core;

class View {
    private string $viewsPath;
    public function __construct(string $viewsPath) { $this->viewsPath = rtrim($viewsPath, '/') . '/'; }
    public function render(string $name, array $data = []): string {
        $file = $this->viewsPath . str_replace('.', '/', $name) . '.php';
        if (!is_file($file)) throw new \RuntimeException("View [{$name}] not found");
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Render a reusable fragment. Used by the layouts to embed shared pieces
     * such as the animated logout button.
     */
    public function partial(string $name, array $data = []): string {
        $file = $this->viewsPath . str_replace('.', '/', $name) . '.php';
        if (!is_file($file)) throw new \RuntimeException("Partial [{$name}] not found");
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }
    public function renderWithLayout(string $name, string $layout, array $data = []): string {
        /*
         * Controllers may either pass their view data directly (and let this
         * method render the view) or pre-render the view into "content".
         * Only render the view here when "content" was not supplied, otherwise
         * the pre-rendered markup would be discarded and the view would be
         * re-rendered without its data.
         */
        if (!isset($data['content'])) {
            $data['content'] = $this->render($name, $data);
        }
        return $this->render($layout, $data);
    }
}
