<?php 
# Copyright (c) 2011 Robert Munteanu (robert@lmn.ro)

# Filter page edit for MantisBT is free software:
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation,
# either version 2 of the License, or (at your option) any later version.
#
# Filter page edit plugin for MantisBT is distributed in the hope
# that it will be useful, but WITHOUT ANY WARRANTY; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Filter page edit plugin for MantisBT.
# If not, see <http://www.gnu.org/licenses/>.
header ("Content-Type: text/javascript");

require_once 'core.php';

$t_icon_path = config_get( 'icon_path' );
$t_edit_icon_string = '<img class="start-inline-edit" src="' . $t_icon_path . 'update.png"/>';
$t_submit_icon_string = '<img class="submit-inline-edit" src="' . $t_icon_path . 'ok.gif"/>';
$t_security_token = form_security_token('filter_page_edit');

$t_filter = current_user_get_bug_filter();
$t_filter = filter_ensure_valid_filter( $t_filter );

// find out which fields should already be rendered as editable
$t_auto_editable_fields = FilterPageEditDao::getAutoEditFields();
$t_filtered_custom_fields = FilterPageEditSelector::getFilteredCustomFields();
$t_reveal_fields = array();

foreach ( $t_auto_editable_fields as $t_source_field => $t_value_field ) {
    if ( in_array( $t_source_field, $t_filtered_custom_fields) ) {
        $t_reveal_fields[] = $t_value_field;
    }
}
?>
var FilterPageEdit = {
    installCustomFieldEdit : function(fieldId, fieldName, displayEditable) {
        var bugTable = jQuery("#buglist");
        
        var checkboxes = bugTable.find('input[type=checkbox][name="bug_arr[]"]');
        if ( checkboxes.length == 0 ) {
            if ( console && console.error )
                console.error("Unable to apply filtering as the 'selection' column is not present.");
            return;
        }
        
        var headerRow = bugTable.find("tr.row-category");
        var customFieldColumn = headerRow.find("td:contains(" + fieldName + ")");
        var customFieldColumnIndex = headerRow.children().index(customFieldColumn);
        customFieldColumn.append('<?php echo $t_edit_icon_string ?>');
        
        var editableColumns = [];
        
        // make editable as when clicking on the cell 
        checkboxes.each(function() {
            var bugId = jQuery(this).val();
            var bugRow = jQuery(this).parent().parent();
            var editableColumn = bugRow.find('td:eq('+customFieldColumnIndex+')')
            editableColumn.data('bugId', bugId).data('fieldId', fieldId);
            editableColumn.addClass('inline-editable').click(function() {
                FilterPageEdit._makeEditable(jQuery(this), customFieldColumn);
            });
            
            editableColumns[editableColumns.length] = editableColumn;
        });
        
        // make editable when clicking on the header
        customFieldColumn.find('.start-inline-edit').click(function() {
            for ( var i = 0 ; i < editableColumns.length; i++ ) {
                FilterPageEdit._makeEditable(editableColumns[i], customFieldColumn);
            }
            jQuery(this).unbind('click');
        });
        
        if ( displayEditable ) {
            customFieldColumn.find('.start-inline-edit').click();
        }
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
        
        // install the submit icon and behaviour only once
        if ( headerCell.find('.submit-inline-edit').length == 0 ) {
            headerCell.append('<?php echo $t_submit_icon_string; ?>');
            headerCell.find('.submit-inline-edit').click(function() {
                var submitValue = {'filter_page_edit_token': '<?php echo $t_security_token ?>'};
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
    $t_display_editable = in_array( $t_custom_field_id, $t_reveal_fields ) ? 'true': 'false';
    echo "\tFilterPageEdit.installCustomFieldEdit('" . $t_custom_field['id'] ."', '" . string_display( lang_get_defaulted( $t_custom_field['name'] ) )  ."', ". $t_display_editable .");\n";
}
?>
});
    
