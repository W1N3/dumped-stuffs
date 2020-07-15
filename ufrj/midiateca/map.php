<?php
include ("./includes/htmlActions.php");
//include "./_webtool/includes/conecta.php";
include ("./_webtool/includes/funcoes.php");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<!doctype html>
<html lang="pt">
	<head>
<?
scriptsAndCSS();
?>
	
		<link rel="stylesheet" href="leaflet/leaflet.css"/>
		<script src="leaflet/leaflet.js"></script>
		<script src="leaflet/heatmap.min.js"></script>
		<script src="leaflet/leaflet-heatmap.js"></script>
		<style>
			#mapa {
				height :80vh;
			}
			#pic {
				min-width : 70vw;
			}
			@media only screen and (min-width: 768px) {
				#pic {
					min-width : 24vw;	
				}
			}
		</style>
	</head>
	<body>
<?
	headerPage();
?>
<div class="bg-dark text-white jumbotron-fluid">
	<div class="container">
		<div id="mapa">
		</div>
	</div>
</div>
<?
	footerPage();

	// retrieve data from database
	$sqlBuscaProjetos = "SELECT id_projeto, nome_projeto as nome_projeto, latitude_projeto, longitude_projeto FROM projetos WHERE latitude_projeto IS NOT NULL";
	$resultBusca = mysqli_query($conn, $sqlBuscaProjetos);
	$projetos = array();
	if($resultBusca && mysqli_num_rows($resultBusca)){
		while ($dataBusca = mysqli_fetch_array($resultBusca, MYSQLI_ASSOC)) {
			$id_projeto = codify($dataBusca["id_projeto"]);
			$nome_projeto = codify($dataBusca["nome_projeto"]);
			$latitude_projeto = codify($dataBusca["latitude_projeto"]);
			$longitude_projeto = codify($dataBusca["longitude_projeto"]);
			$imgs = array();
			//imagens do projeto
			$sqlBuscaImagem = "SELECT id_imagem,nome_imagem FROM imagens WHERE id_imagem IN(SELECT projetos_imagens.id_imagem FROM projetos_imagens WHERE projetos_imagens.id_projeto=$id_projeto) LIMIT 1";
			$resultImgs = mysqli_query($conn, $sqlBuscaImagem);
			$img_projeto = mysqli_result($resultImgs, 0, 1);
			$projetos[] = array("id"=>$id_projeto,"nome"=> htmlspecialchars($nome_projeto,ENT_QUOTES),"lat"=>$latitude_projeto, "lng"=>$longitude_projeto,"imagem"=>$img_projeto);
		}
	}

	// check if come from linked project
	$id = empty($_REQUEST["id"])?"-1":$_REQUEST["id"];
?>
		<script type="text/javascript" charset="utf-8">
			/*
			var YI = L.icon({
				iconUrl: 'leaftlet/images/yellow-icon.png',
				shadowUrl: 'leaftlet/images/marker-shadow.png',
				iconSize:    [12, 20],
				iconAnchor:  [6, 20],
				popupAnchor: [1, -17],
				tooltipAnchor: [8, -14],
				shadowSize:  [20, 20]
				});

			var RI = L.icon({
				iconUrl: 'leaftlet/images/red-icon.png',
				shadowUrl: 'leaftlet/images/marker-shadow.png',
				iconSize:    [25, 41],
				iconAnchor:  [12, 41],
				popupAnchor: [1, -34],
				tooltipAnchor: [16, -28],
				shadowSize:  [41, 41]
				});
*/
			var dodos = []
			// yield that data in json form

			dataset = {max:2, data: <? echo json_encode($projetos);?>};
			
			var mapa = L.map('mapa').setView([-22.7542287, -43.241957], 10);

			L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
	attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
	maxZoom: 18,
	id: 'mapbox.streets',
	// id : 'mapbox.light'
	// id : 'mapbox.dark'
	// id : 'mapbox.navigation-preview-night'
	// id : 'mapbox.navigation-preview-day'
	accessToken: 'pk.eyJ1IjoibWlkaWF0ZWNhIiwiYSI6ImNqbDZ4ZDQxazFhbjMzd3E4M3phN2p1MHEifQ.biX_SnyFQKB_oyiwm8h9Cg'
	}).addTo(mapa);

			markers = [];
			for(i=0 ; i<dataset.data.length ; i++) {
				markers.push(L.marker([dataset.data[i].lat, dataset.data[i].lng]).addTo(mapa));
				popup = markers[i].bindPopup('<picture><div id="pic" class="d-flex justify-content-center"><a class="h5 text-center" href="projetos.php?id='+dataset.data[i].id+'">'+dataset.data[i].nome+'</a></div><img src='+dataset.data[i].imagem+' class="img-fluid img-thumbnail"></picture>');
<?
				if($id != -1) {
?>
				if(dataset.data[i].id == <?=$id?>) {
					popup.openPopup();
					mapa.setView([(parseFloat(dataset.data[i].lat)+0.02).toString(), dataset.data[i].lng], 13);
				}
<?
				}
?>
			}

		</script>
	</body>
</html>