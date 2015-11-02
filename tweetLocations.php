<?php 
    $conf = json_decode(file_get_contents('./conf/conf.json')); 
?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title>User Timeline</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<script src="./resources/js/jquery/jquery-2.1.1.js"></script>
		<script src="./resources/js/rgms.js"></script>

		<link rel="stylesheet" href="./resources/js/bootstrap-3.2.0-dist/css/bootstrap.min.css">
		<link rel="stylesheet" href="./resources/js/bootstrap-3.2.0-dist/css/bootstrap-theme.min.css">
		<script src="./resources/js/bootstrap-3.2.0-dist/js/bootstrap.min.js"></script>

		<script src="./resources/js/leaflet-0.7.3/leaflet.js"></script>
		<link rel="stylesheet" href="./resources/js/leaflet-0.7.3/leaflet.css">
		<link rel="stylesheet" href="./resources/css/rgms.css">

        <script src="./resources/js/geosearch/js/l.control.geosearch.js"></script>
        <link rel="stylesheet" href="./resources/js/geosearch/css/l.geosearch.css" />
        <script src="./resources/js/geosearch/js/l.geosearch.provider.openstreetmap.js"></script>
        
        <script src="./resources/js/awesome-markers-2.0/leaflet.awesome-markers.js"></script>
        <link rel="stylesheet" href="./resources/js/awesome-markers-2.0/leaflet.awesome-markers.css" />
        
        <!-- Moteur de template JS http://handlebarsjs.com/ -->
        <script src="./resources/js/handlebarsjs/handlebars-v2.0.0.js"></script>
        
        <script src="./resources/js/prettyprint-js/prettyprint.js"></script>

		<script>
            // Variables globales ---------------------------------------------
            var baseParamsLocation = <?php echo json_encode($conf -> geo); ?>;
            var baseParamsTwiterReq = <?php echo json_encode($conf -> twitter -> reqParams); ?>;
		</script>
	</head>
	<body onload="initMap('map', baseParamsLocation);">

		<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#">R.G.M.S.</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active">
							<a href="#">Home</a>
						</li>
						<li>
							<a href="#about">A propos</a>
						</li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>

		<div class="starter-template" style="padding:0px;">
			<div class="panel panel-info"  style="margin-top:20px;">
				<div class="panel-heading">
					Recherche Géolocalisée sur les Médias Sociaux
				</div>

				<div class="panel-body container">
				    
					<form role="form" method="get" name="search">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon glyphicon glyphicon-search"></div>
										<input id="frm_kw" name="q" type="text"  class="form-control" placeholder="Mots-clé">
									</div>
								</div>
							</div>
						</div>	
						<div class="row">
							<div class="col-md-2">
								<div class="form-group">
									<div class="input-group">
										<div id="icon_lon" class="input-group-addon glyphicon glyphicon-globe"></div>
										<input name="lon" type="text" class="form-control" placeholder="Longitude">
									</div>
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									<div class="input-group">
										<div id="icon_lat" class="input-group-addon glyphicon glyphicon-globe"></div>
										<input name="lat" type="text" class="form-control" placeholder="Latitude">
									</div>
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									<div class="input-group">
										<div id="icon_rad" class="input-group-addon glyphicon glyphicon-record"></div>
										<input name="radius" type="text" class="form-control" placeholder="10 (km)">
									</div>
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									<div class="input-group">
										<div id="icon_res" class="input-group-addon glyphicon glyphicon-fire"></div>
										<input name="result_type" type="text" class="form-control" placeholder="mixed, popular, recent">
										<!--
										<select class="form-control" name="resultType">
                                          <option selected="selected">recent</option>
                                          <option>mixed</option>
                                          <option>popular</option>
                                        </select>
                                        -->
									</div>
								</div>

							</div>
							<div class="col-md-2">
								<div class="form-group">
									<div class="input-group">
										<div id="icon_ctr" class="input-group-addon glyphicon glyphicon-list"></div>
										<input name="count" type="text" class="form-control" placeholder="100 (maxi)">
										<!--
										<select class="form-control" name="count">
                                          <option>10</option>
                                          <option>20</option>
                                          <option>50</option>
                                          <option selected="1">100</option>
                                        </select>
                                    -->
									</div>
								</div>

							</div>
							<div class="col-md-2">
							    <input type="hidden" name="since_id" value="0" />
								<button type="submit" class="btn btn-default">
									Valider
								</button>
								&nbsp;
								<button class="btn btn-default" id="btnNext">
                                    suivant
                                </button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div>
                <div class="alert" id="result_twt_req" role="alert">test</div>   

			<div id="container-result" class="col-md-8" style="margin:0px;padding:0px;">
                <div align="center">
                    <button id="btn-loading" class="btn btn-lg btn-info" style="display:none;"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Loading...</button>
                </div>   				
				<script id="row-tweet-template" type="text/x-handlebars-template">
    				<table class="table table-condensed table-striped table-hover" id="search-result-list">
                      {{#each .}}
                        <tr>
                            <td>
                                <img src="{{imgSrc}}" width="30"/>
                            </td>
                            <td>
                                {{#if geo_glyph}}
                                    <span id="geo_{{id}}" class="glyphicon {{geo_glyph}}" onclick="gotoMarker({ lat:{{lat}},lon : {{lon}} }); return 0;" data-toggle="tooltip" data-placement="right" title="{{lat}}/{{lon}}"></span>
                                {{/if}}
                                <br />
                                {{#if place_glyph}}
                                    <span id="place_{{id}}" class="glyphicon {{place_glyph}} data-toggle="tooltip" data-placement="right" title="{{place.full_name}}"></span>
                                {{/if}}
                                
                            </td>
                            <td class="created_at">
                                <span>{{created_at}}</span> 
                            </td>

                            <td class="user-name">
                                <span>{{name}}</span> <br />
                                <span>
                                    <a href="https://twitter.com/{{screen_name}}/">{{screen_name}}</a>
                                </span>
                            </td>
                            <td>
                                <span>{{text}}</span>
                            </td>
                            <td>
                                <!-- Large modal -->
                                <button class="btn btn-xs glyphicon glyphicon-zoom-in" onclick="displayItemInfos({{id}}); return false;">
                                    
                                </button>
                            </td>
                        </tr>
                      {{/each}}
                    </table>
				</script>
                <div id="search-result-list-handle">
                </div>
			</div>
			<div id="container-map" class="col-md-4">
			    <div id="map-divider" class="glyphicon glyphicon-resize-horizontal" onclick="expandOrReduceMapPanel()">
			        
			    </div>
				<div id="map">
				    
				</div>
			</div>
		</div>

        <!-- tpl modal -->
    <div id="myModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent">
          ...
        </div>
      </div>
    </div>
</div>
	</body>
</html>