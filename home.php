<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css"> 
	</head>
	<body>
		<header>
				<center><a class=wi href="http://dbpedia.org/ontology/Stadium"><img src="images/inside.jpg" style="width:400px; height:200px"/></a></center>	
		</header>
		<div class=black></div>
		<div class=menu>
			<center>
				<form action=home.php method=POST>
					<table>
						<tr><td align=right colspan=2>number of stadium to show:<select name="k">
		<?php 
		$k=10;
		for($k=10;$k<51;$k++)
			echo"<option>".$k."</option>";
		?>
						</select>
						</td><td><input type=checkbox name=selectedk /></td></tr>
						<tr><td align=right colspan=2>select a country:<select name=country>
		<?php
		include_once('lib/ARC2.php');
		ini_set('max_execution_time', 300);
		$dbpconfig = array("remote_store_endpoint" => "http://dbpedia.org/sparql",);
		$store = ARC2::getRemoteStore($dbpconfig);
		if ($errs = $store->getErrors())
		{
			echo "<h1>getRemoteSotre error<h1>";
		}
		/*pays qui ont des stadiums telque les stadiums ont ses information necesairement*/
		$query='PREFIX foaf: <http://xmlns.com/foaf/0.1/>
				PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
				PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>			
				PREFIX dbpprop: <http://dbpedia.org/property/>
				SELECT ?s (COUNT(?stadium) AS ?count) 
				WHERE {?stadium a <http://dbpedia.org/ontology/Stadium>;
				<http://dbpedia.org/ontology/location> ?s;
				dbpedia-owl:buildingEndDate ?build;
				rdfs:label ?label;
				dbpedia-owl:owner ?owner;
				foaf:depiction ?u;
				dbpprop:capacity ?capacity.
				?s <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://dbpedia.org/ontology/Country> } 
				group by ?s
				order by desc (?count)';//order de pays qui ont le plus grand nombre des stadiums

/* execute the query */
		$rows = $store->query($query, 'rows');
		if ($errs = $store->getErrors()) 
		{
			echo "Query errors" ;
			print_r($errs);
		}
		foreach($rows as $row)
		{
			foreach(explode("/",$row['s']) as $o)
			{$country=$o;}			//explode and add the last string after '/' to the select 'the name of country'
			echo"<option>".$country."</option>";
		}
		echo"</select></td><td><input type=checkbox name=selectedcountry /></td></tr><tr><td align=right colspan=2>order by minimum capacity(default by maximum)</td><td><input type=checkbox name=selectedminimum /></td><td><input type=submit name=search value=search /></tr></table></form></center></div><div class=black></div>";
		$query = '
 PREFIX foaf: <http://xmlns.com/foaf/0.1/>
  PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>			
  PREFIX dbpprop: <http://dbpedia.org/property/>
  SELECT DISTINCT ?stadium (MAX(?capacity) AS ?cap) (MAX(?comment) AS ?comment) (MAX(?build) AS ?build) (MAX(?u) AS ?u) (MAX(?label) AS ?label) (MAX(?tenant) AS ?tenant) (MAX(?owner) AS ?owner)
  WHERE { ?stadium a dbpedia-owl:Stadium;
a dbpedia-owl:SportFacility;
rdfs:comment ?comment;';
if(isset($_POST['selectedcountry']))$query=$query.'dbpedia-owl:location <http://dbpedia.org/resource/'.$_POST['country'].'>;';
elseif(isset($_GET['country']))$query=$query.'dbpedia-owl:location <http://dbpedia.org/resource/'.$_GET['country'].'>;';
$query=$query.'dbpedia-owl:buildingEndDate ?build;
rdfs:label ?label;
dbpedia-owl:owner ?owner;
foaf:depiction ?u;
dbpprop:capacity ?capacity.
OPTIONAL { ?stadium dbpedia-owl:tenant ?tenant}
FILTER ( datatype(?capacity) = xsd:integer ).
FILTER (langMatches(lang(?comment),"en")).
FILTER (langMatches(lang(?label),"en"))}
group by ?stadium';
if(!isset($_POST['selectedminimum'])&&!isset($_GET['selectedminimum']))$query=$query.' order by desc (?cap)';
else $query=$query.' order by (?cap)';
if(isset($_POST['selectedk']))$query=$query.' limit '.$_POST['k'];
elseif(isset($_GET['selectedk']))$query=$query.' limit '.$_GET['selectedk'];
else $query=$query.' limit 10';
if(isset($_GET['offset']))$query=$query.' offset '.$_GET['offset'];

		
		/* execute the query */
		$rows = $store->query($query, 'rows');
		
		if ($errs = $store->getErrors()) {
			echo "Query errors" ;
			print_r($errs);
		}
		/* display the results in an HTML table */
		echo "<table border='1' width=100%>";
		$i=0;
		foreach( $rows as $row )
		{
				foreach(explode("/",$row['owner']) as $o){$owner=$o;}
				
				if($i%2==0)
				echo"<section>
				<article class=left>";
				else echo"<article class=right>";
				echo "<b><center><p style=\"float:left\"><a href=".$row['stadium'].">".$row['label']."</a></p></b>    capacity:".$row['cap']."<p style=\"float:right\"> build end:".$row['build']."</p></center>
							<br><img width=100% style=\"height:400px\" src=\"".$row['u']."\" onerror=\"this.src='images/alt.jpg'\"/>
					  ".$row['comment']."<br><br><p style=\"float:left\"><b><ul><li><a href=team.php?dbpedia=".$row['owner']."&owner=".$owner.">owner:".$owner."</a></li></ul></b></p>";
				if (array_key_exists('tenant', $row))
				{
					foreach(explode("/",$row['tenant']) as $t){$tenant=$t;}
					echo"<b><ul><li>tenant: <a href=".$row['tenant'].">".$tenant."</a></li></ul></b>";
				}
				echo"</center> ";
				echo"</article>";
				if($i%2!=0) echo"</section><div class=black></div>";
			$i++;
		}
		if($i%2!=0)
			echo"<article class=right></article></section><div class=black></div>";
		?>
		<center>
		<?php
			//preparation des deux a href next et previous telque s'il y a un filtrage dans le page courant le page suivant ou reste dans le meme filtrage
				if(isset($_POST['selectedk']))
					$max=$_POST['k'];
				elseif(isset($_GET['selectedk']))$max=$_GET['selectedk']; 
				else $max=10;
				if(isset($_GET['offset']))
				{
					$href2="<a href=home.php?prev=true";
					$prevoffset=$_GET['offset']-$max;
						if($prevoffset!=0)
							$href2=$href2."&offset=".$prevoffset;
						if(isset($_GET['coutry']))
							$href2=$href2.'&country='.$_GET['country'];
						if(isset($_GET['selectedminimum']))
							$href2=$href2.'&selectedminimum=true';
						if($max!=10)
							$href2=$href2.'&selectedk='.$max;
						$href2=$href2.'><img src=images/previous.jpg style="height:50px;width:70px"/></a>';
						echo $href2." ";
					
				}
				if($i==$max)
				{
					$href="<a href=home.php?offset=";
					if(!isset($_GET['offset']))$href=$href.$max;
					else{
						$nextoffset=$_GET['offset']+$max;
						$href=$href.$nextoffset;
						}
					if($max!=10)$href=$href.'&selectedk='.$max;
					if(isset($_POST['selectedcountry'])){
						$href=$href.'&country='.$_POST['country'];
						}
					elseif(isset($_GET['coutry']))
					{
						$href=$href.'&country='.$_GET['country'];
					}
					if(isset($_POST['selectedminimum'])||isset($_GET['selectedminimum']))
					{
						$href=$href.'&selectedminimum=true';
						
					}
					
					$href=$href.'><img src=images/next.jpg style="height:50px;width:50px"/></a>';
					echo $href;
					
				}
				
				
			
		?>
		</center>
	</body>
</html>