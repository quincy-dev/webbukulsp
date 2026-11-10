<?php
// Helper untuk menentukan gambar buku yang akan ditampilkan.

/** Menghasilkan sumber gambar untuk URL eksternal maupun file lokal di assets/uploads. */
function bookstoreImageSrc(?string $image, string $relativePrefix = ''): string
{
    $image = trim((string) $image);
    if ($image === '') {
        return 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=800&h=1000&fit=crop';
    }
    if (preg_match('~^https?://~i', $image)) {
        return $image;
    }
    return $relativePrefix . 'assets/uploads/' . rawurlencode(basename($image));
}
