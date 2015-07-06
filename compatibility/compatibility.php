<?php 

if (!defined('_PS_VERSION_')) {
	exit;
}

class Compatibility extends Module {

	public function __construct()
	{
		$this->name = 'compatibility';
		$this->tab = 'compat';
		$this->version = 1.0;
		$this->author = 'Ismail Aydogmus, Johan Salin';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Compatibility');
		$this->description = $this->l('Module de compatibilié entres produits');
	}

	public function INSTALL() {
		if (parent::INSTALL() == false) {
			return false;
		}
		return parent::INSTALL() && $this->registerHook('Top') &&
		Configuration::updateValue('compatibility', 'Socket');
	}

	public function uninstall() {

		if (!parent::uninstall()) {
            //Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'compatibility`');
		}
		parent::uninstall();
	}

	public function getContent () {
		echo '<div style="position: absolute;top: 50%;left: 50%;">
		<legend>Ajoue de compatibilité : (Si vous voulez en mettre plusieurs, séparez les par une virgule)</legend>
		<form method="post" action "#">
			<label>Caracteristique :</label>
			<input name="featureName" type="text" />
			<input name="envoie" type="submit" value="Envoyer" />
		</form></div>';
		if (isset($_POST['envoie'])) {
			if (!empty($_POST['featureName'])) {
				Db::getInstance()->Execute();
			}
		}
	}

	public function hookTop ($params){
		$compteurDepart = 0;
		$tabProduits = [];
		$tabProduitsName = [];
		$tabIdFeatureValue = [];
		$tabFeatureValue = [];
		$tabProduitsCompatible = [];
		$cart = $params['cart'];
		$produits = $cart->getProducts();
		if (count($produits) > 1) {
			for ($i=0; $i < count($produits); $i++) { 
				array_push($tabProduitsName, $produits[$i]['name']);
			}
			$featureCompatibility = Db::getInstance()->ExecuteS('SELECT value FROM ps_configuration WHERE name = "compatibility"');
			$featureName = $featureCompatibility[0]['value'];
			$featureName = Db::getInstance()->ExecuteS('SELECT id_feature FROM ps_feature_lang WHERE name = "'.$featureName.'"');
			$idFeatureName = $featureName[0]['id_feature'];
			for ($i=0; $i < count($produits); $i++) { 
				array_push($tabProduits, $produits[$i]['id_product']);
			}
			for ($i=0; $i < count($tabProduits); $i++) { 
				$id_feature_value = Db::getInstance()->ExecuteS('SELECT id_feature_value FROM ps_feature_product WHERE id_product = '. $tabProduits[$i] .' AND id_feature = ' . $idFeatureName);
				$id_feature_value = $id_feature_value[0]['id_feature_value'];
				array_push($tabIdFeatureValue, $id_feature_value);
				$featureValue = Db::getInstance()->ExecuteS('SELECT value FROM ps_feature_value_lang WHERE id_feature_value = '. $tabIdFeatureValue[$i] .'');
				$featureValue = $featureValue[0]['value'];
				array_push($tabFeatureValue, $featureValue);
			}
			for ($i=0; $i < count($tabFeatureValue); $i++) { 
				if ($tabFeatureValue[$i] !== null) {
					$compteurDepart = $i;
					break;
				}
			}
			for ($i=$compteurDepart+1; $i < count($tabFeatureValue); $i++) { 
				if ($tabFeatureValue[$compteurDepart] === $tabFeatureValue[$i]) {
					array_push($tabProduitsCompatible, $tabProduitsName[$i]);
				}
			}
			$allProduitsCompatible = implode(", ", $tabProduitsCompatible);
			if (empty($allProduitsCompatible)) {
				return '<p style="color : red;font-size: 1.5em;">Le produit '.$tabProduitsName[$compteurDepart]. ' est compatible avec avec aucun autres produit dans le panier !!!</p>';
			} else {
				return '<p style="color : back;">Le produit '. $tabProduitsName[$compteurDepart] . ' est compatible avec :</p><p style="color : green;">' . $allProduitsCompatible . '</p>' ;
			}
            //return $this->display(__FILE__, 'compatibility.tpl');
		}
	}
}
?>