<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WebsiteVerificationService
{
    /**
     * Verify website ownership using the selected verification method.
     *
     * @param Website $website
     * @return array
     */
    public function verify(Website $website): array
    {
        try {
            switch ($website->verification_method) {
                case 'meta_tag':
                    return $this->verifyMetaTag($website);
                case 'file_upload':
                    return $this->verifyFileUpload($website);
                default:
                    return [
                        'verified' => false,
                        'message' => 'Unknown verification method.',
                    ];
            }
        } catch (Exception $e) {
            Log::error('Website verification failed', [
                'website_id' => $website->id,
                'domain' => $website->domain,
                'error' => $e->getMessage(),
            ]);

            return [
                'verified' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify website using meta tag method.
     *
     * @param Website $website
     * @return array
     */
    protected function verifyMetaTag(Website $website): array
    {
        $url = $this->normalizeDomain($website->domain);
        
        try {
            // Log the URL being fetched for debugging
            Log::info('Verifying website', [
                'website_id' => $website->id,
                'domain' => $website->domain,
                'normalized_url' => $url,
                'verification_code' => $website->verification_code,
            ]);
            
            // Fetch the website HTML
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; AdsNetworkBot/1.0; +https://' . config('app.url') . '/)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Website verification failed - HTTP error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 200),
                ]);
                
                return [
                    'verified' => false,
                    'message' => 'Could not access website (HTTP ' . $response->status() . '). Please ensure your website is accessible at: ' . $url,
                ];
            }

            $html = $response->body();
            
            // Log HTML snippet for debugging (first 500 chars)
            Log::info('Website HTML fetched', [
                'url' => $url,
                'html_length' => strlen($html),
                'html_preview' => substr($html, 0, 500),
            ]);
            
            // More flexible pattern matching - handles various formats
            $verificationCode = preg_quote($website->verification_code, '/');
            
            // Pattern 1: Standard format with double quotes
            $pattern1 = '/<meta\s+name\s*=\s*["\']ads-network-verification["\']\s+content\s*=\s*["\']' . $verificationCode . '["\']\s*\/?>/i';
            
            // Pattern 2: Single quotes
            $pattern2 = '/<meta\s+name\s*=\s*[\'"]ads-network-verification[\'"]\s+content\s*=\s*[\'"]' . $verificationCode . '[\'"]\s*\/?>/i';
            
            // Pattern 3: No quotes around name
            $pattern3 = '/<meta\s+name\s*=\s*ads-network-verification\s+content\s*=\s*["\']' . $verificationCode . '["\']\s*\/?>/i';
            
            // Pattern 4: Very flexible - any order, any spacing
            $pattern4 = '/<meta[^>]*name\s*=\s*["\']?ads-network-verification["\']?[^>]*content\s*=\s*["\']?' . $verificationCode . '["\']?[^>]*>/i';
            
            // Also check simple string match
            $hasMetaTag = preg_match($pattern1, $html) || 
                         preg_match($pattern2, $html) || 
                         preg_match($pattern3, $html) || 
                         preg_match($pattern4, $html) ||
                         strpos($html, 'ads-network-verification') !== false && strpos($html, $website->verification_code) !== false;

            if ($hasMetaTag) {
                // Double check - extract meta tags and verify
                preg_match_all('/<meta[^>]*>/i', $html, $metaTags);
                foreach ($metaTags[0] as $metaTag) {
                    if (stripos($metaTag, 'ads-network-verification') !== false && 
                        stripos($metaTag, $website->verification_code) !== false) {
                        
                        $website->update([
                            'verification_status' => 'verified',
                            'verified_at' => now(),
                        ]);

                        Log::info('Website verified successfully', [
                            'website_id' => $website->id,
                            'verification_code' => $website->verification_code,
                        ]);

                        return [
                            'verified' => true,
                            'message' => 'Website verified successfully!',
                        ];
                    }
                }
            }

            // Log what was found for debugging
            preg_match_all('/<meta[^>]*name\s*=\s*["\']?ads-network-verification["\']?[^>]*>/i', $html, $foundMetaTags);
            Log::warning('Verification meta tag not found', [
                'url' => $url,
                'expected_code' => $website->verification_code,
                'found_meta_tags' => $foundMetaTags[0],
                'html_contains_code' => strpos($html, $website->verification_code) !== false,
            ]);

            return [
                'verified' => false,
                'message' => 'Verification meta tag not found. Please ensure you have added the meta tag to the <head> section of your website. Expected code: ' . $website->verification_code,
            ];
        } catch (Exception $e) {
            Log::error('Website verification exception', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'verified' => false,
                'message' => 'Could not verify website: ' . $e->getMessage() . '. Please ensure the URL is accessible: ' . $url,
            ];
        }
    }

    /**
     * Verify website using file upload method.
     *
     * @param Website $website
     * @return array
     */
    protected function verifyFileUpload(Website $website): array
    {
        $baseUrl = $this->normalizeDomain($website->domain);
        $filename = "ads-network-verification-{$website->verification_code}.html";
        
        // For localhost with path, append filename to existing path
        // For regular domains, append to root
        if (strpos($baseUrl, '/') !== false && preg_match('/^(http:\/\/)?(localhost|127\.0\.0\.1)/i', $baseUrl)) {
            // Localhost with path - append filename
            $url = rtrim($baseUrl, '/') . '/' . $filename;
        } else {
            // Regular domain - append to root
            $url = rtrim($baseUrl, '/') . '/' . $filename;
        }
        
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; AdsNetworkBot/1.0)',
                ])
                ->get($url);

            if ($response->successful()) {
                $content = $response->body();
                
                // Check if the file contains the verification code
                if (strpos($content, $website->verification_code) !== false) {
                    $website->update([
                        'verification_status' => 'verified',
                        'verified_at' => now(),
                    ]);

                    return [
                        'verified' => true,
                        'message' => 'Website verified successfully!',
                    ];
                }
            }

            return [
                'verified' => false,
                'message' => "Verification file not found. Please upload the file '{$filename}' to your website root directory.",
            ];
        } catch (Exception $e) {
            return [
                'verified' => false,
                'message' => 'Could not verify website: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Normalize domain (add protocol, handle localhost, ports, paths)
     *
     * @param string $domain
     * @return string
     */
    protected function normalizeDomain(string $domain): string
    {
        $domain = trim($domain);
        
        // If already has protocol, return as-is
        if (preg_match('#^https?://#', $domain)) {
            return $domain;
        }
        
        // Remove trailing slash (but keep path)
        $domain = rtrim($domain, '/');
        
        // For localhost/127.0.0.1, add http:// (with port and path if present)
        if (preg_match('/^(localhost|127\.0\.0\.1|0\.0\.0\.0|::1)/i', $domain)) {
            return 'http://' . $domain;
        }
        
        // For regular domains, add https:// (default)
        return 'https://' . $domain;
    }

    /**
     * Get verification file content for file upload method.
     *
     * @param Website $website
     * @return string
     */
    public function getVerificationFileContent(Website $website): string
    {
        $filename = "ads-network-verification-{$website->verification_code}.html";
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Ads Network Verification</title>
    <meta name="ads-network-verification" content="{$website->verification_code}">
</head>
<body>
    <h1>Ads Network Verification</h1>
    <p>Verification Code: <strong>{$website->verification_code}</strong></p>
    <p>Domain: <strong>{$website->domain}</strong></p>
    <p>This file is used to verify domain ownership for Ads Network.</p>
    <p>You can delete this file after verification is complete.</p>
</body>
</html>
HTML;
    }

}

