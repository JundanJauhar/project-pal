<?php

namespace Tests\Unit\Services;

use App\Services\CheckpointIconService;
use PHPUnit\Framework\TestCase;

class CheckpointIconServiceTest extends TestCase
{
    /**
     * Test getting icon class for each checkpoint
     */
    public function test_returns_correct_icon_for_each_checkpoint()
    {
        $expectedIcons = [
            1 => 'bi-file-earmark-text',
            2 => 'bi-chat-dots',
            3 => 'bi-clipboard-check',
            4 => 'bi-file-earmark-ruled',
            5 => 'bi-file-earmark-pdf',
            6 => 'bi-pen-fill',
            7 => 'bi-credit-card-fill',
            8 => 'bi-truck',
            9 => 'bi-box-seam-fill',
            10 => 'bi-file-earmark-check-fill',
            11 => 'bi-check-circle-fill',
        ];

        foreach ($expectedIcons as $sequence => $expectedIcon) {
            $icon = CheckpointIconService::getIconClass($sequence);
            $this->assertEquals($expectedIcon, $icon, "Icon for checkpoint $sequence should be $expectedIcon");
        }
    }

    /**
     * Test returns default icon for invalid checkpoint sequence
     */
    public function test_returns_default_icon_for_invalid_sequence()
    {
        $icon = CheckpointIconService::getIconClass(0);
        $this->assertEquals('bi-circle-fill', $icon);

        $icon = CheckpointIconService::getIconClass(99);
        $this->assertEquals('bi-circle-fill', $icon);

        $icon = CheckpointIconService::getIconClass(-1);
        $this->assertEquals('bi-circle-fill', $icon);
    }

    /**
     * Test getAllCheckpointsWithIcons returns array
     */
    public function test_get_all_checkpoints_returns_array()
    {
        $checkpoints = CheckpointIconService::getAllCheckpointsWithIcons();

        $this->assertIsArray($checkpoints);
        $this->assertNotEmpty($checkpoints);
    }

    /**
     * Test all checkpoints have required fields
     */
    public function test_all_checkpoints_have_required_fields()
    {
        $checkpoints = CheckpointIconService::getAllCheckpointsWithIcons();

        foreach ($checkpoints as $sequence => $checkpoint) {
            $this->assertArrayHasKey('name', $checkpoint, "Checkpoint $sequence should have 'name'");
            $this->assertArrayHasKey('icon', $checkpoint, "Checkpoint $sequence should have 'icon'");
            $this->assertArrayHasKey('description', $checkpoint, "Checkpoint $sequence should have 'description'");

            $this->assertNotEmpty($checkpoint['name']);
            $this->assertNotEmpty($checkpoint['icon']);
            $this->assertNotEmpty($checkpoint['description']);
        }
    }

    /**
     * Test icons in getAllCheckpointsWithIcons match getIconClass
     */
    public function test_icons_consistency_between_methods()
    {
        $checkpoints = CheckpointIconService::getAllCheckpointsWithIcons();

        foreach ($checkpoints as $sequence => $checkpoint) {
            $iconFromGetAll = $checkpoint['icon'];
            $iconFromGetClass = CheckpointIconService::getIconClass($sequence);

            $this->assertEquals(
                $iconFromGetClass,
                $iconFromGetAll,
                "Icon for checkpoint $sequence should match between methods"
            );
        }
    }

    /**
     * Test specific checkpoint names
     */
    public function test_checkpoint_names_are_correct()
    {
        $checkpoints = CheckpointIconService::getAllCheckpointsWithIcons();

        $expectedNames = [
            1 => 'Permintaan Pengadaan',
            2 => 'Inquiry & Quotation',
            3 => 'Evatek',
            4 => 'Negotiation',
            5 => 'Usulan Pengadaan / OC',
            6 => 'Pengesahan Kontrak',
            7 => 'Pembayaran DP',
        ];

        foreach ($expectedNames as $sequence => $expectedName) {
            $this->assertEquals($expectedName, $checkpoints[$sequence]['name']);
        }
    }

    /**
     * Test all icons use Bootstrap Icons (bi-) prefix
     */
    public function test_all_icons_use_bootstrap_icons_prefix()
    {
        $checkpoints = CheckpointIconService::getAllCheckpointsWithIcons();

        foreach ($checkpoints as $sequence => $checkpoint) {
            $icon = $checkpoint['icon'];
            $this->assertStringContainsString(
                'bi-',
                $icon,
                "Icon for checkpoint $sequence should use Bootstrap Icons prefix"
            );
        }
    }

    /**
     * Test checkpoint count matches expected workflow
     */
    public function test_has_eleven_checkpoints()
    {
        $checkpoints = CheckpointIconService::getAllCheckpointsWithIcons();

        // Should have 11 checkpoints (1 through 11)
        $this->assertCount(11, $checkpoints);
        $this->assertArrayHasKey(1, $checkpoints);
        $this->assertArrayHasKey(11, $checkpoints);
    }

    /**
     * Test checkpoint sequences are sequential
     */
    public function test_checkpoint_sequences_are_sequential()
    {
        $checkpoints = CheckpointIconService::getAllCheckpointsWithIcons();

        $sequences = array_keys($checkpoints);
        $expectedSequences = range(1, 11);

        $this->assertEquals($expectedSequences, $sequences);
    }
}
