var search = null;
var tweetLayer = null;
var marker;
var results;


/*
 * Paramétrage initial de la page 
 */

$(document).ready(function() {
    
    $('#icon_kw').tooltip({title:"Mots-clé pour la recherche twitter"});
    $('#icon_lon').tooltip({title:"Longitude (le x)"});
    $('#icon_lat').tooltip({title:"Latitude (le y)"});
    $('#icon_rad').tooltip({title:"Rayon en km de la recherche"});
    $('#icon_res').tooltip({title:"Type de résultat"});
    $('#icon_ctr').tooltip({title:"Compteur"});
    
    $('input[name=radius]').blur(function(e){
        updateRadius({
            lat       : $('input[name=lat]').val(),
            lon       : $('input[name=lon]').val(),
            radius    : $('input[name=radius]').val()
        });
    });
    
    // ********************************************************
    // Mofifier le comportement par défaut du formulaire
    
    $('form[name=search]').submit(function(event) {

        var reqDatas = {
            'q'                 : $('input[name=q]').val(),
            'lon'               : $('input[name=lon]').val(),
            'lat'               : $('input[name=lat]').val(),
            'radius'            : $('input[name=radius]').val(),
            'result_type'       : $('input[name=result_type]').val(),
            'simul'             : false,
            'simul_file_name'   : 'response.ser',
            'record'            : true
        };
        
        $("#search-result-list").empty();
        $("#result_twt_req").hide();
        $("#btn-loading").show();
        
        tweetLayer.clearLayers();
        
        $.ajax({
            type        : 'GET',
            url         : 'proxy/proxy.php',  
            data        : reqDatas,         
            dataType    : 'json',           
            encode      : true
        }).done(function(data) {
            
            //console.log(data);
            
            if(data == null) {
                setRequestBannerStatus('Aucun retour', 'error');
                $("#btn-loading").hide();
                return 0;
            }

            if(data.statuses.length == 0) {
                setRequestBannerStatus('Aucun résultat', 'success');
                $("#btn-loading").hide();
                return 0;
            }
            
            $("#btn-loading").hide();
            

            $('input[name=since_id]').val(data.search_metadata.refresh_url);
            
            var message = '<b>réponses=</b>' + data.search_metadata.count; 
            message += ' | <b>time=</b>' + data.search_metadata.completed_in;
            message += ' | <b>refresh=</b>' + data.search_metadata.refresh_url;
            
            setRequestBannerStatus(message, 'success');
                                
            var myArray = []; 
            results = [];
                             
            $.each(data.statuses, function(index, value) {
                    var geo_glyph, place_glyph = null;

                    var dt = new Date(value.created_at);
                    var dt_str = dt.getDate() + '/' + (dt.getMonth()+1) + " " + dt.getHours() + ":" + dt.getSeconds();                    
                    var url = 'https://twitter.com/' + value.user.name + '/status/' + value.id_str;
                    var screen_name = '<a href="https://twitter.com/' + value.user.screen_name + '/">' + "@"+ value.user.screen_name + "</a>";
                    var lat, lon;
                    if(value.geo!==null) {
                        // console.log(value.geo);
                        geo_glyph = 'glyphicon-map-marker'; 
                        if(value.geo.type =='Point') {
                            lat = value.geo.coordinates[0];
                            lon = value.geo.coordinates[1];
                            var redMarker = L.AwesomeMarkers.icon({
                                icon: 'user',
                                markerColor: 'red'
                            });
                            
                            marker = L.marker([lat, lon], {icon: redMarker}).addTo(tweetLayer);
                            
                            marker.bindPopup('<img src="' + value.user.profile_image_url + '"/>' + screen_name + " - " + value.text);
                        }
                    }
                    
                    if(value.place!==null) {
                        place_glyph = "glyphicon-home";
                        
                        
                    }
                    
                    results.push(value);
                    
                    myArray.push({
                        lat:lat,
                        lon:lon,
                        geo_glyph: geo_glyph,
                        place_glyph: place_glyph,
                        place : value.place,
                        created_at: dt_str,
                        screen_name: value.user.screen_name,
                        name : value.user.name,
                        text: value.text,
                        imgSrc : value.user.profile_image_url,
                        id : value.id
                    });
                }
            );
            
            applyTemplate(myArray,"row-tweet-template");  
            applyToolTips();
        });

        event.preventDefault();
    });
    populateForm('search', baseParamsLocation, baseParamsTwiterReq);
});

