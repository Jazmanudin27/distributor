<?php
// Set headers
header('Content-Type: image/png');
header('Cache-Control: public, max-age=604800');

// Get size parameter
$size = isset($_GET['size']) ? intval($_GET['size']) : 192;
if ($size !== 512) {
    $size = 192; // Only allow 192 or 512, default to 192
}

// Check if GD is available
if (!extension_loaded('gd')) {
    // Fallback if GD is missing: serve a simple pre-made SVG-like header or a basic PNG if possible, 
    // but in modern environment GD is usually there. Let's create a minimal valid PNG or error gracefully.
    // For safety, let's output a basic 1x1 PNG or write a warning. But let's assume GD is available.
}

// Create image
$im = imagecreatetruecolor($size, $size);

// Enable alpha blending
imagealphablending($im, true);
imagesavealpha($im, true);

// Color definitions (Gradient: Purple #6366f1 to Indigo #a855f7)
// Start color: 99, 102, 241
// End color: 168, 85, 247
$r1 = 99;  $g1 = 102; $b1 = 241;
$r2 = 168; $g2 = 85;  $b2 = 247;

// Draw gradient background row by row
for ($y = 0; $y < $size; $y++) {
    $ratio = $y / $size;
    $r = intval($r1 + ($r2 - $r1) * $ratio);
    $g = intval($g1 + ($g2 - $g1) * $ratio);
    $b = intval($b1 + ($b2 - $b1) * $ratio);
    
    $color = imagecolorallocate($im, $r, $g, $b);
    imageline($im, 0, $y, $size - 1, $y, $color);
}

// Allocate White for the logo
$white = imagecolorallocate($im, 255, 255, 255);

// Logo Parameters
$cx = $size / 2;
$cy = $size / 2;

// Thickness of the 'D'
$thickness = $size * 0.08;

// Vertical bar coordinates
$x_left = intval($size * 0.35);
$x_mid = intval($size * 0.45);
$r_outer = $size * 0.22;
$r_inner = $r_outer - $thickness;

$y_top = intval($cy - $r_outer);
$y_bottom = intval($cy + $r_outer);

// 1. Draw outer vertical bar
imagefilledrectangle($im, $x_left, $y_top, $x_mid, $y_bottom, $white);

// 2. Draw outer semi-circle (arc) on the right side of the vertical bar
imagefilledarc($im, $x_mid, $cy, intval($r_outer * 2), intval($r_outer * 2), 270, 90, $white, IMG_ARC_PIE);

// 3. Make it hollow by drawing the inner part row-by-row using the background gradient colors
for ($y = intval($cy - $r_inner); $y <= intval($cy + $r_inner); $y++) {
    if ($y < 0 || $y >= $size) continue;
    
    $dy = $y - $cy;
    $r_inner_sq = $r_inner * $r_inner;
    
    if (($dy * $dy) <= $r_inner_sq) {
        $dx = intval(sqrt($r_inner_sq - ($dy * $dy)));
        
        // Re-calculate the gradient color for this specific row
        $ratio = $y / $size;
        $r = intval($r1 + ($r2 - $r1) * $ratio);
        $g = intval($g1 + ($g2 - $g1) * $ratio);
        $b = intval($b1 + ($b2 - $b1) * $ratio);
        
        $bg_row_color = imagecolorallocate($im, $r, $g, $b);
        
        // Draw the horizontal slice of the inner hollow area
        // We extend it slightly left (to $x_mid - 1) and right to avoid subpixel gaps
        imageline($im, $x_mid - 1, $y, $x_mid + $dx, $y, $bg_row_color);
    }
}

// Output PNG
imagepng($im);
imagedestroy($im);
