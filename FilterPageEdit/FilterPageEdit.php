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

class FilterPageEditPlugin extends MantisPlugin {
    
    static $SUPPORTED_FIELD_TYPES = array ( CUSTOM_FIELD_TYPE_NUMERIC , CUSTOM_FIELD_TYPE_STRING, CUSTOM_FIELD_TYPE_FLOAT );
    
    public function register() {
        $this->name = plugin_lang_get("title");
        $this->description = plugin_lang_get("description");

        $this->version = "2.0";
        $this->requires = array(
			"MantisCore" => "2.1",
        );

        $this->author = "Robert Munteanu";
        $this->contact = "robert@lmn.ro";
        $this->url = "http://www.mantisbt.org/wiki/doku.php/mantisbt:filterpageedit";
    }
    
    public function hooks() {
        
        return array(
            'EVENT_LAYOUT_RESOURCES' => 'resources'
        );
    }
    
    public function schema() {
        
        return array(
            array( 'CreateTableSQL',
                array( plugin_table( 'auto_editable_fields' ), "
                    custom_field_id    I NOTNULL,
                    target_field_id    I NOTNULL
                ")
            )
        );
    }
    
    public function init() {
    
        require_once 'FilterPageEdit.API.php';
    }    
    
    public function resources( $p_event ) {
        
        
        if ( basename( $_SERVER['SCRIPT_NAME'] ) != 'view_all_bug_page.php')
            return;
        
        $t_custom_field_ids = custom_field_get_linked_ids( helper_get_current_project() );
        $t_editable_custom_field_ids = array();
        
        foreach ( $t_custom_field_ids as $t_custom_field_id ) {
            
            $t_custom_field_defintion = custom_field_get_definition( $t_custom_field_id );
            
            if ( !in_array( $t_custom_field_defintion['type'], self::$SUPPORTED_FIELD_TYPES ) ) 
                continue;
            
            $t_editable_custom_field_ids[] = $t_custom_field_id;
        }
        
        if ( count ( $t_editable_custom_field_ids ) == 0 )
            return;
        
        $t_field_ids_string = implode(',', $t_editable_custom_field_ids );
        
        return '<script type="text/javascript" src="' . plugin_page( 'filter-page-edit.php&amp;fields=' . $t_field_ids_string ) . '"></script>'.
            '<link rel="stylesheet" type="text/css" href="'. plugin_file('filter-page-edit.css') .'"></link>';
    }
}

?>