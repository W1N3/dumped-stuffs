<?php
include ("./_webtool/includes/funcoes.php");
include ("./includes/htmlActions.php");

$id = empty($_REQUEST["id"])?"-1":$_REQUEST["id"];
$temas = array();
$imgs = array();
$premios = array();
$banca = array();
$busca = filter_input(INPUT_GET, "busca", FILTER_SANITIZE_SPECIAL_CHARS);
$pg = filter_input(INPUT_GET, "pg", FILTER_SANITIZE_SPECIAL_CHARS);

$nome_projeto = "Projeto Inexistente";
$ano_projeto = $autor_projeto = $orientador_projeto = $orientador_projeto_lattes = $coorientador_projeto =  $coorientador_projeto_lattes = $sinopse_projeto = $latitude_projeto = $longitude_projeto = "...";


//informações gerais do projeto
$selectProj = "SELECT W.*, P.latitude_projeto, P.longitude_projeto FROM detalhes_projeto as W INNER JOIN projetos as P ON P.id_projeto = W.id_projeto WHERE W.id_projeto = $id";
$resultProj = mysqli_query($conn, $selectProj);

if(mysqli_num_rows($resultProj)){
	$dataProj = mysqli_fetch_array($resultProj, MYSQLI_ASSOC);
	$id_projeto = $dataProj["id_projeto"];
	$nome_projeto = codify($dataProj["nome_projeto"]);
	$ano_projeto = $dataProj["ano_projeto"]."/".$dataProj["periodo_projeto"];
	$bsc_ano_projeto = $dataProj["ano_projeto"]."|".$dataProj["periodo_projeto"];
	$autor_projeto = codify($dataProj["nome_aluno"]);
	$orientador_projeto = codify($dataProj["professor_orientador"]);
	$id_orientador_projeto = $dataProj["id_professor_orientador"];
	$orientador_projeto_lattes = codify($dataProj["professor_orientador_lattes"]);
	$coorientador_projeto = codify($dataProj["professor_coorientador"]);
	$id_coorientador_projeto = codify($dataProj["id_professor_coorientador"]);
	$coorientador_projeto_lattes = codify($dataProj["professor_coorientador_lattes"]);
	$sinopse_projeto = codify($dataProj["sinopse_projeto"]);
	$latitude_projeto = codify($dataProj["latitude_projeto"]);
	$longitude_projeto = codify($dataProj["longitude_projeto"]);
	//temas do projeto
	$selectTema = "SELECT tm.id_tema, IF(tm.id_tema_pai IS NULL, nome_tema, CONCAT((SELECT tm1.nome_tema FROM temas as tm1 WHERE tm1.id_tema=tm.id_tema_pai),' - ',tm.nome_tema)) as nm_tema FROM temas as tm WHERE tm.id_tema IN (SELECT id_tema FROM projetos_temas WHERE projetos_temas.id_projeto = $id)";
	$resultTema = mysqli_query($conn, $selectTema);
	while($dataTema = mysqli_fetch_array($resultTema, MYSQLI_ASSOC)){
		$temas[] = array($dataTema["id_tema"],codify($dataTema["nm_tema"]));
	}
	//imagens do projeto
	$selectImgs = "SELECT imagens.id_imagem,imagens.nome_imagem,imagens.legenda_imagem FROM imagens JOIN projetos_imagens ON projetos_imagens.id_imagem = imagens.id_imagem  WHERE projetos_imagens.id_projeto=$id ORDER BY projetos_imagens.ordem_imagem, projetos_imagens.id_imagem";
	$resultImgs = mysqli_query($conn, $selectImgs);
	while($dataImg = mysqli_fetch_array($resultImgs, MYSQLI_ASSOC)){
		$imgs[] = array($dataImg["id_imagem"],codify($dataImg["nome_imagem"]),codify($dataImg["legenda_imagem"]));
	}

	$selectPremios = "SELECT projetos_premiacoes.`ano_projeto_premiacao`,premiacoes.`nome_premiacao` FROM projetos_premiacoes JOIN premiacoes ON premiacoes.id_premiacao=`projetos_premiacoes`.`id_premiacao` WHERE projetos_premiacoes.`id_projeto`=$id";
	$resultPremios = mysqli_query($conn, $selectPremios);
	while($dataPremios = mysqli_fetch_array($resultPremios, MYSQLI_ASSOC)){
		$premios[] = codify($dataPremios["nome_premiacao"])."/".$dataPremios["ano_projeto_premiacao"];
	}
	
	$selectBanca = "SELECT DISTINCT id_professor,nome_professor FROM professores WHERE id_professor IN (SELECT id_professor FROM convidado_banca_projeto WHERE id_projeto=$id) ORDER BY nome_professor";
	$resultBanca = mysqli_query($conn, $selectBanca);
	while($dataBanca = mysqli_fetch_array($resultBanca, MYSQLI_ASSOC)){
		$banca[] = array($dataBanca["id_professor"],codify($dataBanca["nome_professor"]));
	}    
}
$seoTIT = "Midiateca FAU-UFRJ - Projetos".(empty($nome_projeto)?"":" - ".$nome_projeto);
$seoURL = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$IMG = count($imgs)?$imgs[0][1]:"imagens/md_no_foto.jpg";
$seoIMG =  "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/returnImg.php?imagem=".$IMG."&w=330&h=330&trim=1&rnd=".rand();
$seoIMGTW =  "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/returnImg.php?imagem=".$IMG."&w=60&h=60&trim=1&rnd=".rand();
$seoDESC = empty($sinopse_projeto)?"Midiateca FAU-UFRJ. Projeto não encontrado":substr(strip_tags($sinopse_projeto), 0, 290);
?>
<html>
	<head>
