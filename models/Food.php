<?php
declare(strict_types=1);

namespace models;
require_once __UTILS__ . '/Data.php';

use utils\Data;

class Food {

    use Data;
    private string $filePath;
    private string $name;
    private string $description;

    public function toArray(): array {
        return [
            'name'        => $this->getName(),
            'description' => $this->getDescription(),
            'image_path'  => $this->getFilePath(),
        ];
    }
}
