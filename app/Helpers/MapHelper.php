<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Bogdaan\OpenLocationCode\OpenLocationCode;

class MapHelper
{
    public static function extractCoordinates(string $url): ?array
    {
        Log::info('Original URL', ['url' => $url]);

        return Cache::remember(md5($url), now()->addDays(7), function () use ($url) {
            $expandedUrl = self::expandUrl($url);
            Log::info('Expanded URL', ['expanded_url' => $expandedUrl]);

            if ($coords = self::extractFromUrl($expandedUrl)) {
                return $coords;
            }

            if ($coords = self::extractFromPage($expandedUrl)) {
                return $coords;
            }

            if ($coords = self::extractFromPlusCode($expandedUrl)) {
                return $coords;
            }

            Log::warning('Coordinates not found for URL', ['checked_url' => $expandedUrl]);
            return null;
        });
    }

    protected static function expandUrl(string $url): string
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Bot/1.0)'])
                ->head($url);

            $finalUrl = $response->effectiveUri()->__toString();
            Log::info('Final expanded URL', ['final_url' => $finalUrl]);
            return $finalUrl;
        } catch (\Throwable $e) {
            Log::warning('URL expansion failed', ['error' => $e->getMessage()]);
            return $url;
        }
    }

    protected static function extractFromUrl(string $url): ?array
    {
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return ['lat' => $m[1], 'lng' => $m[2]];
        }
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $m)) {
            return ['lat' => $m[1], 'lng' => $m[2]];
        }
        return null;
    }

    protected static function extractFromPage(string $url): ?array
    {
        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) return null;

            $html = $response->body();

            $patterns = [
                '/"lat":\s*([\-0-9.]+)\s*,\s*"lng":\s*([\-0-9.]+)/',
                '/"latitude":\s*([\-0-9.]+)\s*,\s*"longitude":\s*([\-0-9.]+)/',
                '/"center":\{"lat":([\-0-9.]+),"lng":([\-0-9.]+)\}/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $m)) {
                    Log::info('Coordinates found in page HTML', ['lat' => $m[1], 'lng' => $m[2]]);
                    return ['lat' => $m[1], 'lng' => $m[2]];
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to scrape page', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected static function extractFromPlusCode(string $url): ?array
    {
        if (preg_match('/([23456789CFGHJMPQRVWX]{4,}\+[23456789CFGHJMPQRVWX]{2,})/i', urldecode($url), $match)) {
            $plusCode = strtoupper(trim($match[1]));
            Log::info('Detected Plus Code', ['plus_code' => $plusCode]);

            return self::decodePlusCodeLocally($plusCode);
        }

        return null;
    }

    protected static function decodePlusCodeLocally(string $plusCode): ?array
    {
        try {
            $olc = new OpenLocationCode();

            if ($olc->isFull($plusCode)) {
                $area = $olc->decode($plusCode);
            } elseif ($olc->isShort($plusCode)) {
                $referenceLat = 36.7525;  // Algiers
                $referenceLng = 3.0420;

                $fullCode = $olc->recover($plusCode, $referenceLat, $referenceLng);
                if (!$fullCode) {
                    Log::warning('Failed to recover short Plus Code', ['plus_code' => $plusCode]);
                    return null;
                }

                Log::info('Recovered full Plus Code', [
                    'short' => $plusCode,
                    'full' => $fullCode
                ]);

                $area = $olc->decode($fullCode);
            } else {
                Log::warning('Invalid Plus Code format', ['plus_code' => $plusCode]);
                return null;
            }

            $lat = ($area['latitudeLo'] + $area['latitudeHi']) / 2;
            $lng = ($area['longitudeLo'] + $area['longitudeHi']) / 2;

            $lat = round($lat, 6);
            $lng = round($lng, 6);

            Log::info('Coordinates from Plus Code', [
                'plus_code' => $plusCode,
                'lat' => $lat,
                'lng' => $lng
            ]);

            return ['lat' => (string)$lat, 'lng' => (string)$lng];

        } catch (\Throwable $e) {
            Log::error('Plus Code decode error', [
                'plus_code' => $plusCode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}