<?php
// Ondersteunt losse "extra velden" op producten/categorieën/portfolio/usps/
// reviews/faqs: een super admin definieert per website welke extra velden
// een bepaald type item heeft (super_velden.php), en de bestaande
// beheerpagina's (productbeheer.php, websitebeheer.php) vangen de waarden op
// bij het aanmaken van een item en tonen ze read-only op bestaande items —
// consistent met hoe die pagina's nu al werken (aanmaken + verwijderen, geen
// aparte bewerk-modus per item).

const CUSTOM_FIELD_ENTITY_TYPES = [
    'product'   => 'Producten / Diensten',
    'category'  => 'Categorieën',
    'portfolio' => 'Portfolio',
    'usp'       => "USP's",
    'review'    => 'Reviews',
    'faq'       => "FAQ's",
];

const CUSTOM_FIELD_TYPES = [
    'text'     => 'Tekst (kort)',
    'textarea' => 'Tekst (lang)',
    'number'   => 'Getal',
    'boolean'  => 'Ja / Nee',
    'url'      => 'Link (URL)',
];

function get_custom_field_definitions($entity_type)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM custom_field_definitions WHERE website_id = ? AND entity_type = ? ORDER BY sort_order ASC, label ASC");
    $stmt->execute([current_website_id(), $entity_type]);
    return $stmt->fetchAll();
}

// Rendert de invoervelden voor een "nieuw item toevoegen"-formulier.
// $values is optioneel: [field_id => waarde] om vooraf in te vullen.
function render_custom_field_inputs($entity_type, array $values = [])
{
    $definitions = get_custom_field_definitions($entity_type);
    if (empty($definitions)) {
        return;
    }
    echo '<div class="border-t border-gray-100 pt-4 mt-4 space-y-4">';
    echo '<h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Extra velden</h4>';
    foreach ($definitions as $def) {
        $value = $values[$def['id']] ?? '';
        $name = 'custom_fields[' . htmlspecialchars($def['id']) . ']';
        echo '<div>';
        echo '<label class="block text-sm font-medium text-gray-700 mb-1">' . htmlspecialchars($def['label']) . '</label>';
        switch ($def['field_type']) {
            case 'textarea':
                echo '<textarea name="' . $name . '" rows="3" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">' . htmlspecialchars($value) . '</textarea>';
                break;
            case 'number':
                echo '<input type="number" step="any" name="' . $name . '" value="' . htmlspecialchars($value) . '" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">';
                break;
            case 'boolean':
                $checked = $value === '1' ? 'checked' : '';
                echo '<label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" name="' . $name . '" value="1" ' . $checked . ' class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"><span class="text-sm text-gray-600">Ingeschakeld</span></label>';
                break;
            case 'url':
                echo '<input type="url" name="' . $name . '" value="' . htmlspecialchars($value) . '" placeholder="https://" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">';
                break;
            default:
                echo '<input type="text" name="' . $name . '" value="' . htmlspecialchars($value) . '" class="w-full border-gray-300 rounded-md border p-2 focus:border-pink-500 focus:ring-pink-500">';
        }
        echo '</div>';
    }
    echo '</div>';
}

// Slaat de geposte custom_fields[field_id] waarden op voor één item (nieuw
// aangemaakt, dus alle definities worden als nieuwe rij ingevoegd).
function save_custom_field_values($entity_type, $entity_id, array $posted)
{
    global $pdo;
    $definitions = get_custom_field_definitions($entity_type);
    if (empty($definitions)) {
        return;
    }
    $insert = $pdo->prepare("INSERT INTO custom_field_values (id, field_id, entity_id, value) VALUES (UUID(), ?, ?, ?)");
    foreach ($definitions as $def) {
        if ($def['field_type'] === 'boolean') {
            $value = isset($posted[$def['id']]) ? '1' : '0';
        } else {
            $value = trim((string)($posted[$def['id']] ?? ''));
        }
        if ($value === '') {
            continue;
        }
        $insert->execute([$def['id'], $entity_id, $value]);
    }
}

// Haalt in één query de ingevulde extra velden op voor een lijst items,
// klaar om als badges te tonen: [entity_id => [['label'=>..,'value'=>..], ...]]
function get_custom_field_value_map($entity_type, array $entity_ids)
{
    global $pdo;
    if (empty($entity_ids)) {
        return [];
    }
    $definitions = get_custom_field_definitions($entity_type);
    if (empty($definitions)) {
        return [];
    }
    $field_ids = array_column($definitions, 'id');
    $field_labels = array_column($definitions, 'label', 'id');
    $field_types = array_column($definitions, 'field_type', 'id');

    $in_fields = implode(',', array_fill(0, count($field_ids), '?'));
    $in_entities = implode(',', array_fill(0, count($entity_ids), '?'));
    $stmt = $pdo->prepare("SELECT field_id, entity_id, value FROM custom_field_values WHERE field_id IN ($in_fields) AND entity_id IN ($in_entities)");
    $stmt->execute(array_merge($field_ids, $entity_ids));

    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        if ($row['value'] === '' || $row['value'] === null) {
            continue;
        }
        if ($field_types[$row['field_id']] === 'boolean' && $row['value'] !== '1') {
            continue;
        }
        $map[$row['entity_id']][] = [
            'label' => $field_labels[$row['field_id']],
            'value' => $field_types[$row['field_id']] === 'boolean' ? 'Ja' : $row['value'],
        ];
    }
    return $map;
}

function delete_custom_field_values_for_entity($entity_id)
{
    global $pdo;
    $pdo->prepare("DELETE FROM custom_field_values WHERE entity_id = ?")->execute([$entity_id]);
}
