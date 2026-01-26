<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class CaptchaController extends Controller
{
    public function generate()
    {
        // =============================
        // 1. Generate Code
        // =============================
        $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5));

        Session::put('captcha', [
            'code' => $code,
            'expires_at' => now()->addSeconds(60)->timestamp,
        ]);

        // =============================
        // 2. Image Setup
        // =============================
        $width  = 170;
        $height = 55;

        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        // =============================
        // 3. Colors
        // =============================
        $textColors = [
            imagecolorallocate($image, 0, 0, 0),
            imagecolorallocate($image, 40, 40, 40),
            imagecolorallocate($image, 80, 80, 80),
        ];

        $noiseColor = imagecolorallocate($image, 140, 140, 140);
        $lineColor  = imagecolorallocate($image, 120, 120, 120);

        // =============================
        // 4. Noise (Dots)
        // =============================
        for ($i = 0; $i < 350; $i++) {
            imagesetpixel(
                $image,
                rand(0, $width),
                rand(0, $height),
                $noiseColor
            );
        }

        // =============================
        // 5. Random Lines
        // =============================
        for ($i = 0; $i < 6; $i++) {
            imageline(
                $image,
                rand(0, $width),
                rand(0, $height),
                rand(0, $width),
                rand(0, $height),
                $lineColor
            );
        }

        // =============================
        // 6. Fonts (Open Source)
        // =============================
        $fonts = [
            public_path('fonts/RobotoSlab-Bold.ttf'),
            public_path('fonts/OpenSans-Bold.ttf'),
            public_path('fonts/Montserrat-Bold.ttf'),
            public_path('fonts/PlayfairDisplay-Bold.ttf'),
        ];

        // =============================
        // 7. Draw Text (TTF + Rotation)
        // =============================
        $x = 15;

        for ($i = 0; $i < strlen($code); $i++) {
            $fontSize = rand(22, 28);
            $angle    = rand(-25, 25);
            $y        = rand(35, 48);

            $font  = $fonts[array_rand($fonts)];
            $color = $textColors[array_rand($textColors)];

            imagettftext(
                $image,
                $fontSize,
                $angle,
                $x,
                $y,
                $color,
                $font,
                $code[$i]
            );

            $x += rand(26, 32);
        }

        // =============================
        // 8. Output
        // =============================
        ob_start();
        imagepng($image);
        $content = ob_get_clean();

        imagedestroy($image);

        return response($content, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
