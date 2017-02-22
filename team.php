<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css"> 
	</head>
	<body>
		<header>
				<center><a class=wi href="http://dbpedia.org/ontology/Stadium"><img src="images/inside.jpg" style="width:400px; height:200px"/></a></center>	
		</header>
		<div class=black></div>
		
	<?php 
	if(isset($_GET['dbpedia']))
	{
		echo"<center><b><a style=\"font-size:25px\" href=".$_GET['dbpedia'].">resource</a></b></center>";
		$db=$_GET['dbpedia'];
		include_once('lib/ARC2.php');
		ini_set('max_execution_time', 300);
		$dbpconfig = array(
				"remote_store_endpoint" => "http://dbpedia.org/sparql",
		);
		
		$store = ARC2::getRemoteStore($dbpconfig);
		
		if ($errs = $store->getErrors()) {
			echo "<h1>getRemoteSotre error<h1>" ;
		}
		$query = '
  PREFIX foaf: <http://xmlns.com/foaf/0.1/>
  PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
  PREFIX dbpprop: <http://dbpedia.org/property/>
		
  SELECT ?coach ?name ?birthday ?pic
  WHERE { <'.$db.'> dbpedia-owl:manager ?coach.
?coach foaf:depiction ?pic.
?coach foaf:name ?name.
OPTIONAL { ?coach dbpedia-owl:birthDate ?birthday }					
	}
limit 1';
		$result = $store->query($query, 'rows');
		$exist=0;
		foreach($result as $r)
		{
			$exist=1;
			echo"<section style=\"height:500px\">";
			echo "<b><center>Coach:<a href=".$r['coach'].">".$r['name']."</a></b></center>
							<br><center><img width=400px style=\"height:400px\" src=\"".$r['pic']."\" onerror=\"this.src='alt.jpg'\"/></center><br>";
			   if (array_key_exists('birthday', $r))
			   		echo"<center>".$r['birthday']."</center>";
				echo"</section><div class=black2>";
		
			//if($r['tenant']!=NULL)echo"<b>tenant</b><ul><li>".$r['tenant']."</li></ul>d";
			//echo"</nav></section><div class=black></div>";
		}
		if($exist==0)echo"<center>sorry,no information about coach</center>";
		$exist2=0;
		$query = '
  PREFIX foaf: <http://xmlns.com/foaf/0.1/>
  PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
  PREFIX dbpprop: <http://dbpedia.org/property/>
		
  SELECT ?player
  WHERE { <'.$db.'> dbpprop:name ?player.
		}';
		
		$result1 = $store->query($query, 'rows');
		$i=0;
		//if (!array_key_exists('player', $result1))echo"<br><br><center>sorry,no information about players check the 'resource' for more info</center>";
		foreach ($result1 as $r1)
		{
			$exist2=1;
			$query = '
  PREFIX foaf: <http://xmlns.com/foaf/0.1/>
  PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
  PREFIX dbpprop: <http://dbpedia.org/property/>
			
  SELECT ?player ?birthday ?pic ?name
  WHERE {<'.$r1['player'].'> foaf:depiction ?pic;
foaf:name ?name.
OPTIONAL { <'.$r1['player'].'> dbpedia-owl:birthDate ?birthday }
		}
LIMIT 1';
			$result = $store->query($query, 'rows');
			foreach($result as $r)
		
		{
			if($i%2==0)
				echo"<section>
				<article class=left>";
				else echo"<article class=right>";
			echo "<b><center>player:<a href=".$r1['player'].">".$r['name']."</a></b></center>
							<br><center><img width=400px style=\"height:400px\" src=\"".$r['pic']."\" onerror=\"this.src='alt.jpg'\"/></center><br>";
				if (array_key_exists('birthday', $r)) echo"<center>".$r['birthday']."</center>";
				echo"</article>";
				if($i%2!=0) echo"</section><div class=black2></div>";
			
			//if($r['tenant']!=NULL)echo"<b>tenant</b><ul><li>".$r['tenant']."</li></ul>d";
		$i++;	//echo"</nav></section><div class=black></div>";
		}
		}
		if($exist2==0)echo"<br><br><center>sorry,no information about players check the 'resource' for more info</center>";
		if($exist==0 && $exist2==0)header('location:'.$_GET['dbpedia']);
	}
	?>
	</body>
	</html>