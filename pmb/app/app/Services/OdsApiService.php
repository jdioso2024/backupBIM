<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Read-only HTTP client for the ODS (PMB Terpadu) monitor API.
 *
 * Configure in .env:
 *   ODS_API_BASE=https://ods.bim.ac.id        # no trailing slash
 *   ODS_API_TOKEN=<same value as ODS PMB_API_TOKEN>
 *   ODS_API_CACHE_TTL=300                      # seconds, default 5 min
 */
class OdsApiService
{
    private string $base;
    private string $token;
    private int $cacheTtl;

    public function __construct()
    {
        $this->base     = rtrim(config('services.ods.base', ''), '/');
        $this->token    = config('services.ods.token', '');
        $this->cacheTtl = (int) config('services.ods.cache_ttl', 300);
    }

    /**
     * Fetch JSON from an ODS monitor endpoint, with caching.
     * Returns the decoded array, or null on failure (log the error).
     */
    private function get(string $path): ?array
    {
        $cacheKey = 'ods_api_' . md5($path);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($path) {
            try {
                $response = Http::withHeaders(['X-PMB-Token' => $this->token])
                    ->timeout(10)
                    ->get("{$this->base}/api/monitor/{$path}");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error("ODS API {$path} returned {$response->status()}");
            } catch (ConnectionException $e) {
                Log::error("ODS API connection failed for {$path}: {$e->getMessage()}");
            }

            return null;
        });
    }

    public function rekap(): ?array
    {
        return $this->get('rekap/');
    }

    public function rekapPasca(): ?array
    {
        return $this->get('rekap-pasca/');
    }

    public function programStudi(): ?array
    {
        return $this->get('program-studi/');
    }

    public function laporanRegistrasi(): ?array
    {
        return $this->get('laporan-registrasi/');
    }

    public function dataDetail(): ?array
    {
        return $this->get('data-detail/');
    }

    public function perbandinganTahun(): ?array
    {
        return $this->get('perbandingan-tahun/');
    }

    public function sebaranDomisili(): ?array
    {
        return $this->get('sebaran-domisili/');
    }

    public function sebaranSekolah(): ?array
    {
        return $this->get('sebaran-sekolah/');
    }
}
