<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_views_components_GoogleAnalytics extends org_glizy_components_Component
{
	function render_html()
	{
		$siteProp = $this->_application->getSiteProperty();
		if ( isset( $siteProp[ 'analytics' ] ) && !empty( $siteProp[ 'analytics' ] ) )
		{
			$code = $siteProp[ 'analytics' ];
			$host = $_SERVER['SERVER_NAME'];
			$output =  <<< EOD
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '{$code}', 'auto');
  ga('send', 'pageview');

</script>
EOD;
		// add the code in the output buffer
		$this->addOutputCode($output);
		}
	}
}