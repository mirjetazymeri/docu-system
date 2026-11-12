require.config({
  baseUrl: "../vendors/bootstrap-form-builder/assets/js/lib/",
  paths: {
	'text'  : "https://rawgithub.com/requirejs/text/latest/text",
	'app'   : "..",
	 collections : "../collections",
	 data        : "../data",
	 models      : "../models",
	 helper      : "../helper",
	 templates   : "../templates",
	 views       : "../views"
  },
  shim: {
  }
});


require(['app/app'], function(app){
  app.initialize();
});
