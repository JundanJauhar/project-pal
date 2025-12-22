<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class CaptchaController extends Controller
{
    public function generate()
    {
        $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5));

        Session::put('captcha_code', $code);

        $image = imagecreatetruecolor(130, 40);
        $bg = imagecolorallocate($image, 255, 255, 255);
        $text = imagecolorallocate($image, 50, 50, 50);

        imagefilledrectangle($image, 0, 0, 130, 40, $bg);

        imagestring($image, 5, 22, 10, $code, $text);

        ob_start();
        imagepng($image);
        $content = ob_get_clean();

        imagedestroy($image);

        return new Response($content, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
