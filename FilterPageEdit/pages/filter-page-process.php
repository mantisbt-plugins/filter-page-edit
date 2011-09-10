<?php 
foreach ( $_POST as $f_submitted_change => $f_submitted_value ) {
    
    if ( strstr($f_submitted_change, 'inline-' !== 0 ) )
        continue;
    
    list($t_prefix, $t_field_id, $t_bug_id) = explode( '-', $f_submitted_change);
    
    // we can not have meaningful error reporting with trigger_error for now
     if ( !custom_field_has_write_access( $t_field_id, $t_bug_id ) )
         continue;
     
     $t_old_value = custom_field_get_value( $t_field_id, $t_bug_id );
     $t_new_value = gpc_get_string( $f_submitted_change );
     
     // prevent dummy history entries/bug_update_date calls
     if ( $t_old_value === $t_new_value )
         continue;
    
     custom_field_set_value( $t_field_id, $t_bug_id, gpc_get_string( $f_submitted_change ) );
     bug_update_date( $t_bug_id );
}
?>