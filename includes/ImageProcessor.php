<?php
/**
 * Image Processor Helper
 * Håndterer billedbehandling inkl. ansigts-sløring
 */

namespace RTF\Platform;

class ImageProcessor {
    
    /**
     * Blur ansigter i billede (GDPR-compliant)
     * Returnerer sti til det blur'ede billede
     */
    public static function blurFaces($imagePath, $blurIntensity = 20) {
        if (!file_exists($imagePath)) {
            throw new \Exception('Billede ikke fundet: ' . $imagePath);
        }
        
        // Check GD library
        if (!function_exists('imagecreatetruecolor')) {
            throw new \Exception('GD library ikke tilgængelig');
        }
        
        // Load billede baseret på type
        $imageInfo = getimagesize($imagePath);
        $mimeType = $imageInfo['mime'];
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                throw new \Exception('Ikke-understøttet billedformat');
        }
        
        if (!$image) {
            throw new \Exception('Kunne ikke loade billede');
        }
        
        // Få billedstørrelse
        $width = imagesx($image);
        $height = imagesy($image);
        
        // NOTE: Real face detection ville kræve OpenCV eller cloud API
        // Her laver vi en placeholder implementation der blur'er hele billedet
        // I produktion skal dette integreres med Google Cloud Vision, AWS Rekognition eller Face++ API
        
        // PLACEHOLDER: Blur hele billedet
        // I en real implementation ville vi:
        // 1. Detektere ansigter med ML model
        // 2. Få bounding boxes for hvert ansigt
        // 3. Kun blur inden for disse områder
        
        $blurred = self::applyGaussianBlur($image, $blurIntensity);
        
        // Gem blurred billede
        $pathInfo = pathinfo($imagePath);
        $blurredPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_blurred.' . $pathInfo['extension'];
        
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($blurred, $blurredPath, 90);
                break;
            case 'image/png':
                imagepng($blurred, $blurredPath, 9);
                break;
            case 'image/gif':
                imagegif($blurred, $blurredPath);
                break;
        }
        
        // Cleanup
        imagedestroy($image);
        imagedestroy($blurred);
        
        return $blurredPath;
    }
    
    /**
     * Anvend Gaussian blur filter
     */
    private static function applyGaussianBlur($image, $intensity) {
        // PHP GD's imagefilter med IMG_FILTER_GAUSSIAN_BLUR
        // Intensity: hvor mange gange blur skal køres (1-30)
        $blurred = $image;
        
        for ($i = 0; $i < $intensity; $i++) {
            imagefilter($blurred, IMG_FILTER_GAUSSIAN_BLUR);
        }
        
        return $blurred;
    }
    
    /**
     * Blur specifikt område af billede (når vi har face detection)
     */
    public static function blurRegion($imagePath, $x, $y, $width, $height, $blurIntensity = 15) {
        $imageInfo = getimagesize($imagePath);
        $mimeType = $imageInfo['mime'];
        
        // Load billede
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            default:
                throw new \Exception('Ikke-understøttet billedformat');
        }
        
        // Udtræk region
        $region = imagecreatetruecolor($width, $height);
        imagecopy($region, $image, 0, 0, $x, $y, $width, $height);
        
        // Blur region
        for ($i = 0; $i < $blurIntensity; $i++) {
            imagefilter($region, IMG_FILTER_GAUSSIAN_BLUR);
        }
        
        // Indsæt blurred region tilbage
        imagecopy($image, $region, $x, $y, 0, 0, $width, $height);
        
        // Cleanup
        imagedestroy($region);
        
        return $image;
    }
    
    /**
     * Resize billede til thumbnail
     */
    public static function createThumbnail($imagePath, $maxWidth = 300, $maxHeight = 300) {
        $imageInfo = getimagesize($imagePath);
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Beregn nye dimensioner (behold aspect ratio)
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Load original
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($imagePath);
                break;
            default:
                throw new \Exception('Ikke-understøttet billedformat');
        }
        
        // Opret thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Bevar transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        // Resize
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Gem thumbnail
        $pathInfo = pathinfo($imagePath);
        $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($thumbnail, $thumbPath, 85);
                break;
            case 'image/png':
                imagepng($thumbnail, $thumbPath, 8);
                break;
        }
        
        // Cleanup
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return $thumbPath;
    }
    
    /**
     * Optimér billede filstørrelse
     */
    public static function optimize($imagePath, $quality = 85) {
        $imageInfo = getimagesize($imagePath);
        $mimeType = $imageInfo['mime'];
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                imagejpeg($image, $imagePath, $quality);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                imagepng($image, $imagePath, round($quality / 10));
                break;
        }
        
        if (isset($image)) {
            imagedestroy($image);
        }
        
        return filesize($imagePath);
    }
    
    /**
     * Validér billede
     */
    public static function validate($filePath) {
        $errors = [];
        
        if (!file_exists($filePath)) {
            $errors[] = 'Fil ikke fundet';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024;
        if (filesize($filePath) > $maxSize) {
            $errors[] = 'Billede er for stort (max 5MB)';
        }
        
        // Check image type
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            $errors[] = 'Ikke et gyldigt billede';
            return ['valid' => false, 'errors' => $errors];
        }
        
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
        if (!in_array($imageInfo[2], $allowedTypes)) {
            $errors[] = 'Ikke-understøttet billedformat (tilladt: JPEG, PNG, GIF)';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime']
        ];
    }
}
