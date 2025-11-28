<?php

namespace App\Services;

/**
 * CheckpointIconService
 * 
 * Service untuk manage icon yang tepat untuk setiap checkpoint
 * Menggunakan Bootstrap Icons (bi) atau FontAwesome (fa)
 */
class CheckpointIconService
{
    /**
     * Get icon class untuk setiap checkpoint berdasarkan sequence
     * 
     * @param int $checkpointSequence
     * @return string Icon class (contoh: 'bi-file-earmark-check')
     */
    public static function getIconClass(int $checkpointSequence): string
    {
        $icons = [
            1 => 'bi-file-earmark-text',           // Penawaran Permintaan
            2 => 'bi-clipboard-check',             // Evatek
            3 => 'bi-file-earmark-pdf',            // Usulan Pengadaan / OC
            4 => 'bi-pen-fill',                    // Pengesahan Kontrak
            5 => 'bi-truck',                       // Pengiriman Material
            6 => 'bi-credit-card-fill',            // Pembayaran DP
            7 => 'bi-gear-fill',                   // Proses Importasi / Produksi
            8 => 'bi-box-seam-fill',               // Kedatangan Material
            9 => 'bi-hand-thumbs-up-fill',        // Serah Terima Dokumen
            10 => 'bi-search',                     // Inspeksi Barang
            11 => 'bi-file-earmark-check-fill',    // Berita Acara / NCR
            12 => 'bi-file-earmark-check-2',       // Verifikasi Dokumen
            13 => 'bi-check-circle-fill',          // Pembayaran
        ];

        return $icons[$checkpointSequence] ?? 'bi-circle-fill';
    }

    /**
     * Get semua checkpoint dengan icon mereka
     * 
     * @return array
     */
    public static function getAllCheckpointsWithIcons(): array
    {
        return [
            1 => [
                'name' => 'Penawaran Permintaan',
                'icon' => 'bi-file-earmark-text',
                'description' => 'Submission of procurement request'
            ],
            2 => [
                'name' => 'Evatek',
                'icon' => 'bi-clipboard-check',
                'description' => 'Technical evaluation'
            ],
            3 => [
                'name' => 'Usulan Pengadaan / OC',
                'icon' => 'bi-file-earmark-pdf',
                'description' => 'Procurement proposal'
            ],
            4 => [
                'name' => 'Pengesahan Kontrak',
                'icon' => 'bi-pen-fill',
                'description' => 'Contract approval'
            ],
            5 => [
                'name' => 'Pengiriman Material',
                'icon' => 'bi-truck',
                'description' => 'Material delivery'
            ],
            6 => [
                'name' => 'Pembayaran DP',
                'icon' => 'bi-credit-card-fill',
                'description' => 'Down payment'
            ],
            7 => [
                'name' => 'Proses Importasi / Produksi',
                'icon' => 'bi-gear-fill',
                'description' => 'Import/Production process'
            ],
            8 => [
                'name' => 'Kedatangan Material',
                'icon' => 'bi-box-seam-fill',
                'description' => 'Material arrival'
            ],
            9 => [
                'name' => 'Serah Terima Dokumen',
                'icon' => 'bi-hand-thumbs-up-fill',
                'description' => 'Document handover'
            ],
            10 => [
                'name' => 'Inspeksi Barang',
                'icon' => 'bi-search',
                'description' => 'Goods inspection'
            ],
            11 => [
                'name' => 'Berita Acara / NCR',
                'icon' => 'bi-file-earmark-check-fill',
                'description' => 'Berita Acara / NCR Report'
            ],
            12 => [
                'name' => 'Verifikasi Dokumen',
                'icon' => 'bi bi-file-earmark-post',
                'description' => 'Document verification'
            ],
            13 => [
                'name' => 'Pembayaran',
                'icon' => 'bi-check-circle-fill',
                'description' => 'Final payment'
            ],
        ];
    }

    /**
     * Get icon dengan warna berdasarkan status
     * 
     * @param string $status 'completed', 'active', 'not-started'
     * @param int $checkpointSequence
     * @return array ['icon' => 'class', 'color' => 'hex']
     */
    public static function getIconWithColor(string $status, int $checkpointSequence): array
    {
        $icon = self::getIconClass($checkpointSequence);
        
        $colors = [
            'completed' => '#28AC00',    // Hijau
            'active' => '#ECAD02',       // Kuning
            'not-started' => '#e0e0e0',  // Abu-abu
        ];

        return [
            'icon' => $icon,
            'color' => $colors[$status] ?? '#e0e0e0',
        ];
    }
}