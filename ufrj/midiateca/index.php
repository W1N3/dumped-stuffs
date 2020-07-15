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
	</head>
	<body>
<?
	headerPage();
?>
<div class="bg-dark text-white jumbotron-fluid">
	<div class="container">
		<div id="carouselApresentação" class="carousel slide" data-ride="carousel">
			<ol class="carousel-indicators">
<?
	$sqlBusca = "SELECT id_projeto,nome_projeto,nome_aluno FROM projetos JOIN alunos ON (projetos.id_aluno=alunos.id_aluno) WHERE (destaque_projeto=1 OR id_projeto IN (SELECT id_projeto FROM projetos_premiacoes)) AND id_projeto IN (SELECT id_projeto FROM projetos_imagens)  ORDER BY RAND()  LIMIT 12;";
	$resultBusca = mysqli_query($conn, $sqlBusca);
	$projetos = array();
if($resultBusca && mysqli_num_rows($resultBusca)){
	while ($dataBusca = mysqli_fetch_array($resultBusca, MYSQLI_ASSOC)) {
	$id_projeto = codify($dataBusca["id_projeto"]);
		$nome_projeto = codify($dataBusca["nome_projeto"]);
		$autor_projeto = codify($dataBusca["nome_aluno"]);
		$imgs = array();
		//imagens do projeto
		$selectImgs = "SELECT id_imagem,nome_imagem FROM imagens WHERE id_imagem IN(SELECT projetos_imagens.id_imagem FROM projetos_imagens WHERE projetos_imagens.id_projeto=$id_projeto) LIMIT 1";
		$resultImgs = mysqli_query($conn, $selectImgs);
		$img_projeto = mysqli_result($resultImgs, 0, 1);
		$projetos[] = array("id"=>$id_projeto,"nome"=>  htmlspecialchars($nome_projeto,ENT_QUOTES),"autor"=>htmlspecialchars($autor_projeto,ENT_QUOTES),"imagem"=>$img_projeto);
	}
}
$ttproj = count($projetos);
$tst = $ttproj%6;
if($tst){
	for($x=0;$x<$tst;$x++){
		array_pop ($projetos);
	}
}

for ($iterator = 0 ; $iterator < count($projetos) ; $iterator++) {
?>

	<li data-target="#carouselApresentação" class="btn btn-sm btn-primary <?= ($iterator==0 ? 'active' : '' )?>" data-slide-to=<?=$iterator?> ></li>

<?
}
?>
	</ol>
	<div class="carousel-inner">
<?
$iterator = 0;
foreach ($projetos as $key => $proj) {
?>

				<div class="carousel-item <?= ($iterator==0 ? 'active' : '' )?>">
					<a href="projetos.php?id=<?=$proj["id"]?>">
						<img class="d-block w-100" src="<?=$proj['imagem']?>" alt="">
						<div class="carousel-caption d-none d-md-block d-lg-block d-xl-block">
							<blockquote class="blockquote text-primary bg-light">
								<p class="h4"><?=$proj["nome"]?></p>
								<p class="h6"><?=$proj["autor"]?></p>
							</blockquote>
						</div>
					</a>
				</div>
<?
	$iterator++;
}
?>

			</div>
			<a class="carousel-control-prev" href="#carouselApresentação" role="button" data-slide="prev">
				<span class="btn btn-primary bg-primary carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</a>
			<a class="carousel-control-next" href="#carouselApresentação" role="button" data-slide="next">
				<span class="btn btn-primary bg-primary carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</a>
		</div>
	</div>
	<hr class="my-4">
	<div class="container">
		<div class="row">
			<div class="col-md">
				<p class="text-justify"><span style='display: inline-block; width: 3em;'></span> O Portal MIDIATECA DA FAU/UFRJ, lançado em 2013, disponibiliza os Trabalhos Finais de Graduação (TFGs) dos últimos anos. Trata-se de uma base de dados de grande valia não apenas para a difusão do acervo, como também para a análise e reflexão teórica da produção recente da FAU. A plataforma permitirá uma visão panorâmica importante da produção do curso, ajudando a identificar tendências e questões no ensino e prática do projeto. </p>
				<p class="text-justify"><span style='display: inline-block; width: 3em;'></span> Os TFGs, implantados desde 1998, são individuais, de tema livre, são submetidos a duas bancas compostas por, no mínimo, três arquitetos e urbanistas – orientador, um docente interno e um externo à FAU. A partir de 2000, o TFG passou a ser desenvolvido ao longo de dois períodos letivos, o que permitiu o aprofundamento e amadurecimento da proposta. Em 2012, as bancas, por sua vez, foram ampliadas para quatro arquitetos e urbanistas, incorporando mais um docente interno. </p>
			</div>
			<div class="col-md">
				<p class="text-justify"><span style='display: inline-block; width: 3em;'></span> Contando com a colaboração de diversos monitores voluntários e bolsistas, o sistema passou por atualizações e, nesta nova fase, pode ser acessado em vários tipos de dispositivos. As consultas podem ser feitas a partir de diversos tópicos – temas, autores, períodos, orientadores, avaliadores, local – enriquecendo e multiplicando as possibilidades de difusão e de aplicação da produção da FAU. Destacam-se diversos trabalhos premiados regional, nacional ou internacionalmente, que atestam o valor do conjunto e a qualidade da formação oferecida na instituição. </p>
				<p class="text-justify"><span style='display: inline-block; width: 3em;'></span> O Portal, viabilizado inicialmente graças a recursos da FAPERJ, vem sendo alimentado constantemente com as novas produções e, em breve, será enriquecido com a recuperação de trabalhos mais antigos armazenados em CDs, DVDs e pranchas impressas. Espera-se incorporar os dados disponíveis na plataforma anterior – Mediateca do TFG – para completar o registro dos trabalhos de TFG da FAU/UFRJ e ampliar a base de dados para outros trabalhos de síntese do curso. </p>
			</div>
		</div>
	</div>
</div>
<?
	footerPage();
?>
	</body>
</html>

