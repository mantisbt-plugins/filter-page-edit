<?php 
header ("Content-Type: text/javascript");

require_once 'core.php';

$t_icon_path = config_get( 'icon_path' );
$t_edit_icon_string = '<img class="start-inline-edit" src="' . $t_icon_path . 'update.png"/>';
?>
var FilterPageEdit = {
    installCustomFieldEdit : function(fieldName) {
        var bugTable = jQuery("#buglist");
        var headerRow = bugTable.find("tr.row-category");
        var customFieldColumn = headerRow.find("td:contains(" + fieldName + ")");
        var customFieldColumnIndex = headerRow.children().index(customFieldColumn);
        customFieldColumn.append('<?php echo $t_edit_icon_string ?>');
        
        var editableColumns = [];
        
        bugTable.find('input[type=checkbox]').each(function() {
            var bugRow = jQuery(this).parent().parent();
            var editableColumn = bugRow.find('td:eq('+customFieldColumnIndex+')')
            editableColumn.addClass('inline-editable').click(function() {
                FilterPageEdit._makeEditable(jQuery(this));
            });
            
            editableColumns[editableColumns.length] = editableColumn;
        });
        
        customFieldColumn.find('.start-inline-edit').click(function() {
            for ( var i = 0 ; i < editableColumns.length; i++ ) {
                FilterPageEdit._makeEditable(editableColumns[i]);
            }
            jQuery(this).unbind('click');
        });
    },
    
    _makeEditable: function(jQueryCell) {
        var oldText = jQueryCell.text();
        jQueryCell.removeClass('inline-editable');
        jQueryCell.text('').append('<input type="text" value="' + oldText + '">');
        jQueryCell.unbind('click');
    }
};
jQuery(document).ready(function() {
<?php

$f_custom_fields = explode( ',' , gpc_get_string( 'fields' ) );
foreach ( $f_custom_fields as $t_custom_field_id ) {
    $t_custom_field = custom_field_get_definition( $t_custom_field_id );
    echo "\tFilterPageEdit.installCustomFieldEdit('" . $t_custom_field['name'] ."');\n";
}
?>
});
    
