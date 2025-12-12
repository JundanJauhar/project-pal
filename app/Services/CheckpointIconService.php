<?php

namespace App\Services;

/**
 * CheckpointIconService
 * 
 * Service untuk manage icon yang tepat untuk setiap checkpoint
 * Menggunakan Bootstrap Icons (bi)
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
            1 => 'bi-file-earmark-text',           // Permintaan Pengadaan
            2 => 'bi-chat-dots',                   // Inquiry & Quotation
            3 => 'bi-clipboard-check',             // Evatek
            4 => 'bi-handshake',                   // Negotiation
            5 => 'bi-file-earmark-pdf',            // Usulan Pengadaan / OC
            6 => 'bi-pen-fill',                    // Pengesahan Kontrak
            7 => 'bi-credit-card-fill',            // Pembayaran DP
            8 => 'bi-truck',                       // Pengiriman Material
            9 => 'bi-box-seam-fill',               // Kedatangan Material
            10 => 'bi-file-earmark-check-fill',    // Verifikasi Dokumen
            11 => 'bi-check-circle-fill',          // Pembayaran
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
                'name' => 'Permintaan Pengadaan',
                'icon' => 'bi-file-earmark-text',
                'description' => 'Procurement request submission'
            ],
            2 => [
                'name' => 'Inquiry & Quotation',
                'icon' => 'bi-chat-dots',
                'description' => 'Inquiry and quotation process'
            ],
            3 => [
                'name' => 'Evatek',
                'icon' => 'bi-clipboard-check',
                'description' => 'Technical evaluation'
            ],
            4 => [
                'name' => 'Negotiation',
                'icon' => 'bi-handshake',
                'description' => 'Price and term negotiation'
            ],
            5 => [
                'name' => 'Usulan Pengadaan / OC',
                'icon' => 'bi-file-earmark-pdf',
                'description' => 'Procurement proposal'
            ],
            6 => [
                'name' => 'Pengesahan Kontrak',
                'icon' => 'bi-pen-fill',
                'description' => 'Contract approval'
            ],
            7 => [
                'name' => 'Pembayaran DP',
                'icon' => 'bi-credit-card-fill',
                'description' => 'Down payment'
            ],
            8 => [
                'name' => 'Pengiriman Material',
                'icon' => 'bi-truck',
                'description' => 'Material delivery'
            ],
            9 => [
                'name' => 'Kedatangan Material',
                'icon' => 'bi-box-seam-fill',
                'description' => 'Material arrival'
            ],
            10 => [
                'name' => 'Verifikasi Dokumen',
                'icon' => 'bi-file-earmark-check-fill',
                'description' => 'Document verification'
            ],
            11 => [
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