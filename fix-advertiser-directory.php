<?php
/**
 * Quick Fix Script for Advertiser View Directory
 * 
 * Run this script on your production server via SSH:
 * php fix-advertiser-directory.php
 * 
 * Or upload it to your Laravel root and access via browser (remove after use!)
 */

// Get the base path
$basePath = __DIR__;
$oldPath = $basePath . '/resources/views/dashboard/Advertiser';
$tempPath = $basePath . '/resources/views/dashboard/advertiser_temp';
$newPath = $basePath . '/resources/views/dashboard/advertiser';

echo "=== Fixing Advertiser Directory Case Issue ===\n\n";

// Check if old directory exists (capital A)
if (is_dir($oldPath) && !is_dir($newPath)) {
    echo "Found 'Advertiser' directory (capital A)\n";
    echo "Renaming to 'advertiser' (lowercase)...\n";
    
    // Rename using temporary name first (for case-sensitive systems)
    if (rename($oldPath, $tempPath)) {
        echo "✓ Renamed Advertiser to advertiser_temp\n";
        if (rename($tempPath, $newPath)) {
            echo "✓ Renamed advertiser_temp to advertiser\n";
            echo "✓ Directory renamed successfully!\n\n";
        } else {
            echo "✗ ERROR: Failed to rename advertiser_temp to advertiser\n";
            // Try to restore
            rename($tempPath, $oldPath);
            echo "Restored original directory\n";
            exit(1);
        }
    } else {
        echo "✗ ERROR: Failed to rename Advertiser directory\n";
        echo "Please check file permissions\n";
        exit(1);
    }
} elseif (is_dir($newPath)) {
    echo "✓ Directory 'advertiser' already exists (correct name)\n\n";
} else {
    echo "✗ ERROR: Neither Advertiser nor advertiser directory found\n";
    echo "Please check the path: $oldPath\n";
    exit(1);
}

// Clear Laravel caches
echo "Clearing Laravel caches...\n";

$commands = [
    'view:clear' => 'View cache',
    'cache:clear' => 'Application cache',
    'config:clear' => 'Config cache',
    'route:clear' => 'Route cache',
    'optimize:clear' => 'Optimize cache',
];

foreach ($commands as $command => $name) {
    $output = [];
    $returnVar = 0;
    exec("php artisan $command 2>&1", $output, $returnVar);
    if ($returnVar === 0) {
        echo "✓ Cleared $name\n";
    } else {
        echo "✗ Failed to clear $name\n";
        echo "  Error: " . implode("\n", $output) . "\n";
    }
}

echo "\n=== Fix Complete! ===\n";
echo "Please test the advertiser dashboard now.\n";
echo "If you still see errors, try:\n";
echo "  php artisan config:cache\n";
echo "  php artisan route:cache\n";
echo "  php artisan view:cache\n";
echo "  php artisan optimize\n";

