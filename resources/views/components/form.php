<?php
function form_input($name, $label, $type = 'text', $value = null, $attributes = []) {
    $value = $value ?? ($_SESSION['old'][$name] ?? '');
    $id = 'input_' . $name;
    
    $attributeString = '';
    foreach ($attributes as $key => $val) {
        $attributeString .= " {$key}=\"" . htmlspecialchars($val) . "\"";
    }
    
    echo "
    <div class=\"form-floating mb-3\">
        <input type=\"{$type}\" 
               class=\"form-control\" 
               id=\"{$id}\" 
               name=\"{$name}\" 
               placeholder=\"{$label}\"
               value=\"" . htmlspecialchars($value) . "\"
               {$attributeString}>
        <label for=\"{$id}\">{$label}</label>
    </div>";
}

function form_textarea($name, $label, $value = null, $rows = 3, $attributes = []) {
    $value = $value ?? ($_SESSION['old'][$name] ?? '');
    $id = 'textarea_' . $name;
    
    $attributeString = '';
    foreach ($attributes as $key => $val) {
        $attributeString .= " {$key}=\"" . htmlspecialchars($val) . "\"";
    }
    
    echo "
    <div class=\"form-floating mb-3\">
        <textarea class=\"form-control\" 
                  id=\"{$id}\" 
                  name=\"{$name}\" 
                  placeholder=\"{$label}\"
                  style=\"height: " . ($rows * 2.5) . "rem\"
                  {$attributeString}>" . htmlspecialchars($value) . "</textarea>
        <label for=\"{$id}\">{$label}</label>
    </div>";
}

function form_select($name, $label, $options = [], $selected = null, $attributes = []) {
    $selected = $selected ?? ($_SESSION['old'][$name] ?? '');
    $id = 'select_' . $name;
    
    $attributeString = '';
    foreach ($attributes as $key => $val) {
        $attributeString .= " {$key}=\"" . htmlspecialchars($val) . "\"";
    }
    
    echo "<div class=\"form-floating mb-3\">
        <select class=\"form-select\" id=\"{$id}\" name=\"{$name}\" {$attributeString}>
            <option value=\"\">Seleziona {$label}</option>";
    
    foreach ($options as $value => $text) {
        $selectedAttr = ($value == $selected) ? 'selected' : '';
        echo "<option value=\"" . htmlspecialchars($value) . "\" {$selectedAttr}>" . htmlspecialchars($text) . "</option>";
    }
    
    echo "</select>
        <label for=\"{$id}\">{$label}</label>
    </div>";
}

function form_checkbox($name, $label, $checked = false, $value = '1', $attributes = []) {
    $checked = $checked || ($_SESSION['old'][$name] ?? false);
    $id = 'checkbox_' . $name;
    
    $attributeString = '';
    foreach ($attributes as $key => $val) {
        $attributeString .= " {$key}=\"" . htmlspecialchars($val) . "\"";
    }
    
    echo "
    <div class=\"form-check mb-3\">
        <input class=\"form-check-input\" 
               type=\"checkbox\" 
               id=\"{$id}\" 
               name=\"{$name}\" 
               value=\"{$value}\"
               " . ($checked ? 'checked' : '') . "
               {$attributeString}>
        <label class=\"form-check-label\" for=\"{$id}\">
            {$label}
        </label>
    </div>";
}

// Pulisci i dati old dopo l'uso
if (isset($_SESSION['old'])) {
    unset($_SESSION['old']);
}
?>