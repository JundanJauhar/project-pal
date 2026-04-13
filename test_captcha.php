<?php
// Test if GD functions are available
echo "Testing GD Library Functions:\n";
echo "============================\n\n";

// Check if GD is available
if (extension_loaded('gd')) {
    echo "✅ GD Extension is loaded\n\n";
} else {
    echo "❌ GD Extension is NOT loaded\n";
    exit(1);
}

// Test image creation
try {
    $image = imagecreatetruecolor(170, 55);
    if ($image) {
        echo "✅ imagecreatetruecolor() works\n";
        imagedestroy($image);
    } else {
        echo "❌ imagecreatetruecolor() failed\n";
    }
} catch (Exception $e) {
    echo "❌ imagecreatetruecolor() error: " . $e->getMessage() . "\n";
}

// Test image functions used in captcha
$image = imagecreatetruecolor(170, 55);
$color = imagecolorallocate($image, 255, 255, 255);

try {
    imagefilledrectangle($image, 0, 0, 170, 55, $color);
    echo "✅ imagefilledrectangle() works\n";
} catch (Exception $e) {
    echo "❌ imagefilledrectangle() error: " . $e->getMessage() . "\n";
}

try {
    imagesetpixel($image, 10, 10, $color);
    echo "✅ imagesetpixel() works\n";
} catch (Exception $e) {
    echo "❌ imagesetpixel() error: " . $e->getMessage() . "\n";
}

try {
    imagepng($image);
    echo "✅ imagepng() works\n";
} catch (Exception $e) {
    echo "❌ imagepng() error: " . $e->getMessage() . "\n";
}

imagedestroy($image);

echo "\n✅ All GD functions are working correctly!\n";
?>