// Remplir le formulaire avec les paramètres fournis
function populateForm(formName, location, twitterRequest) {
    if (location !== undefined) {
        $.each(location, function(index, value) {
            $('form[name=' + formName + '] input[name=' + index + ']').val(value);
        });
    }

    if(twitterRequest !== undefined) {
        $.each(twitterRequest, function(index, value) {
            $('form[name=' + formName + '] input[name=' + index + ']').val(value);
        }); 
    }
}

function readFormParams(formName){
    /*
     * A voir au taf...
     */
}

// Mettre à jour la taille du radius
function updateRadius(location) {
    search.clearLayers();        

    marker = L.marker([location.lat, location.lon]).addTo(search);
    circle = L.circle([location.lat, location.lon], location.radius*1000,  { color: 'green'}).addTo(search);
}

/*
 * Paramétrage initial de la carte
 */

function initMap(divTarget, location) {
    map = new L.Map(divTarget);
    var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var osmAttrib = 'Map data OpenStreetMap contributors';
    var osm = new L.TileLayer(osmUrl, {
        minZoom : 1,
        maxZoom : 19,
        attribution : osmAttrib
    });
    
    map.setView([location.lat, location.lon], location.zoom);
    map.addLayer(osm);
    
    search = new L.LayerGroup(); 
    tweetLayer = new L.LayerGroup(); 
    
    marker = L.marker([location.lat, location.lon]).addTo(search);
    circle = L.circle([location.lat, location.lon], location.radius*1000,  { color: 'red'}).addTo(search);;

    map.addLayer(search);
    map.addLayer(tweetLayer);

    map.on('contextmenu', function(e) {
        // this.setView(e.latlng, this.getZoom());
        
        search.clearLayers();        
        
        marker = L.marker(e.latlng).addTo(search);
        circle = L.circle(e.latlng, location.radius*1000,  { color: 'red'}).addTo(search);
        
        populateForm('search', {'lat' : e.latlng.lat, 'lon' : e.latlng.lng});
    });
    
    // Plugin pour le géocodage
    new L.Control.GeoSearch({
        provider: new L.GeoSearch.Provider.OpenStreetMap()
    }).addTo(map);
}

function setRequestBannerStatus(message, status) {
    $("#result_twt_req").empty().append(message);
    $("#result_twt_req").show();
    $("#result_twt_req").addClass('alert-' + status);   
}

function applyTemplate(datas, templateSrc) {
    
    var source   = $("#"+templateSrc).html();
    var template = Handlebars.compile(source);
    var html = template(datas);
    
    $("#search-result-list-handle").empty().append(html);
}

/*
 * Récupérer l'élément souhaité dans la liste de résultat
 */
function getResultItem(id) {
    if(results !== undefined) {
        var result;
        $.each(results, function(index, value) {
            if(id == value.id) {
                result = value;
            }
        });
        return result;
   }else return false;  
}

function displayItemInfos(id) {
    $('#modalContent').empty().append(prettyPrint(getResultItem(id)));
    $('#myModal').modal({keyboard:true});
}

function applyToolTips(){
    if(results !== undefined) {
        $.each(results, function(index, value) {
            if(value.place != null) {
                $('#place_' + value.id).tooltip();    
            }
            if(value.geo != null) {
                if(value.geo.type =='Point') {
                    $('#geo_' + value.id).tooltip();
                }    
            }            
        });
    }
}

function gotoMarker(params) {
    map.setView([params.lat, params.lon], 16);
}

/**
 * Réduction ou agrandissement de la zone de carte
 * On joue sur le nombre de colonnes indiquées dans la 
 * classe Bootstrap
 * 
 */

function expandOrReduceMapPanel() {
        
    if($('#container-result').attr('class') == 'col-md-8') {
       $('#container-result').removeClass().addClass('col-md-4');
       $('#container-map').removeClass().addClass('col-md-8');
    }else {
       $('#container-map').removeClass().addClass('col-md-4');
       $('#container-result').removeClass().addClass('col-md-8');
    }
    
    // Sinon la zone de carte ne se refraichit pas correctement au resize
    L.Util.requestAnimFrame(map.invalidateSize, map, false, map._container);
}
