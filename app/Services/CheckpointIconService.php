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
            3 => 'bi-chat-dots-fill',              // Negosiasi
            4 => 'bi-file-earmark-pdf',            // Usulan Pengadaan / OC
            5 => 'bi-pen-fill',                    // Pengesahan Kontrak
            6 => 'bi-truck',                       // Pengiriman Material
            7 => 'bi-credit-card-fill',            // Pembayaran DP
            8 => 'bi-gear-fill',                   // Proses Importasi / Produksi
            9 => 'bi-box-seam-fill',               // Kedatangan Material
            10 => 'bi-hand-thumbs-up-fill',        // Serah Terima Dokumen
            11 => 'bi-search',                     // Inspeksi Barang
            12 => 'bi-file-earmark-check-fill',    // Berita Acara / NCR
            13 => 'bi-file-earmark-check-2',       // Verifikasi Dokumen
            14 => 'bi-check-circle-fill',          // Pembayaran
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
                'name' => 'Negosiasi',
                'icon' => 'bi-chat-dots-fill',
                'description' => 'Negotiation with vendors'
            ],
            4 => [
                'name' => 'Usulan Pengadaan / OC',
                'icon' => 'bi-file-earmark-pdf',
                'description' => 'Procurement proposal'
            ],
            5 => [
                'name' => 'Pengesahan Kontrak',
                'icon' => 'bi-pen-fill',
                'description' => 'Contract approval'
            ],
            6 => [
                'name' => 'Pengiriman Material',
                'icon' => 'bi-truck',
                'description' => 'Material delivery'
            ],
            7 => [
                'name' => 'Pembayaran DP',
                'icon' => 'bi-credit-card-fill',
                'description' => 'Down payment'
            ],
            8 => [
                'name' => 'Proses Importasi / Produksi',
                'icon' => 'bi-gear-fill',
                'description' => 'Import/Production process'
            ],
            9 => [
                'name' => 'Kedatangan Material',
                'icon' => 'bi-box-seam-fill',
                'description' => 'Material arrival'
            ],
            10 => [
                'name' => 'Serah Terima Dokumen',
                'icon' => 'bi-hand-thumbs-up-fill',
                'description' => 'Document handover'
            ],
            11 => [
                'name' => 'Inspeksi Barang',
                'icon' => 'bi-search',
                'description' => 'Goods inspection'
            ],
            12 => [
                'name' => 'Berita Acara / NCR',
                'icon' => 'bi-file-earmark-check-fill',
                'description' => 'Berita Acara / NCR Report'
            ],
            13 => [
                'name' => 'Verifikasi Dokumen',
                'icon' => 'bi bi-file-earmark-post',
                'description' => 'Document verification'
            ],
            14 => [
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