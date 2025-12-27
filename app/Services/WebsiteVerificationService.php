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
            // Fetch the website HTML
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; AdsNetworkBot/1.0; +https://' . config('app.url') . '/)',
                ])
                ->get($url);

            if (!$response->successful()) {
                return [
                    'verified' => false,
                    'message' => 'Could not access website. Please ensure your website is accessible.',
                ];
            }

            $html = $response->body();
            
            // Check for the meta tag
            $expectedMetaTag = sprintf(
                '<meta name="ads-network-verification" content="%s">',
                $website->verification_code
            );
            
            $expectedMetaTagAlt = sprintf(
                '<meta name="ads-network-verification" content=\'%s\'>',
                $website->verification_code
            );

            // Also check without quotes (some CMS might strip them)
            $pattern = sprintf(
                '/<meta\s+name=["\']?ads-network-verification["\']?\s+content=["\']?%s["\']?\s*\/?>/i',
                preg_quote($website->verification_code, '/')
            );

            if (preg_match($pattern, $html) || 
                strpos($html, $expectedMetaTag) !== false || 
                strpos($html, $expectedMetaTagAlt) !== false) {
                
                $website->update([
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                ]);

                return [
                    'verified' => true,
                    'message' => 'Website verified successfully!',
                ];
            }

            return [
                'verified' => false,
                'message' => 'Verification meta tag not found. Please ensure you have added the meta tag to the <head> section of your website.',
            ];
        } catch (Exception $e) {
            return [
                'verified' => false,
                'message' => 'Could not verify website: ' . $e->getMessage(),
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
        
        // Remove protocol if present
        $domain = preg_replace('#^https?://#', '', $domain);
        
        // Remove trailing slash
        $domain = rtrim($domain, '/');
        
        // For localhost/127.0.0.1, keep as-is (with port and path if present)
        if (preg_match('/^(localhost|127\.0\.0\.1|0\.0\.0\.0)/i', $domain)) {
            // Add http:// for localhost (https usually doesn't work on localhost)
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

