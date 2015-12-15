GlizyApp.pages[ 'org.glizycms.contents.views.ContentsEdit' ] = function( state, routing ) {
    $(function(){
        if ('index'==state) {
            var tree = new GlizycmsSiteTree("#js-glizycmsSiteTree", "#js-glizycmsSiteTreeAdd");
            // Glizy.module('cms.SiteTree').run();
            Glizy.module('cms.PageEditIframe').run();
        } else  if ('edit'==state) {
            Glizy.module('glizycms.BlockEdit').run();
        }
    });
}
