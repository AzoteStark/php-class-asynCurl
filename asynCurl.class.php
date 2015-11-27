<?php
//////////////////////////////////////////////////////////////////////////////////////////////////////
//																									//
//				 CLASS asynCurl v1 by AzoteStark													//
//							simplification du gestionnaire Multi Curl								//
//																									//
//																									//
//																									//
//																									//
//////////////////////////////////////////////////////////////////////////////////////////////////////
//
//
//
// ======================================== CHANGELOG ===============================================//
//
//
//
//
//
//
// ======================================= AMELIORATION ==============================================//
//
//
//
//
// ========================================= SCRIPT ==================================================//
class asynCurl
{
	//new
	private $ch;
	private $input_arr;
	private $mch;
	private $async;
	public $result;
	
/*

		Constructeur
		
*/
public function __construct($input, $options = array() )
{
	if( $this->Check_Curl_Func() === false && !is_array( $input ) ) return false;
	$this->Init( $input );
	$this->Set_Option( $options );
	$this->Init_Multi();
	$this->Async_Exec();
	$this->Resultat();
	$this->Close_All();
}	
	
/*
	
	Verifie que les function de base de Curl soit chargé
	
*/
private function Check_Curl_Func() 
{ 
	if( !function_exists("curl_init") && 
		!function_exists("curl_setopt") && 
		!function_exists("curl_exec") && 
		!function_exists("curl_close") ) return false; 
	else return true; 
} 	
	
/*
	
		Création des ressources cURL
			array( 'titre' => lien, ...)
	
*/	
private function Init( $input_arr )
{
	$this->input_arr = $input_arr;
	foreach( $this->input_arr as $titre => $lien )
	{
		$this->ch[$titre] = curl_init();
	}
}

/*
	
	Option 'curl_setopt'
	
*/
private function Set_Option( $options )
{
	foreach( $this->input_arr as $titre => $lien )
	{
		// L'URL à récupérer
		curl_setopt( $this->ch[$titre], CURLOPT_URL, $lien );
		
		// inclure l'en-tête dans la valeur de retour
		curl_setopt($this->ch[$titre], CURLOPT_HEADER, 0);

		curl_setopt($this->ch[$titre], CURLOPT_RETURNTRANSFER, 1);
			 	
		foreach($options as $option=>$value) {
			curl_setopt($this->ch[$titre], $option, $value);
		}
	}
}

/*
	
		Création du gestionnaire multiple
	
*/
private function Init_Multi()
{
	$this->mch = curl_multi_init();
	
	// Ajoute les deux gestionnaires
	foreach( $this->ch as $titre => $v)
	{
		curl_multi_add_handle($this->mch, $this->ch[$titre]);
	}
	
}

/*
	
		Exécute le gestionnaire
	
*/
private function Async_Exec()
{
	$active = null;
	// Exécute le gestionnaire
	do {
    		$this->async = curl_multi_exec($this->mch, $active);
    		curl_multi_select($this->mch);
	} while ($active > 0);
}	

/*
	
		Résultat
	
*/
private function Resultat()
{
	foreach ($this->ch as $titre => $ch) 
	{
        $this->result[$titre] = curl_multi_getcontent($ch); // Contenue
	}
}

/*
	
		Ferme les gestionnaires
	
*/
public function Close_All()
{
	foreach( $this->ch as $titre => $v)
	{
		curl_multi_remove_handle($this->mch, $this->ch[$titre]);
	}	
	curl_multi_close($this->mch);
}

/*

	Nettoie les <script>
	
*/
static function CleanJS( $page )
{
	if( !empty($page)){
	$dom = new DOMDocument();
	
	libxml_use_internal_errors(true);
	
	$dom->loadHTML($page);
	
	libxml_clear_errors();
	
	$script = $dom->getElementsByTagName('script');
	
	$remove = [];
	
	foreach($script as $item) $remove[] = $item;

	foreach ($remove as $item) $item->parentNode->removeChild($item); 

	return $dom->saveHTML();
	}
}

/*

	Nettoie les <!-- Commentaires -->
	
*/
static function CleanCom( $page )
{
	return preg_replace("/<!--.*?-->/ms","",$page);
}

}  //Fin class
?>
