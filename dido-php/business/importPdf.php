<?php
require_once ("../config.php");
IF (! Utils::checkAjax ()) {
	header ( "location: " . HTTP_ROOT );
	die ();
}
if (count ( $_FILES )) {
	foreach ( $_FILES as $inputKey => $file ) {
		if ($file ['error'])
			die ( json_encode ( array (
					"error" => "Errore nel caricamento del file {$file['name']}" 
			) ) );
		$path_parts = pathinfo($file ['name']);
		$ext = strtoupper($path_parts['extension']);
		$SignatureInspector = new SignatureInspector ();
		$SignatureInspector->load ( $file ['tmp_name'], $ext );
		/*
		 * if(!$SignatureInspector->isPDFA()){
		 * die(json_encode(array("error" => "Il file {$file['name']} non è un
		 * pdf di tipo A")));
		 * }
		 */
		if (isset ( $_POST ['getOnlySignatures'] )) {
			$signatures = $SignatureInspector->getSignatures ();
			if (count ( $signatures ) == 0)
				die ( json_encode ( array (
						"error" => "Nel file {$file['name']} non sono presenti firme digitali" 
				) ) );
			else
				die ( json_encode ( array (
						"signatures" => $signatures 
				) ) );
		}
	}
} else
	die ( json_encode ( array (
			"error" => "Nessun file." 
	) ) );

?>