<?
	scriptsAndCSS();
?>
		<meta property="og:title" content="<?=$seoTIT?>" />
		<meta property="og:url" content="<?=$seoURL?>" />
		<meta property="og:image" content="<?=$seoIMG?>"/>
		<meta property="og:description" content="<?="$seoDESC..."?>" />
		<meta property=”og:type” content=”article”/>
		<meta name=”twitter:title” content="<?=$seoTIT?>"/>
		<meta name=”twitter:card” content=”summary”/>
		<meta name=”twitter:url” content=”<?=$seoURL?>”/>
		<meta name=”twitter:description” content="<?=substr($seoDESC, 0, 190)."..."?>"/>
		<meta name="twitter:image" content="<?=$seoIMGTW?>"/>
		<meta name="description" content="<?=substr($seoDESC, 0, 155)."..."?>"/>
		<title><?=$seoTIT?></title>
	</head>
	<body>
<?
	headerPage();
?>
		<div class="bg-dark jumbotron-fluid">
			<div class="container justify-content-center">
				<div class="text-primary pt-2 mb-2">
					<ul class="list-group">
						<li class="list-group-item">
							<h5 class="text-center">
								<?=$nome_projeto?>
								<a class="badge badge-primary" href="pesquisa.php?busca=<?=$busca.$lstfiltrs?>&pg=<?=$pg?>">
									retornar à pesquisa
								</a>
							</h5>
						</li>
					</ul>
				</div>
