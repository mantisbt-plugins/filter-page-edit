<?php 
header ("Content-Type: text/javascript");

require_once 'core.php';

$t_icon_path = config_get( 'icon_path' );
$t_edit_icon_string = '<img class="start-inline-edit" src="' . $t_icon_path . 'update.png"/>';
$t_submit_icon_string = '<img class="submit-inline-edit" src="' . $t_icon_path . 'ok.gif"/>';

?>
var FilterPageEdit = {
    installCustomFieldEdit : function(fieldId, fieldName) {
        var bugTable = jQuery("#buglist");
        var headerRow = bugTable.find("tr.row-category");
        var customFieldColumn = headerRow.find("td:contains(" + fieldName + ")");
        var customFieldColumnIndex = headerRow.children().index(customFieldColumn);
        customFieldColumn.append('<?php echo $t_edit_icon_string ?>');
        
        var editableColumns = [];
        
        bugTable.find('input[type=checkbox]').each(function() {
            var bugId = jQuery(this).val();
            var bugRow = jQuery(this).parent().parent();
            var editableColumn = bugRow.find('td:eq('+customFieldColumnIndex+')')
            editableColumn.data('bugId', bugId).data('fieldId', fieldId);
            editableColumn.addClass('inline-editable').click(function() {
                FilterPageEdit._makeEditable(jQuery(this), customFieldColumn);
            });
            
            editableColumns[editableColumns.length] = editableColumn;
        });
        
        customFieldColumn.find('.start-inline-edit').click(function() {
            for ( var i = 0 ; i < editableColumns.length; i++ ) {
                FilterPageEdit._makeEditable(editableColumns[i], customFieldColumn);
            }
            jQuery(this).unbind('click');
        });
    },
    
    _makeEditable: function(jQueryCell, headerCell) {
    
        // already editable
        if ( jQueryCell.find('input').length != 0 ) {
            return;
        }
        var oldText = jQueryCell.text();
        jQueryCell.removeClass('inline-editable');
        var identifier = 'inline-' + jQueryCell.data("fieldId") +'-' + jQueryCell.data('bugId'); 
        jQueryCell.text('').append('<input type="text" value="' + oldText + '" id="' + identifier +'" name="' + identifier +'"/>');
        jQueryCell.unbind('click');
        
        if ( headerCell.find('.submit-inline-edit').length == 0 ) {
            headerCell.append('<?php echo $t_submit_icon_string; ?>');
            headerCell.find('.submit-inline-edit').click(function() {
                var submitValue = {};
                jQuery('#buglist').find('[id|=inline-' + jQueryCell.data("fieldId")+']').each(function() {
                    submitValue[jQuery(this).attr('id')] =jQuery(this).val();
                });
                jQuery.post('<?php echo plugin_page('filter-page-process.php')?>', submitValue, function(result) {
                    window.location.reload(true);
                });
            });
        }
    }
};
jQuery(document).ready(function() {
<?php

$f_custom_fields = explode( ',' , gpc_get_string( 'fields' ) );
foreach ( $f_custom_fields as $t_custom_field_id ) {
    $t_custom_field = custom_field_get_definition( $t_custom_field_id );
    echo "\tFilterPageEdit.installCustomFieldEdit('" . $t_custom_field['id'] ."', '" . $t_custom_field['name'] ."');\n";
}
?>
});
    
