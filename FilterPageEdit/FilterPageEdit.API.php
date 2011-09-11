<?php 

class FilterPageEditDao {

    /**
     * Returns all auto-editable fields
     * 
     * <p>The return value has the 'source' fields in the keys and the 'target' fields in the values</p>
     * 
     * @return array all auto-editable fields
     */
    static function getAutoEditFields( ) {

        
        $t_query = "SELECT custom_field_id, target_field_id FROM " . plugin_table('auto_editable_fields');
        $t_result = db_query_bound( $t_query );
        if ( 0 == db_num_rows ( $t_result ) ) {
            return array();
        }

        $t_return = array();

        for ( $i = 0 ; $i < db_num_rows( $t_result); $i++ ) {
            $t_array = db_fetch_array( $t_result );
            $t_custom_field_id = $t_array['custom_field_id'];
            $t_target_field_id = $t_array['target_field_id'];

            $t_return[ $t_custom_field_id ] = $t_target_field_id;
        }

        return $t_return;
    }
}

class FilterPageEditSelector {
    
    /**
     * @return array An array of custom field ids which are currently filtered on the page.
     */
    static function getFilteredCustomFields() {

        $t_return = array();
        
        $t_filter = current_user_get_bug_filter();
        $t_filter = filter_ensure_valid_filter( $t_filter );
        
        foreach ( $t_filter['custom_fields'] as $t_custom_field_id => $t_filter_custom_values ) {
            
            if ( filter_field_is_any( $t_filter_custom_values ) )
                continue;
            
            $t_custom_field = custom_field_get_definition( $t_custom_field_id );
            
            // filter_field_is_any does not handle date custom field types
            if ( $t_custom_field['type'] == CUSTOM_FIELD_TYPE_DATE  && $t_filter_custom_values[0] == CUSTOM_FIELD_DATE_ANY)
                continue;
            
            $t_return[] = $t_custom_field_id;
        }
        
        return $t_return;
    }
}
?>