<?
if (count($imgs)){
?>
				<div id="carouselProjetos" class="mb-2 carousel slide" data-ride="carousel">
	<?if(count($imgs)>1) {?>
					<ol class="carousel-indicators">
<?
	$index=0;
	foreach ($imgs as $img) {
		$img_nome = $img[1];
		$img_leg = $img[2];

		echo '<li data-target="#carouselProjetos" data-slide-to="'.$index.'" class="btn btn-sm btn-primary '.($index==0?'active':'').'"></li>';

		$index++;
	}
?>
					</ol>
	<?}?>
					<div class="carousel-inner">
<?
	$index=0;
	foreach ($imgs as $img) {
		$img_nome = $img[1];
		$img_leg = $img[2];

		echo '<div class="carousel-item '.($index==0?'active':'').'"><img class="d-block w-100" src="./returnImg.php?imagem='.$img_nome.'&w=800&h=600&trim=1&rnd='.rand().'" alt="Slide '.$index.'"></div>';

		$index++;
	}
?>
					</div>
	<?if(count($imgs)>1) {?>
					<a class="carousel-control-prev" href="#carouselProjetos" role="button" data-slide="prev">
						<span class="btn btn-primary bg-primary carousel-control-prev-icon" aria-hidden="true"></span>
						<span class="sr-only">Previous</span>
					</a>
					<a class="carousel-control-next" href="#carouselProjetos" role="button" data-slide="next">
						<span class="btn btn-primary bg-primary carousel-control-next-icon" aria-hidden="true"></span>
						<span class="sr-only">Next</span>
					</a>
	<?}?>
				</div>
<?
} else {
				//echo '<li data-target="#carouselProjetos" data-slide-to="0" class="active"></li></ol>';
				//echo '<div class="carousel-item active"><img class="d-block w-100" src="imagens/md_no_foto.jpg" alt="Sem Imagem"></div></div>';
}
?>
				<div class="pb-2">
					<ul class="list-group">
						<li class="list-group-item">
							<div class="d-flex justify-content-between">
								<span class='text-primary'>Autor</span>
								<span class='text-right text-dark'><?=$autor_projeto?></span>
							</div>
						</li>

						<?if($ano_projeto!="...") {?>
						<li class="list-group-item">
							<div class="d-flex justify-content-between">
								<span class="text-primary">Ano</span>
								<a class='text-right text-dark' href=<?echo 'pesquisa.php?flt_ano_d='.$bsc_ano_projeto.'';?>><?=$ano_projeto?></a>
							</div>
						</li>
						<?}?>

						<?if(count($temas)){?>
						<li class="list-group-item">
							<div class="d-flex justify-content-between">
								<span class="text-primary">Tema(s)</span>
								<div class="text-right">
									<?

											$lst_temas = array();
											foreach ($temas as $tema) {
												$id_tema = $tema[0];
												$nm_tema = $tema[1];
												$lst_temas[] = "<a class='text-dark' href='pesquisa.php?flt_temas[]=$id_tema'>$nm_tema</a>";
											}
										?>
									<?=join(", ", $lst_temas)?>
								</div>
							</div>
						</li>
						<?}?>

						<?if($latitude_projeto && $latitude_projeto!="..." && $longitude_projeto && $longitude_projeto!="..."){?>
						<li class="list-group-item">
							<div class="d-flex justify-content-between">
									<span class="text-primary">Localização</span>
									<a class='text-right text-warning' href="map.php?id=<?=$id_projeto?>"><?=$latitude_projeto?>, <?=$longitude_projeto?></a>
							</div>
						</li>
						<?}?>

						<?if($orientador_projeto!="...") {?>
						<li class="list-group-item">
							<div class="d-flex justify-content-between">
								<span class="text-primary">Orientador</span>
								<a class='text-right text-dark' href='pesquisa.php?flt_orientador=$id_orientador_projeto'><?=$orientador_projeto?></a>
							</div>
						</li>
						<?}?>

						<?if($coorientador_projeto!="..." && $coorientador_projeto!="") {?>
							<li class='list-group-item'>
								<div class='d-flex justify-content-between'>
									<span class='text-primary'>Coorientador</span>
									<a class='text-right text-dark' href='pesquisa.php?flt_orientador=$id_coorientador_projeto'><?=$coorientador_projeto?></a>
								</div>
							</li>
						<?}?>

						<?if(count($banca)) {?>
						<li class="list-group-item">
							<div class="d-flex justify-content-between">
								<span class="text-primary">Banca</span>
								<div class="text-right">
									<?

										$lst_banca = array();
										foreach ($banca as $prof) {
											$id_prof = $prof[0];
											$nm_prof = $prof[1];
											$lst_banca[] = "<a class='text-dark' href='pesquisa.php?flt_banca=$id_prof'>$nm_prof</a>";
										}
									?>
									<?=join(", ", $lst_banca)?>
								</div>
							</div>
						</li>
						<?}?>

						<?if(count($premios)) {?>
							<li class='list-group-item'>
								<div class='d-flex justify-content-between'>
									<span class='text-warning'>Premiações</span>
									<span class='text-right text-success'><?=join(", ", $premios)?></span>
								</div>
							</li>
						<?}?>

						<?if((!isset($sinopse_projeto) || trim($sinopse_projeto)==='')==false) {?>
						<li class="list-group-item">
							<div class="d-flex justify-content-center">
								<p class="text-primary text-center">
									Resumo
								</p>
							</div>
							<p class="text-justify text-dark">
								<span style='display: inline-block; width: 3em;'></span><?=$sinopse_projeto?>
							</p>
						</li>
						<?}?>
					</ul>
				</div>
			</div>
		</div>
<?
	footerPage();
?>
	</body>
</html>

