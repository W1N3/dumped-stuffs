<?php
include ("../includes/htmlActions.php");
//include "./_webtool/includes/conecta.php";
include ("./includes/funcoes.php");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*

para fazer os graficos de acesso

https://stackoverflow.com/questions/22151771/how-to-push-google-analytics-data-into-mysql-tables#22154364

*/
?>
<!doctype html>
<html lang="pt">
	<head>
<?
scriptsAndCSS();
?>
		<script type="text/javascript" src="../js/Chart.min.js" language="javascript"></script>
		<script type="text/javascript" src="../js/Chart.bundle.min.js" language="javascript"></script>
		<script type="text/javascript" src="../js/utils.js" language="javascript"></script>
		<style type="text/css">
			canvas{
				-moz-user-select: none;
				-webkit-user-select: none;
				-ms-user-select: none;
			}
		</style>
	</head>
	<body>
<?
	headerPage();
?>
<div class="bg-dark text-white jumbotron">
	<div class="container-fluid">
		<div class="row align-items-center">
			<div class="col-3">
				<label class="mr-sm-2"> Selecionar gráfico  a ser visualizado : </label>
			</div>
			<div class="col-6">
				<select class="custom-select mr-sm-2" id="opts">
					<option value="empty" selected> ... </option>
					<option value="g00">Grafico de Conceitos no Total</option>
					<option value="g01">Grafico de Conceitos por Período</option>
					<option value="g02">Grafico de Temas no Total</option>
					<option value="g03">Grafico de Temas por Período</option>
					<option value="g04">Grafico de Prêmios/Menções por Período</option>
				</select>
			</div>
			<div class="col-3">
				<button class="btn btn-success" type="button" onclick="show_chart(document.getElementById('opts').value)"> Mostrar Gráfico </button>
			</div>
		</div>
		<hr></hr>
		<div id="marcador">
			<canvas id="grafo"></canvas>
		</div>
	</div>
</div>
<?
	footerPage();
