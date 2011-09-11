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

form_security_validate( 'filter_page_edit' );

foreach ( $_POST as $f_submitted_change => $f_submitted_value ) {
    
    if ( strpos($f_submitted_change, 'inline-') !== 0 ) {
        echo "$f_submitted_change\n";
        continue;
    }
    
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

form_security_purge( 'filter_page_edit' );
?>