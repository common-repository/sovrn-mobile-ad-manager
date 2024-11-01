<?php

$settingsKey = $this->model->getSettingsKey();
$inputType = $field['input'];
$fieldName = $field['name'];
$fieldInputName = sprintf('%s[%s]', $this->model->getSettingsKey(), $fieldName);

// Get field value

$fieldValue = null;

// If it's been set, grab the value, otherwise check for a default

if ($this->settings && array_key_exists($fieldName, $this->settings) && $this->settings[$fieldName] != '' && !is_null($this->settings[$fieldName])) {
    $fieldValue = $this->settings[$fieldName];
} else if (array_key_exists('default', $field) && $field['default'] != '' && !is_null($field['default'])) {
    $fieldValue = $field['default'];
}

?>
<?php if ($inputType == 'select'): ?>
    <select name="<?php echo $fieldInputName ?>">
        <?php foreach ($field['options'] as $value => $label): ?>
        <option value="<?php echo $value; ?>"<?php if ($value == $fieldValue): ?> selected<?php endif ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
    </select>
<?php elseif ($inputType == 'textarea'): ?>
    <textarea name="<?php echo $fieldInputName ?>"><?php if ($fieldValue) { echo $fieldValue; } ?></textarea>
<?php elseif ($inputType == 'checkbox'): ?>
    <input type="checkbox" name="<?php echo $fieldInputName ?>" value="1" <?php checked($fieldValue, 1); ?>>
<?php else: ?>
    <input type="<?php echo $inputType ?>" name="<?php echo $fieldInputName ?>">
<?php endif; ?>