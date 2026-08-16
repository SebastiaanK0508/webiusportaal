<?php
// Wordt vanuit de <head> van elke pagina geïncludeerd (niet vanuit header.php,
// dat voorkwam voorheen een dubbele <head>/dubbele Tailwind-CDN-load).
$__branding = function_exists('current_website') ? current_website() : null;
$brand_primary   = $__branding['color_primary'] ?? '#db2777';   // pink-600, huidige huisstijl als fallback
$brand_secondary = $__branding['color_secondary'] ?? '#fce7f3'; // pink-100
$brand_accent    = $__branding['color_accent'] ?? '#1f2937';    // slate-800
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<style>
    :root {
        --brand-primary: <?php echo htmlspecialchars($brand_primary); ?>;
        --brand-secondary: <?php echo htmlspecialchars($brand_secondary); ?>;
        --brand-accent: <?php echo htmlspecialchars($brand_accent); ?>;
    }
</style>
<script>
    function openTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-pink-600', 'text-pink-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById(tabName).classList.remove('hidden');
        document.getElementById('btn-' + tabName).classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('btn-' + tabName).classList.add('border-pink-600', 'text-pink-600');
    }
</script>