?>
		<script type="text/javascript" charset="utf-8">
			function encode_utf8(s) {
				return unescape(encodeURIComponent(s));
			}

			function decode_utf8(s) {
				return decodeURIComponent(s);
			}
			function config_skeleton() {
				return {
					type: '',
					data: {
						labels: [],
						datasets: [/*{
							label: "im",
							backgroundColor: "rgb(200,200,0)",
							borderColor: "rgb(0,200,0)",
							data: [325],
							fill: false,
						},{
							label: "ouy",
							backgroundColor: "rgb(0,200,200)",
							borderColor: "rgb(0,200,0)",
							data: [32],
							fill: false,
						}*/]
					},
					options: {
						responsive: true,
						title:{
							display:true,
							text:''
						},
						tooltips: {
							mode: 'index',
							intersect: false,
						},
						hover: {
							mode: 'nearest',
							intersect: true
						},
						scales: {
							xAxes: [{
								stacked:false,
								display: true,
								scaleLabel: {
									display: true,
									labelString: 'x'
								}
							}],
							yAxes: [{
								stacked: false,
								display: true,
								scaleLabel: {
									display: true,
									labelString: 'y'
								},
								ticks: {
									stepSize: 1
								}
							}]
						}
					}
				};
			}
			
			var cor = [];
			cor[0] = [  "rgb(0,50,0)",
						"rgb(50,100,50)",
						"rgb(100,150,100)",
						"rgb(150,200,150)",
						"rgb(200,255,200)"];
			cor[1] = [  "rgb(20,129,204)",
						"rgb(61,116,153)",
						"rgb(0,255,223)",
						"rgb(255,94,64)",
						"rgb(204,23,20)"];
			cor[2] = [  "rgb(255,123,25",
						"rgb(40,0,255",
						"rgb(27,204,20"];
			cor[4] = [  "204,118,105",
						"33,77,153",
						"157,245,255",
						"255,230,220"];

			function clear_chart() {
				var elem = document.getElementsByTagName('canvas')[0];
				elem.parentNode.removeChild(elem);
				var canvas = document.createElement('canvas');
				canvas.id = 'grafo';
				mark = document.getElementById('marcador');
				mark.appendChild(canvas);
				mark.style = "background-color: #343A40";
			}
			function plot_chart(title, config) {
				mark = document.getElementById('marcador');
				mark.style = "background-color: #FFFFFF";
				var actx = document.getElementsByTagName("canvas");
				actx[0].id = title;
				ctx = actx[0].getContext("2d");
				window.myLine = new Chart(ctx, config);
			}
			function show_chart(opt) {
				if(opt == "empty") {
					clear_chart();
				}
				if(opt == "g00") {
					clear_chart();
					info = [{"conceito":"E","quantidade":324,"porcentagem":0.2232},{"conceito":"MB","quantidade":557,"porcentagem":0.386},{"conceito":"B","quantidade":351,"porcentagem":0.2445},{"conceito":"R","quantidade":126,"porcentagem":0.0879},{"conceito":"S","quantidade":31,"porcentagem":0.0213}];

					config = config_skeleton();
					config.type = 'pie';
					config.options = {responsive:true,title:{display:true,text:''}};
					config.options.title.text = document.getElementsByTagName("canvas").id;
					config.data.datasets = [{data:[], backgroundColor:[], label:['']}];
					config.data.labels = ["Excelente", "Muito Bom", "Bom", "Regular", "Suficiente"];
					for(i = 0 ; i < info.length ; i++) {
						config.data.datasets[0].data[i] = info[i].quantidade;
						config.data.datasets[0].backgroundColor[i] = cor[0][i];
					}

					plot_chart("Conceitos no Total", config);
					
				}
				if(opt == "g01") {
					clear_chart();
					info = [{"periodo":"2008/1","conceito":"E","quantidade":20},{"periodo":"2008/1","conceito":"MB","quantidade":30},{"periodo":"2008/1","conceito":"R","quantidade":10},{"periodo":"2008/1","conceito":"B","quantidade":27},{"periodo":"2008/2","conceito":"S","quantidade":1},{"periodo":"2008/2","conceito":"B","quantidade":24},{"periodo":"2008/2","conceito":"R","quantidade":7},{"periodo":"2008/2","conceito":"E","quantidade":24},{"periodo":"2008/2","conceito":"MB","quantidade":41},{"periodo":"2009/1","conceito":"B","quantidade":20},{"periodo":"2009/1","conceito":"S","quantidade":1},{"periodo":"2009/1","conceito":"E","quantidade":22},{"periodo":"2009/1","conceito":"MB","quantidade":40},{"periodo":"2009/1","conceito":"R","quantidade":4},{"periodo":"2009/2","conceito":"B","quantidade":22},{"periodo":"2009/2","conceito":"R","quantidade":7},{"periodo":"2009/2","conceito":"MB","quantidade":30},{"periodo":"2009/2","conceito":"E","quantidade":21},{"periodo":"2010/1","conceito":"B","quantidade":11},{"periodo":"2010/1","conceito":"E","quantidade":8},{"periodo":"2010/1","conceito":"R","quantidade":5},{"periodo":"2010/1","conceito":"S","quantidade":3},{"periodo":"2010/1","conceito":"MB","quantidade":16},{"periodo":"2010/2","conceito":"S","quantidade":5},{"periodo":"2010/2","conceito":"E","quantidade":16},{"periodo":"2010/2","conceito":"B","quantidade":13},{"periodo":"2010/2","conceito":"R","quantidade":6},{"periodo":"2010/2","conceito":"MB","quantidade":21},{"periodo":"2011/1","conceito":"B","quantidade":9},{"periodo":"2011/1","conceito":"E","quantidade":14},{"periodo":"2011/1","conceito":"MB","quantidade":13},{"periodo":"2011/1","conceito":"S","quantidade":2},{"periodo":"2011/2","conceito":"R","quantidade":6},{"periodo":"2011/2","conceito":"B","quantidade":17},{"periodo":"2011/2","conceito":"E","quantidade":15},{"periodo":"2011/2","conceito":"MB","quantidade":27},{"periodo":"2012/1","conceito":"R","quantidade":7},{"periodo":"2012/1","conceito":"B","quantidade":14},{"periodo":"2012/1","conceito":"E","quantidade":11},{"periodo":"2012/1","conceito":"MB","quantidade":38},{"periodo":"2012/1","conceito":"S","quantidade":2},{"periodo":"2012/2","conceito":"E","quantidade":14},{"periodo":"2012/2","conceito":"S","quantidade":6},{"periodo":"2012/2","conceito":"R","quantidade":3},{"periodo":"2012/2","conceito":"B","quantidade":23},{"periodo":"2012/2","conceito":"MB","quantidade":29},{"periodo":"2013/1","conceito":"S","quantidade":3},{"periodo":"2013/1","conceito":"B","quantidade":21},{"periodo":"2013/1","conceito":"E","quantidade":8},{"periodo":"2013/1","conceito":"R","quantidade":4},{"periodo":"2013/1","conceito":"MB","quantidade":24},{"periodo":"2013/2","conceito":"MB","quantidade":28},{"periodo":"2013/2","conceito":"B","quantidade":30},{"periodo":"2013/2","conceito":"E","quantidade":17},{"periodo":"2013/2","conceito":"S","quantidade":3},{"periodo":"2013/2","conceito":"R","quantidade":6},{"periodo":"2014/1","conceito":"MB","quantidade":13},{"periodo":"2014/1","conceito":"S","quantidade":5},{"periodo":"2014/1","conceito":"R","quantidade":7},{"periodo":"2014/1","conceito":"B","quantidade":28},{"periodo":"2014/1","conceito":"E","quantidade":13},{"periodo":"2014/2","conceito":"MB","quantidade":33},{"periodo":"2014/2","conceito":"R","quantidade":9},{"periodo":"2014/2","conceito":"B","quantidade":24},{"periodo":"2014/2","conceito":"E","quantidade":21},{"periodo":"2015/1","conceito":"E","quantidade":17},{"periodo":"2015/1","conceito":"B","quantidade":11},{"periodo":"2015/1","conceito":"R","quantidade":9},{"periodo":"2015/1","conceito":"MB","quantidade":45},{"periodo":"2015/2","conceito":"B","quantidade":15},{"periodo":"2015/2","conceito":"MB","quantidade":32},{"periodo":"2015/2","conceito":"E","quantidade":18},{"periodo":"2015/2","conceito":"R","quantidade":11},{"periodo":"2016/1","conceito":"R","quantidade":10},{"periodo":"2016/1","conceito":"MB","quantidade":34},{"periodo":"2016/1","conceito":"E","quantidade":18},{"periodo":"2016/1","conceito":"B","quantidade":17},{"periodo":"2016/2","conceito":"R","quantidade":7},{"periodo":"2016/2","conceito":"E","quantidade":24},{"periodo":"2016/2","conceito":"B","quantidade":13},{"periodo":"2016/2","conceito":"MB","quantidade":25},{"periodo":"2017/1","conceito":"MB","quantidade":38},{"periodo":"2017/1","conceito":"B","quantidade":12},{"periodo":"2017/1","conceito":"E","quantidade":23},{"periodo":"2017/1","conceito":"R","quantidade":8}];
					
					config = config_skeleton();
					config.type = 'line';
					config.options.title.text = document.getElementsByTagName("canvas").id;
					config.options.scales.xAxes[0].scaleLabel.labelString ='';
					config.options.scales.yAxes[0].scaleLabel.labelString ='';
					
					lbs = [];
					for(i = 0 ; i < info.length ; i++) {
						lbs[i] = info[i].periodo;
					}
					lbs = lbs.filter(function(elem,index,self) {return index == self.indexOf(elem);});
					config.data.labels = lbs;

					rds = [];
					for(i = 0 ; i < info.length ; i++) {
						rds[i] = info[i].conceito;
					}
					rds = rds.filter(function(elem,index,self) {return index == self.indexOf(elem);});
					aux = rds[6];
					rds[6] = rds[5];
					rds[5] = aux;
					ds = [];
					for(i = 0 ; i < rds.length ; i++) {
						if(rds[i] == "E") {
							ds[i] = {key: rds[i], name:"Excelente"};
						}
						else if(rds[i] == "MB") {
							ds[i] = {key: rds[i], name:"Muito Bom"};
						}
						else if(rds[i] == "B") {
							ds[i] = {key: rds[i], name:"Bom"};
						}
						else if(rds[i] == "R") {
							ds[i] = {key: rds[i], name:"Regular"};
						}
						else if(rds[i] == "S") {
							ds[i] = {key: rds[i], name:"Suficiente"};
						}
					}

					for(i = 0 ; i < ds.length ; i++) {
						config.data.datasets[i] = {
								label: ds[i].name,
								backgroundColor: cor[1][i],
								borderColor: cor[1][i],
								data: [],
								fill: false,
							};
					}

					for(i = 0 ; i < info.length; i++) {
						config.data.datasets[rds.indexOf(info[i].conceito)].data[lbs.indexOf(info[i].periodo)] = info[i].quantidade;
					}

					plot_chart("Conceitos por Período", config);
				}
				if(opt == "g02") {
					clear_chart();
					info = '[{"Temas":"Anexos","Total":2},{"Temas":"Arquitetura","Total":1},{"Temas":"Comercial","Total":16},{"Temas":"Edificação","Total":94},{"Temas":"Educação","Total":38},{"Temas":"Entretenimento/Cultura","Total":67},{"Temas":"Escala Local","Total":21},{"Temas":"Esportes","Total":9},{"Temas":"Grande Escala","Total":4},{"Temas":"Habitação de interesse social","Total":24},{"Temas":"Habitação multifamiliar","Total":11},{"Temas":"Hotelaria","Total":29},{"Temas":"Infraestrutura","Total":1},{"Temas":"Institucional/Coorporativo","Total":14},{"Temas":"Interesse Social","Total":1},{"Temas":"Interiores","Total":2},{"Temas":"Investigação Experimental","Total":14},{"Temas":"Paisagismo","Total":12},{"Temas":"Parques/Praças","Total":3},{"Temas":"Patrimônio","Total":16},{"Temas":"Requalificação","Total":24},{"Temas":"Restauro","Total":3},{"Temas":"Revitalização","Total":5},{"Temas":"Revitalização da Paisagem","Total":6},{"Temas":"Saúde","Total":20},{"Temas":"Sistemas Construtivos","Total":1},{"Temas":"Sustentabilidade","Total":3},{"Temas":"Transporte","Total":26},{"Temas":"Urbanismo","Total":45}]';
					info = JSON.parse(info);

					config = config_skeleton();
					config.type = 'bar';
					config.options = {responsive : true, legend:{position:'top'},title:{display:true,text:document.getElementsByTagName("canvas").id}};
					config.data.labels[0] = "Total";

					info = info.sort(function(a,b){return b.Total-a.Total;});
					for(i = 0 ; i < info.length ; i++) {
						bc = ''+randomScalingFactor()+','+randomScalingFactor()+','+randomScalingFactor()+'';
						config.data.datasets[i] = {
							label:info[i].Temas,
							backgroundColor:'rgb('+bc+',0.7)',
							borderColor:'rgb('+bc+')',
							fill:true,
							data:[info[i].Total]
						};
					}

					plot_chart("Prêmios/Menções por Período", config);
				}
				if(opt == "g03") {
					clear_chart();
					info = '[{"tema":"Educação","periodo":"2013/1","quantidade":6},{"tema":"Educação","periodo":"2013/2","quantidade":8},{"tema":"Educação","periodo":"2014/1","quantidade":5},{"tema":"Educação","periodo":"2014/2","quantidade":5},{"tema":"Educação","periodo":"2015/1","quantidade":10},{"tema":"Educação","periodo":"2015/2","quantidade":4},{"tema":"Entretenimento/Cultura","periodo":"2012/2","quantidade":1},{"tema":"Entretenimento/Cultura","periodo":"2013/1","quantidade":11},{"tema":"Entretenimento/Cultura","periodo":"2013/2","quantidade":15},{"tema":"Entretenimento/Cultura","periodo":"2014/1","quantidade":19},{"tema":"Entretenimento/Cultura","periodo":"2014/2","quantidade":9},{"tema":"Entretenimento/Cultura","periodo":"2015/1","quantidade":8},{"tema":"Entretenimento/Cultura","periodo":"2015/2","quantidade":2},{"tema":"Entretenimento/Cultura","periodo":"2016/2","quantidade":2},{"tema":"Habitação de interesse social","periodo":"2013/1","quantidade":1},{"tema":"Habitação de interesse social","periodo":"2013/2","quantidade":7},{"tema":"Habitação de interesse social","periodo":"2014/1","quantidade":4},{"tema":"Habitação de interesse social","periodo":"2014/2","quantidade":3},{"tema":"Habitação de interesse social","periodo":"2015/1","quantidade":6},{"tema":"Habitação de interesse social","periodo":"2015/2","quantidade":1},{"tema":"Habitação de interesse social","periodo":"2016/2","quantidade":1},{"tema":"Habitação de interesse social","periodo":"2017/1","quantidade":1},{"tema":"Requalificação","periodo":"2013/1","quantidade":6},{"tema":"Requalificação","periodo":"2013/2","quantidade":2},{"tema":"Requalificação","periodo":"2014/1","quantidade":4},{"tema":"Requalificação","periodo":"2014/2","quantidade":2},{"tema":"Requalificação","periodo":"2015/1","quantidade":8},{"tema":"Requalificação","periodo":"2015/2","quantidade":2}]';
					info = JSON.parse(decode_utf8(info));
					
					config = config_skeleton();
					config.type = 'line';
					config.options.title.text = document.getElementsByTagName("canvas").id;
					config.options.scales.xAxes[0].scaleLabel.labelString ='';
					config.options.scales.yAxes[0].scaleLabel.labelString ='';
					
					lbs = [];
					for(i = 0 ; i < info.length ; i++) {
						lbs[i] = info[i].periodo;
					}
					lbs = lbs.filter(function(elem,index,self) {return index == self.indexOf(elem);});
					lbs = lbs.sort();
					config.data.labels = lbs;

					ds = [];
					for(i = 0 ; i < info.length ; i++) {
						ds[i] = info[i].tema;
					}
					ds = ds.filter(function(elem,index,self) {return index == self.indexOf(elem);});

					for(i = 0 ; i < ds.length ; i++) {
						bc = ''+randomScalingFactor()+','+randomScalingFactor()+','+randomScalingFactor()+'';
						config.data.datasets[i] = {
								label: ds[i],
								backgroundColor: 'rgb('+cor[4][i]+',0.6)',
								borderColor: 'rgb('+cor[4][i]+')',
								data: [],
								fill: false,
							};
					}

					for(i = 0 ; i < info.length; i++) {
						config.data.datasets[ds.indexOf(info[i].tema)].data[lbs.indexOf(info[i].periodo)] = info[i].quantidade;
					}

					plot_chart("Temas no Total", config);
				}
				if(opt == "g04") {
					clear_chart();
					var info = [{"premios":2,"periodo":"2008/1"},{"premios":4,"periodo":"2008/2"},{"premios":2,"periodo":"2009/1"},{"premios":3,"periodo":"2009/2"},{"premios":1,"periodo":"2010/1"},{"premios":1,"periodo":"2011/1"},{"premios":1,"periodo":"2013/1"},{"premios":1,"periodo":"2013/2"},{"mencoes":1,"premios":2,"periodo":"2014/1"},{"mencoes":1,"premios":1,"periodo":"2014/2"},{"mencoes":1,"periodo":"2015/1"},{"premios":1,"periodo":"2016/1"},{"premios":1,"periodo":"2016/2"}];
					var config = config_skeleton();

					config.type = 'bar';
					config.options.title.text = document.getElementsByTagName("canvas").id;
					config.options.tooltips.mode = 'index';
					config.options.tooltips.intersect = false;
					config.options.scales.xAxes[0].scaleLabel.labelString = 'Períodos';
					config.options.scales.yAxes[0].scaleLabel.labelString = 'Quantidade';
					config.options.scales.xAxes[0].stacked = true;
					config.options.scales.yAxes[0].stacked = true;
					config.data.datasets = [{data:[], backgroundColor:'rgb(100,0,0)', label:'Prêmios'},{data:[], backgroundColor:'rgb(0,100,100)', label:'Menções'}];
					for(i = 0 ; i < info.length ; i++) {
						config.data.labels[i] = info[i].periodo;
					}
					for(i = 0 ; i < info.length ; i++) {
						config.data.datasets[0].data[i] = info[i].premios || 0;
						config.data.datasets[1].data[i] = info[i].mencoes || 0;
					}

					
					config = JSON.parse(decode_utf8(JSON.stringify(config)));

					plot_chart("Temas por Período", config);
				}
			}
		</script>
	</body>
</html>

