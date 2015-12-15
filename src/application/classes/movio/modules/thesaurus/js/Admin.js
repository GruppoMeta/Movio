GlizyApp.pages[ 'movio.modules.thesaurus.views.Admin' ] = function( state, routing ) {
    $(function(){
        if ('editDictionary'==state) {
            var tree = new DictionaryTree("#js-glizycmsSiteTree", "#js-glizycmsSiteTreeAdd");
            Glizy.module('thesaurus.TermEditIframe').run();
        }
    });
};
2