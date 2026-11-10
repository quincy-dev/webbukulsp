<?php
// Fungsi bantuan untuk upload dan pengelolaan gambar buku.

/** Menyimpan gambar cover dari URL atau file upload. */
function processBookImage(array $file, string $imageUrl, string $oldImage = ''): array
{
    $imageUrl = trim($imageUrl);
    $hasUpload = isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE;

    if ($hasUpload && $imageUrl !== '') {
        return [$oldImage, 'Pilih salah satu: URL gambar atau upload file, jangan keduanya.'];
    }

    if ($hasUpload) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [$oldImage, 'Upload gambar gagal. Coba pilih file kembali.'];
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            return [$oldImage, 'Ukuran gambar maksimal 5 MB.'];
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return [$oldImage, 'File yang diunggah harus berupa gambar yang valid.'];
        }

        $mimeToExtension = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        $mime = $imageInfo['mime'] ?? '';
        if (!isset($mimeToExtension[$mime])) {
            return [$oldImage, 'Format gambar yang didukung: JPG, PNG, GIF, dan WEBP.'];
        }

        $uploadDir = __DIR__ . '/../../assets/uploads/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            return [$oldImage, 'Folder upload tidak dapat dibuat.'];
        }

        $filename = bin2hex(random_bytes(12)) . '.' . $mimeToExtension[$mime];
        if (!// Memindahkan file gambar yang berhasil diunggah.
move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return [$oldImage, 'Gambar gagal disimpan ke folder uploads.'];
        }
        return [$filename, ''];
    }

    if ($imageUrl !== '') {
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            return [$oldImage, 'URL gambar harus berupa alamat http atau https yang valid.'];
        }
        return [$imageUrl, ''];
    }

    return [$oldImage, ''];
}
