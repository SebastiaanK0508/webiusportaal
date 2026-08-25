<?php
// Wordt vanuit de <head> van elke pagina geïncludeerd (niet vanuit header.php,
// dat voorkwam voorheen een dubbele <head>/dubbele Tailwind-CDN-load).
$__branding = function_exists('current_website') ? current_website() : null;
$brand_primary   = $__branding['color_primary'] ?? '#db2777';   // pink-600, huidige huisstijl als fallback
$brand_secondary = $__branding['color_secondary'] ?? '#fce7f3'; // pink-100
$brand_accent    = $__branding['color_accent'] ?? '#1f2937';    // slate-800
$brand_font      = $__branding['font_family'] ?? 'Outfit';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=<?php echo urlencode($brand_font); ?>:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        primary: 'var(--brand-primary)',
                        secondary: 'var(--brand-secondary)',
                        accent: 'var(--brand-accent)',
                    },
                },
                fontFamily: {
                    sans: ['<?php echo addslashes($brand_font); ?>', 'sans-serif'],
                },
            },
        },
    };
</script>
<style>
    :root {
        --brand-primary: <?php echo htmlspecialchars($brand_primary); ?>;
        --brand-secondary: <?php echo htmlspecialchars($brand_secondary); ?>;
        --brand-accent: <?php echo htmlspecialchars($brand_accent); ?>;
        --brand-50:  color-mix(in srgb, var(--brand-primary) 6%, white);
        --brand-100: color-mix(in srgb, var(--brand-primary) 14%, white);
        --brand-200: color-mix(in srgb, var(--brand-primary) 28%, white);
        --brand-300: color-mix(in srgb, var(--brand-primary) 45%, white);
        --brand-400: color-mix(in srgb, var(--brand-primary) 70%, white);
        --brand-500: color-mix(in srgb, var(--brand-primary) 88%, black);
        --brand-600: var(--brand-primary);
        --brand-700: color-mix(in srgb, var(--brand-primary) 85%, black);
        --brand-800: color-mix(in srgb, var(--brand-primary) 70%, black);
        --brand-900: color-mix(in srgb, var(--brand-primary) 55%, black);
    }
    body { font-family: '<?php echo htmlspecialchars($brand_font); ?>', sans-serif; }

    /* Herbrandt de hardcoded pink-*-klassen door de hele beheeromgeving naar
       de merkkleur van de actieve website (super_websites.php) — zo komt de
       gekozen huisstijl ook echt terug in de eigen adminomgeving. */
    .bg-pink-50 { background-color: var(--brand-50) !important; }
    .bg-pink-100 { background-color: var(--brand-100) !important; }
    .bg-pink-200 { background-color: var(--brand-200) !important; }
    .bg-pink-300 { background-color: var(--brand-300) !important; }
    .bg-pink-500 { background-color: var(--brand-500) !important; }
    .bg-pink-600 { background-color: var(--brand-600) !important; }
    .bg-pink-700 { background-color: var(--brand-700) !important; }
    .border-l-pink-500 { border-left-color: var(--brand-500) !important; }
    .border-pink-100 { border-color: var(--brand-100) !important; }
    .border-pink-200 { border-color: var(--brand-200) !important; }
    .border-pink-300 { border-color: var(--brand-300) !important; }
    .border-pink-400 { border-color: var(--brand-400) !important; }
    .border-pink-500 { border-color: var(--brand-500) !important; }
    .border-pink-600 { border-color: var(--brand-600) !important; }
    .ring-pink-400 { --tw-ring-color: var(--brand-400) !important; }
    .ring-pink-500 { --tw-ring-color: var(--brand-500) !important; }
    .shadow-pink-500 { --tw-shadow-color: var(--brand-500) !important; }
    .text-pink-400 { color: var(--brand-400) !important; }
    .text-pink-500 { color: var(--brand-500) !important; }
    .text-pink-600 { color: var(--brand-600) !important; }
    .text-pink-700 { color: var(--brand-700) !important; }
    .text-pink-800 { color: var(--brand-800) !important; }
    .text-pink-900 { color: var(--brand-900) !important; }
    .focus\:border-pink-400:focus { border-color: var(--brand-400) !important; }
    .focus\:border-pink-500:focus { border-color: var(--brand-500) !important; }
    .focus\:ring-pink-400:focus { --tw-ring-color: var(--brand-400) !important; }
    .focus\:ring-pink-500:focus { --tw-ring-color: var(--brand-500) !important; }
    .group:hover .group-hover\:bg-pink-50 { background-color: var(--brand-50) !important; }
    .group:hover .group-hover\:text-pink-600 { color: var(--brand-600) !important; }
    .hover\:bg-pink-50:hover { background-color: var(--brand-50) !important; }
    .hover\:bg-pink-200:hover { background-color: var(--brand-200) !important; }
    .hover\:bg-pink-600:hover { background-color: var(--brand-600) !important; }
    .hover\:bg-pink-700:hover { background-color: var(--brand-700) !important; }
    .hover\:border-pink-300:hover { border-color: var(--brand-300) !important; }
    .hover\:border-pink-500:hover { border-color: var(--brand-500) !important; }
    .hover\:text-pink-500:hover { color: var(--brand-500) !important; }
    .hover\:text-pink-600:hover { color: var(--brand-600) !important; }
    .hover\:text-pink-700:hover { color: var(--brand-700) !important; }
    .hover\:shadow-pink-500:hover { --tw-shadow-color: var(--brand-500) !important; }
    .peer:checked ~ .peer-checked\:bg-pink-500 { background-color: var(--brand-500) !important; }
    .peer:checked ~ .peer-checked\:bg-pink-600 { background-color: var(--brand-600) !important; }
    .selection\:bg-pink-200 ::selection { background-color: var(--brand-200) !important; }
    .selection\:text-pink-900 ::selection { color: var(--brand-900) !important; }
    .shadow-pink-600 { --tw-shadow-color: var(--brand-600) !important; }
</style>
