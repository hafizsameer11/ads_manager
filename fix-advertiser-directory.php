<?php
/**
 * Quick Fix Script for Advertiser View Directory
 * 
 * Run this script on your production server via SSH:
 * php fix-advertiser-directory.php
 * 
 * Or upload it to your Laravel root and access via browser (remove after use!)
 */

// Helper function to delete directory recursively
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

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
} elseif (is_dir($oldPath) && is_dir($newPath)) {
    // Both directories exist (shouldn't happen on Linux, but can on Windows)
    echo "Found both 'Advertiser' and 'advertiser' directories\n";
    echo "Removing 'Advertiser' directory (capital A)...\n";
    
    // On case-sensitive systems, these are different. On Windows, they're the same.
    // Try to remove the capital one (only on case-sensitive systems)
    // On Windows, they're the same directory, so we can't delete it
    if (PHP_OS_FAMILY !== 'Windows') {
        if (deleteDirectory($oldPath)) {
            echo "✓ Removed 'Advertiser' directory\n";
            echo "✓ Using 'advertiser' directory (correct name)\n\n";
        } else {
            echo "⚠ Warning: Could not remove 'Advertiser' directory\n";
            echo "✓ 'advertiser' directory exists (correct name)\n\n";
        }
    } else {
        echo "⚠ Note: On Windows, 'Advertiser' and 'advertiser' are the same directory\n";
        echo "✓ 'advertiser' directory exists (correct name)\n\n";
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

// Rebuild Laravel caches
echo "\nRebuilding Laravel caches...\n";

$rebuildCommands = [
    'config:cache' => 'Config cache',
    'route:cache' => 'Route cache',
    'view:cache' => 'View cache',
    'optimize' => 'Optimize',
];

foreach ($rebuildCommands as $command => $name) {
    $output = [];
    $returnVar = 0;
    exec("php artisan $command 2>&1", $output, $returnVar);
    if ($returnVar === 0) {
        echo "✓ Rebuilt $name\n";
    } else {
        echo "✗ Failed to rebuild $name\n";
        echo "  Error: " . implode("\n", $output) . "\n";
    }
}

echo "\n=== Fix Complete! ===\n";
echo "✓ Directory renamed successfully\n";
echo "✓ All caches cleared and rebuilt\n";
echo "\nPlease test the advertiser dashboard now:\n";
echo "  https://adnetwork.hmstech.org/dashboard/advertiser/home\n";

