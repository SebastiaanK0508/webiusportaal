<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
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
</head>
</html>