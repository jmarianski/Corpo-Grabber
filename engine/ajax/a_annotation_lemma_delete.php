<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE
 */


class Ajax_annotation_lemma_delete extends CPage {
	var $isSecure = true;
	
 	function checkPermission(){
 		if (hasRole(USER_ROLE_ADMIN) || hasPerspectiveAccess("annotation_lemma"))
 			return true;
 		else
 			return "Brak prawa do edycji.";
	}

	function execute(){
		$lemma_id = intval($_POST['annotation_id']);
		
		if(!$lemma_id){
			throw new Exception("Lemma id not provided.");
		}
		
		DbReportAnnotationLemma::deleteAnnotationLemma($lemma_id);
		return;
	}
}

?>