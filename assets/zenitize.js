jQuery(document).ready(function(){

  Symphony.Language.add({ 'Zen Coding is enabled ({$link})' : false });
  Symphony.Language.add({ 'More details about Zen Coding and features enabled' : false });

  var msg = Symphony.Language.get('Zen Coding is enabled ({$link})', {
    link: '<a href="http://code.google.com/p/zen-coding/" title="' + Symphony.Language.get('More details about Zen Coding and features enabled') + '">?</a>'
  });

  jQuery(".primary label:last").append("<i>" + msg + "</i>");
  jQuery("textarea").addClass("zc-use_tab-true zc-syntax-xsl zc-profile-xml");
});
