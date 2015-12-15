GlizyApp.pages[ 'movio.modules.publishApp.views.Admin' ] = function( state, routing ) {
    $(function(){
        if ('index'==state) {
            var tree = new GlizycmsSiteTree("#js-glizycmsSiteTree", "#js-glizycmsSiteTreeAdd");
        }
    });
}
