<?php
class org_glizycms_mediaArchive_views_components_CustomStorageBrowser extends org_glizy_components_Component
{
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('filter',  false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('copyCheckbox',  false, true, COMPONENT_TYPE_BOOLEAN);

        // call the superclass for validate the attributes
        parent::init();
    }

    /**
     * Process
     *
     * @return    boolean    false if the process is aborted
     * @access    public
     */
    function process()
    {
    }

    function render()
    {
        $onlyFolder = __Request::get('onlyFolder');
        $onlyFirstLevel = __Request::get('onlyFirstLevel');
        $customPath = __Request::get('customPath');
        $enableDownload = __Request::get('enableDownload');

// TODO localizzare l'interfaccia
        $this->_application->_rootComponent->addOutputCode( org_glizy_helpers_JS::JScode( 'if ( typeof(Glizy) == "undefined" ) Glizy = {}; Glizy.baseUrl ="'.GLZ_HOST.'"; Glizy.ajaxUrl = "ajax.php?pageId='.$this->_application->getPageId().'&ajaxTarget='.$this->getId().'&onlyFolder='.$onlyFolder.'&customPath='.$customPath.'&enableDownload='.$enableDownload.'&command=";' ), 'head' );
        $showCopy = $this->getAttribute('copyCheckbox') && !$onlyFolder ? 'true' : 'false';
        $showFolderSelection = $onlyFolder ? 'true' : 'false';
        $showOnlyFirstLevel = $onlyFirstLevel ? 'true' : 'false';

        $output = <<<EOD
<div id="storageBrowser"></div>
<script type="text/javascript">
jQuery(document).ready(function() {
    var currentFolder = '';

    function redraw( data )
    {
        var html = '';
        if ($showCopy) {
            html += '<input name="copyToCMS" id="copyToCMS" type="checkbox" class="pull-left"/>';
            html += '<label for="copyToCMS" class="pull-left">Copia il file nell\'archivio del CMS</label></br></br>';
        }

        html += '<h4>Posizione: root/'+ currentFolder+'</h4>';
        html += '<table id="dataGrid" class="storageBrowser table table-bordered table-striped">';
        html += '<thead><tr>';
        html += '<th class="icon"></th>';
        html += '<th class="filename">Nome file</th>';
        html += '<th class="size">Dimensione</th>';
        html += '<th class="date">Ultima modifica</th>';
        html += '</tr></thead>';

        var htmlDirs = '';
        var htmlFiles = '';
        jQuery( data ).each( function( index, value ){
            var rowCss = index % 2 ? 'odd' : 'even';
            if ( value.type == "dir" )
            {
                htmlDirs += '<tr class="'+rowCss+'" data-path="'+value.path+'" data-type="folder"><td class="icon folder"></td>';
                htmlDirs += '<td class="filename">'+value.name+'</td>';
                htmlDirs += '<td class="size"></td>';
                htmlDirs += '<td class="date"></td>';
                htmlDirs += '</tr>';
            }
            else
            {
                htmlFiles += '<tr class="'+rowCss+'" data-path="'+value.path+'" data-type="file"><td class="icon '+value.icon+'"></td>';
                htmlFiles += '<td class="filename">'+value.name+'</td>';
                htmlFiles += '<td class="size">'+value.size+'</td>';
                htmlFiles += '<td class="date">'+value.date+'</td>';
                htmlFiles += '</tr>';
            }
        });

        html += '<tbody>'+htmlDirs+htmlFiles+'</tbody></table>';

        if ($showFolderSelection && !$showOnlyFirstLevel ) {
            html += '<input value="Seleziona cartella" name="selectFolder" id="selectFolder" type="button" class="btn"/>';
        }
        
        jQuery('#storageBrowser').html( html );
    }

    function loadFolder() {
        jQuery.ajax({
                 type: "POST",
                 url: Glizy.ajaxUrl + "read",
                 dataType: "json",
                 data: {path: currentFolder},
                 success: function (data) {
                         // console.log( data );
                         redraw( data );
                     }
                });
    }

    jQuery( document ).on( 'click', '#storageBrowser tbody tr', function( ){
        if ( jQuery( this ).data( 'type' ) == 'folder' )
        {
            currentFolder = jQuery( this ).data( 'path' );
            if ($showOnlyFirstLevel) {
                parent.custom_storageBrowserSelect(currentFolder);
            } else {
                loadFolder();
            }
        }
        else
        {
            parent.custom_storageBrowserSelect( jQuery( this ).data( 'path' ) );
        }
    })

    jQuery( document ).on('change', '#copyToCMS', function () {
        parent.setCopyToCMS( jQuery( this ).attr('checked') );
    });
    
    jQuery(document).on( 'click', '#selectFolder', function( ){
        parent.custom_storageBrowserSelect( currentFolder );
    })

    jQuery( document ).on( 'hover', '#storageBrowser tbody tr', function( ){
        jQuery( this ).addClass( 'ruled' );
    }).on( 'mouseout', function( ){
        jQuery( this ).removeClass( 'ruled' );
    })

    loadFolder();
});
</script>
EOD;

        $this->addOutputCode($output);
    }

