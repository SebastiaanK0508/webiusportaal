<?php
// Google Analytics 4 (Data API) voor het dashboard. Overgenomen uit
// debandijk/beheer/dashboard.php, maar per website: het property-ID komt uit
// websites.ga_property_id. Alle websites delen één service-account.

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Analytics\Data\V1beta\RunReportRequest;

const ANALYTICS_CACHE_SECONDS = 300;

function analytics_credentials_file(): string
{
    $path = $_ENV['GA_CREDENTIALS_FILE'] ?? '';
    return $path !== '' ? $path : __DIR__ . '/../credentials/analytics-key.json';
}

/**
 * Bezoekers van de afgelopen $days dagen plus het aantal live bezoekers.
 * Geeft null terug als de website geen property-ID heeft. Bij een fout
 * bevat de array een 'error'-sleutel in plaats van data, zodat het
 * dashboard gewoon blijft werken.
 *
 * @return array{labels: string[], data: int[], total: int, active_now: int, error?: string}|null
 */
function get_website_analytics(?array $website, int $days = 14): ?array
{
    $property_id = trim((string)($website['ga_property_id'] ?? ''));
    if ($property_id === '' || !ctype_digit($property_id)) {
        return null;
    }

    // Kort in de sessie cachen: de GA-API is traag en het dashboard is de
    // startpagina na elke login/siteswitch.
    $cache_key = $website['id'] . ':' . $property_id . ':' . $days;
    $cached = $_SESSION['analytics_cache'][$cache_key] ?? null;
    if ($cached && $cached['at'] > time() - ANALYTICS_CACHE_SECONDS) {
        return $cached['result'];
    }

    $credentials = analytics_credentials_file();
    if (!is_readable($credentials)) {
        return ['labels' => [], 'data' => [], 'total' => 0, 'active_now' => 0, 'error' => 'Analytics-sleutel ontbreekt op de server'];
    }

    $labels = [];
    $data = [];
    $active_now = 0;
    try {
        $client = new BetaAnalyticsDataClient(['credentials' => $credentials]);

        $request = (new RunReportRequest())
            ->setProperty('properties/' . $property_id)
            ->setDateRanges([new DateRange(['start_date' => ($days - 1) . 'daysAgo', 'end_date' => 'today'])])
            ->setDimensions([new Dimension(['name' => 'date'])])
            ->setMetrics([new Metric(['name' => 'activeUsers'])])
            ->setOrderBys([new OrderBy(['dimension' => new OrderBy\DimensionOrderBy(['dimension_name' => 'date'])])]);
        foreach ($client->runReport($request)->getRows() as $row) {
            $labels[] = date('d M', strtotime($row->getDimensionValues()[0]->getValue()));
            $data[] = (int)$row->getMetricValues()[0]->getValue();
        }
        if (!$labels) {
            $labels = [date('d M')];
            $data = [0];
        }

        $realtime = (new RunRealtimeReportRequest())
            ->setProperty('properties/' . $property_id)
            ->setMetrics([new Metric(['name' => 'activeUsers'])]);
        $rows = $client->runRealtimeReport($realtime)->getRows();
        if (count($rows) > 0) {
            $active_now = (int)$rows[0]->getMetricValues()[0]->getValue();
        }
    } catch (Throwable $e) {
        error_log('Analytics fout (website ' . $website['id'] . '): ' . $e->getMessage());
        return ['labels' => [], 'data' => [], 'total' => 0, 'active_now' => 0, 'error' => 'Analytics tijdelijk offline'];
    }

    $result = ['labels' => $labels, 'data' => $data, 'total' => array_sum($data), 'active_now' => $active_now];
    $_SESSION['analytics_cache'][$cache_key] = ['at' => time(), 'result' => $result];
    return $result;
}
