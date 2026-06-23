<?php

class FileProcessor {
    protected $relativeFolderPath;
    protected $fullFolderPath;

    function __construct(string $fullFolderPath = "", string $relativeFolderPath = "") {
        $this->fullFolderPath = $fullFolderPath;
        $this->relativeFolderPath = $relativeFolderPath;
    }

    public function save(?string $fileName = null): ?array {
        if (empty($_FILES)) return null;

        try {
            $fileRaw = $_FILES['file'];
            $fileInfo = pathinfo($fileRaw['name']);
            $fileExt = $fileInfo['extension'];
            $fileName = ($fileName ?? $fileInfo['filename']);
            $fileFullName = "{$fileName}.{$fileExt}";

            if (!file_exists($this->fullFolderPath)) {
                mkdir($this->fullFolderPath, 0777, true);
            }

            $tempFile = $fileRaw['tmp_name'];
            $targetFile = "{$this->fullFolderPath}/{$fileFullName}";
            move_uploaded_file($tempFile, $targetFile);

            return [
                'name' => $fileName,
                'ext' => $fileExt,
                'url' =>  "{$this->relativeFolderPath}/{$fileFullName}"
            ];
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getFiles(): ?array {
        try {
            if (!file_exists($this->fullFolderPath)) return null;

            if (!$handle = opendir($this->fullFolderPath)) return null;

            $files = [];
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $entrySize = filesize("{$this->fullFolderPath}/{$entry}");

                    $files[] = [
                        'name' => $entry,
                        'size' => $entrySize,
                        'url' => "{$this->relativeFolderPath}/{$entry}"
                    ];
                }
            }

            return $files;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function remove(string $file): bool {
        try {
            return unlink("{$this->fullFolderPath}/{$file}");
        } catch (\Throwable $th) {
            return false;
        }
    }
}