    function process_ajax()
    {
        $onlyFolder = __Request::get( 'onlyFolder' );
        $command = __Request::get( 'command' );
        $path = ltrim( __Request::get( 'path' ), '/' );
        $customPath = __Request::get('customPath');
        $enableDownload = __Request::get('enableDownload');
        
        $result = array();
        if ( $command == "read" )
        {
            $path = utf8_decode($path);
            if (!$customPath) {
                $mappingService = $this->_application->retrieveProxy('org.glizycms.mediaArchive.services.MediaMappingService');

                if ($path == '') {
                    $mappings = $mappingService->getAllMappings();
                    foreach ($mappings as $mappingName => $mappingTarget) {
                        $result[] = array( 'type' => 'dir', 'name' => $mappingName, 'path' => $mappingName, 'icon' => 'folder' );
                    }
                    return $result;
                }

                $slashPos = strpos($path, '/');

                if ($slashPos == false) {
                    $dir = $mappingService->getMapping($path);
                } else {
                    $name = substr($path, 0, $slashPos);
                    $dir = $mappingService->getMapping($name) . substr($path, $slashPos);
                }

            } else  {
                $dir = $customPath.$path;
            }

            $result[] = array( 'type' => 'dir', 'name' => 'root/', 'path' => '', 'icon' => 'folder' );
            
            $files = glob($dir.'/*');
            
            if (!empty($files)) {
                $filter = $this->getAttribute('filter');

                foreach ($files as $file_name)
                {
                    $file_name = str_replace($dir.'/', '', $file_name);
                    
                    if ( $file_name == "." ) continue;
                    if ( $file_name == ".." && $path == '' ) continue;
                    if ( $file_name[0] == "." && $file_name[1] != ".") continue; // nasconde i file che iniziano col punto
                    
                    // se è settato un filtro sull'estensione dei file, allora non mostra i file che non hanno come estensione quella specificata nel filtro
                    if (!is_dir($dir.'/'.$file_name) && $filter != '' && pathinfo($file_name, PATHINFO_EXTENSION) != $filter ) continue;

                    $fullPath = $dir.'/'.$file_name;
                    $fullPath2 = $path.'/'.$file_name;

                    if ( is_dir( $fullPath ) )
                    {
                        if ( $file_name == ".."  )
                        {
                            $fullPath2 = dirname( dirname( $fullPath2 ) );
                        }
                        if ( $fullPath2 == '/' || $fullPath2 == '.') $fullPath2 = '';
                        $result[] = array( 'type' => 'dir', 'name' => utf8_encode($file_name), 'path' => ltrim( utf8_encode($fullPath2), '/' ), 'icon' => 'folder' );
                    }
                    else if (!$onlyFolder)
                    {
                        $sizeInBytes = filesize( $fullPath );
                        $extension = strtolower( pathinfo( $fullPath, PATHINFO_EXTENSION ) );
                        $iconType= array(
                            'jpg' => 'image',
                            'jpeg' => 'image',
                            'gif' => 'image',
                            'png' => 'image',
                            'pdf' => 'pdf',
                            'flv' => 'video',
                            'mp4' => 'video',
                            'm4v' => 'video',
                            'mp3' => 'audio'
                        );
                        $icon = isset( $iconType[ $extension ] ) ? $iconType[ $extension ] : 'other';
                        
                        if ($enableDownload) {
                            $name = org_glizy_helpers_Link::makeSimpleLink($file_name, $fullPath, '', '', '', array('download' => $file_name));
                        } else {
                            $name = utf8_encode($file_name);
                        }
                        
                        $result[] = array( 'type' => 'file',
                                            'name' => $name,
                                            'path' => utf8_encode($fullPath2),
                                            'size' => $this->formatSize( $sizeInBytes ),
                                            'icon' => $icon,
                                            'date' => date( 'H:i:s d/m/Y', filemtime( $fullPath ) )
                                             );
                    }
                }
            }
        }

        return $result;
    }

    function formatSize($size)
    {
        $sizes = Array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
        $y = $sizes[0];
        for ($i = 1; (($i < count($sizes)) && ($size >= 1024)); $i++)
        {
            $size = $size / 1024;
            $y  = $sizes[$i];
        }
        return round($size, 2)." ".$y;
    }
}