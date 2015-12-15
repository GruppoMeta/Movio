GlizyApp.pages[ 'movio.modules.ontologybuilder.views.ContentsEditor' ] = function( state, routing ) {
    if ( state == 'edit' ) {
        Glizy.module('ontologybuilder.SearchContent').run();
    }
}