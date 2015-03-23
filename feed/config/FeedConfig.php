<?php


class FeedConfig {

    public $feedData;
    public $productsCategory;
	public $productsIds;
	public $productsId;
    public $manufactures ;
    public $product_options;
    public $product_option_values;
	public $productAttributes;
	public $defaultsShipping;
	public $defaultPAvailability;
	public $defaultSCost;
	public $defaultTRate;
	public $storePickup;
	public $taxZone;
	public $perItemCost;
	public $deliveryTime;
	public $shipping;
	public $attToFeed;
	public $productsWithAttributes;
	public $extraAttributes = array();
	public $shippingAttributes = array();
	public static $gReturn = array (
        'ModelOwn'              => 'ModelOwn',
        'Name'                  => 'Name',
        'Subtitle'              => 'Subtitle',
        'Description'           => 'Description',
        'AdditionalInfo'        => 'AdditionalInfo',
        'Image'                 => 'Image',
        'Manufacturer'          => 'Manufacturer',
        'Model'                 => 'Model',
        'Category'              => 'Category',
        'CategoriesGoogle'      => 'CategoriesGoogle',
        'CategoriesYatego'      => 'CategoriesYatego',
        'ProductsEAN'           => 'ProductsEAN',
        'ProductsISBN'          => 'ProductsISBN',
        'Productsprice_brut'    => 'Productsprice_brut',
        'Productspecial'        => 'Productspecial',
        'Productsprice_uvp'     => 'Productsprice_uvp',
        'BasePrice'             => 'BasePrice',
        'BaseUnit'              => 'BaseUnit',
        'Productstax'           => 'Productstax',
        'ProductsVariant'       => 'ProductsVariant',
        'Currency'              => 'Currency',
        'Quantity'              => 'Quantity',
        'Weight'                => 'Weight',
        'AvailabilityTxt'       => 'AvailabilityTxt',
        'Condition'             => 'Condition',
        'Coupon'                => 'Coupon',
        'Gender'                => 'Gender',
        'Size'                  => 'Size',
        'Color'                 => 'Color',
        'Material'              => 'Material',
        'Packet_size'           => 'Packet_size',
        'DeliveryTime'          => 'DeliveryTime',
        'Shipping'              => 'Shipping',
        'ShippingAddition'      => 'ShippingAddition',
        'shipping_paypal_ost'   => 'shipping_paypal_ost',
        'shipping_cod'          => 'shipping_cod',
        'shipping_credit'       => 'shipping_credit',
        'shipping_paypal'       => 'shipping_paypal',
        'shipping_transfer'     => 'shipping_transfer',
        'shipping_debit'        => 'shipping_debit',
        'shipping_account'      => 'shipping_account',
        'shipping_moneybookers' => 'shipping_moneybookers',
        'shipping_giropay'      => 'shipping_giropay',
        'shipping_click_buy'    => 'shipping_click_buy',
        'shipping_comment'      => 'shipping_comment'
	);

	public $base_price;
	public $price;
	public $special;
	public $tax_rate;

	protected $categoryParent;
	protected $categoryPath;

	//the rule is: key->admin panel fields name with prefix FEEDIFY_FIELD_
	//value->name of field which is extracted from db
	//if is necessary to add a new field simply add here a new item and
	//in function getFeedColumnValue set the value to export like this: $oArticle["coupon"]
	protected $parameters = array(
		"EAN" => "ean",
		"ISBN" => "isbn",
		"BASE_UNIT" => "base_unit",
		"UVP" => "uvp",
		"YATEGOO" => "yategoo",
		"PACKET_SIZE" => "packet_size",
		"SUBTITLE" => "subtitle",
		"COLOR" => "color",
		"SIZE" => "size",
		"GENDER" => "gender",
		"MATERIAL" => "material",
		"COUPON" => "coupon",
		"AUTO_MANUFACTURER" => "auto_manufacturer"
	);

	public function __construct()
	{
		$this->_initParameters();
	}

    /**
     * update database
     */
    public function remove()
    {
        $db = $GLOBALS['db'];

        $db->Execute( "
            DELETE FROM " . TABLE_CONFIGURATION . "
            WHERE configuration_key LIKE '%FEED_%'"
        );
    }

    /**
     * save data in database
     */
	public function install()
	{
		$db = $GLOBALS['db'];

		foreach($_POST as $feedifyField => $value) {
			if(strpos($feedifyField,'FEED_') !== false) {
				$db->Execute( "
                    INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value)
                    VALUES ('". $feedifyField ."','" . $value ."' )"
				);
			}
		}
	}

	/**
	 * @param $string
	 * @return string
	 */
	//get the user data from database, example : getConfig('FEEDIFY_PASSWORD')
	public function getConfig($string)
	{
		$db = $GLOBALS['db'];
		$config = $db->Execute( "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '$string' " );

		return $config->fields['configuration_value'];
	}

	/**
	 * @return array
	 */
	//select from db all languages and stock it into array
	public function getLanguagesArray(){
		$db = $GLOBALS['db'];
		$query = $db->Execute( "SELECT languages_id as id, name FROM " . TABLE_LANGUAGES );

		$rez = $this->dataFetch($query);

		return $rez;
	}

	/**
	 * @return array
	 */
	//select from db currencyes and stock it into array
	public function getCurrencyArray()
	{
		$db = $GLOBALS['db'];
		$query = $db->Execute( "SELECT currencies_id as id, title FROM " . TABLE_CURRENCIES );

		$rez = $this->dataFetch($query);

		return $rez;
	}

	/**
	 * @return array
	 */
	public function getTaxZones(){
		$db = $GLOBALS['db'];
		$result = $db->Execute( "SELECT  geo_zone_id, geo_zone_name FROM " .TABLE_GEO_ZONES );

		$rez = array();
		while ( !$result->EOF ) {
			$rez[$result->fields['geo_zone_id']] = $result->fields['geo_zone_name'];
			$result->MoveNext();
		}

		return $rez;
	}

	public function getShopLanguageConfig()
	{
		$oConfig = new stdClass();
		$aLanguages = $this->getLanguagesArray();
		$oConfig->key = "lang";
		$oConfig->title = "Language";
		foreach ($aLanguages as $language) {
			$oValue = new stdClass();
			$oValue->key = $language['id'];
			$oValue->title = $language['name'];
			$oConfig->values[] = $oValue;
		}

		return $oConfig;
	}

	public function getShopAvailabilityConfig()
	{
		$oConfig = new stdClass();
		$aAvailabilities[] = array('id' => '1', 'title' => 'No export inactive and with quantity = 0 products');
		$aAvailabilities[] = array('id' => '2', 'title' => 'Export inactive No export with quantity = 0 products');
		$aAvailabilities[] = array('id' => '3', 'title' => 'No export inactive Export with quantity = 0 products');
		$aAvailabilities[] = array('id' => '4', 'title' => 'Export inactive and with quantity = 0 products');
		$oConfig->key = "availability";
		$oConfig->title = "Availability";
		foreach($aAvailabilities as $oAvailability) {
			$oValue = new stdClass();
			$oValue->key = $oAvailability['id'];
			$oValue->title = $oAvailability['title'];
			$oConfig->values[] = $oValue;
		}

		return $oConfig;
	}

	public function getShopCurrencyConfig()
	{
		$oConfig = new stdClass();
		$aCurrencies =  $this->getCurrencyArray();
		$oConfig->key = "currency";
		$oConfig->title = "Currency";
		foreach($aCurrencies as $oCurrency) {
			$oValue = new stdClass();
			$oValue->key = $oCurrency['id'];
			$oValue->title = $oCurrency['title'];
			$oConfig->values[] = $oValue;
		}

		return $oConfig;
	}

	public function getQueryFields()
	{
		return array(
			'id' => 'id',
			'quantity' => 'quantity',
			'model' => 'model',
			'image' => 'image',
			'price' => 'price',
			'weight' => 'weight',
			'status' => 'status',
			'always_free_shipping' => 'always_free_shipping',
			'master_categories_id' => 'master_categories_id',
			'tax_class_id' => 'tax_class_id',
			'manufacturers_name' => 'manufacturers_name',
			'parent_id' => 'parent_id',
			'language_id' => 'language_id',
			'products_name' => 'products_name',
			'products_description' => 'products_description',
			'currencies_code' => 'currencies_code',
			'currencies_decimal_places' => 'currencies_decimal_places',
			'currencies_value' => 'currencies_value',
			'special_price' => 'special_price',
		);
	}

    public function getProducts($limit, $offset){
        $db = $GLOBALS['db'];
        $select = '
                SELECT
                    p.products_id as products_id,
                    p.products_quantity as products_quantity,
                    p.products_model as products_model,
                    p.products_image as products_image,
                    p.products_price as products_price,
                    p.products_weight as products_weight,
                    p.manufacturers_id as manufacturers_id,
                    p.products_tax_class_id as products_tax_class_id,
                    pd.products_name as products_name,
                    pd.language_id as language_id,
                    pd.products_description as products_description,
                    pd.products_url as products_url,
                    p.products_tax_class_id as tax_class_id

        ';
        $from = ' FROM
                    '.TABLE_PRODUCTS.' p
                  inner join '.TABLE_PRODUCTS_DESCRIPTION.' pd on p.products_id=pd.products_id
        ';
        $dimensions = ' limit '.$limit.'  offset '.$offset;
        $query  = $select.$from.$dimensions;
        $response = $this->dataFetch($db->Execute($query), true);
        $temp = array();

        foreach ($response as $item) {
            $temp[] = $item['products_id'];
        }
        $this->productsId = implode(',',$temp);

        return  $response;
    }


    public function getProductsAttr(){
        $db = $GLOBALS['db'];
        $query  = '
                    select
                        pa.products_attributes_id as products_attributes_id,
                        pa.products_id as products_id,
                        pa.options_id as options_id,
                        pa.options_values_id as options_values_id,
                        pa.options_values_price as options_values_price,
                        pa.price_prefix as price_prefix,
                        pa.products_attributes_weight as attributes_weight,
                        pa.products_attributes_weight_prefix as attributes_weight_prefix
                    from '.TABLE_PRODUCTS_ATTRIBUTES.' pa
                    where pa.products_id in ('.$this->productsId.')

        ';
        $response = $this->dataFetch($db->Execute($query), true);

        $lastProductId = null;
        $idList = array();
        $temp = array();

        foreach ($response[0] as $key=>$value) {
            $temp[$key] = array();
        }

        foreach ($response as $item) {
            if($lastProductId != $item['products_id'] ){
                $idList[] = $item['products_id'];
            }
            $lastProductId = $item['products_id'];
        }
        foreach ($idList as $item) {
            foreach ($response as $attribute) {
                if($item == $attribute['products_id']){
                    foreach ($attribute as $key=>$value) {
                        $temp[$item][$key][] = $value;
                    }
                }
            }
            $temp[$item]['options_list'] = array();
        }
        foreach ($temp as $key=>$item) {
            if(empty($item)){
                unset($temp[$key]);
            }
        }
        $option_var = null;
        $option_array = array();
        foreach ($temp as $key=>$item) {
            foreach ($item['options_id'] as $element) {
                if($element != $option_var){
                    $option_array[$key][] =  (int) $element ;
                }
                $option_var = $element;
            }
            $buff = array();
            foreach ($option_array[$key] as $value) {
                foreach ($temp[$key]['options_id'] as $key2 => $element) {
                    if($value == $element) {
                        $buff[$value][$key2] = $temp[$key]['options_values_id'][$key2];
                    }
                }
            }
            $temp[$key]['options_list'] = $buff ;
            //unset($temp[$key]['options_id']);
        }

        return $temp;
    }

    /**
     * @param $queryParameters
     * @param $limit
     * @param $offset
     * @param $id
     * @return object
     */
    //return data for one or more products, or data from order by Id
    public function getProductsResource($queryParameters = null, $offset = 0, $limit = 0, $id = array()){
        $db = $GLOBALS['db'];

		// integrating fields if they was selected by user
		$queryToAdd = $this->_addToQuery();

        $query = "
        	p.products_id AS id,
			p.products_quantity AS quantity,
			p.products_model AS model,
			p.products_image AS image,
			p.products_price AS price,
			p.products_weight AS weight,
			p.products_status AS status,
			p.product_is_always_free_shipping AS always_free_shipping,
			p.master_categories_id AS master_categories_id,
			p.products_tax_class_id AS tax_class_id,
			m.manufacturers_name AS manufacturers_name,
			c.parent_id AS parent_id,
			pd.language_id AS language_id,
			pd.products_name AS products_name,
			pd.products_description AS products_description,
			sp.specials_new_products_price AS special_price
        ";

		if($queryParameters) {
			$query .= ",
				cr.code AS currencies_code,
				cr.decimal_places AS currencies_decimal_places,
				cr.value AS currencies_value,
				sp.specials_new_products_price AS special_price
        	";
		}

		$query .= $queryToAdd[0];

        $t_query = "SELECT ";

        $t_query .= $query."
        	FROM	" . TABLE_PRODUCTS . " p
			LEFT JOIN " . TABLE_CATEGORIES . " c
			ON (c.categories_id = p.master_categories_id)

			LEFT JOIN " . TABLE_MANUFACTURERS . " m
			ON (p.manufacturers_id = m.manufacturers_id)
		";

		if($queryParameters) {
			$t_query .= "
				LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
				ON (p.products_id = pd.products_id AND pd.language_id = '" . $queryParameters->lang . "' )

				LEFT JOIN " . TABLE_CURRENCIES . " cr
				ON ( cr.currencies_id = ".$queryParameters->currency." )
			";
		} else {
			$t_query .= "
				LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
				ON (p.products_id = pd.products_id)
			";
		}

        $t_query .= "
			LEFT JOIN " . TABLE_SPECIALS . " sp
			ON ( p.products_id = sp.products_id )
        ";

		$t_query .= $queryToAdd[1];

        if( $id ){
            $t_query .= " WHERE p.products_id IN ('".implode("','", $id)."')";
        } else {
            $t_query .= " WHERE p.products_id > 0";
        }

		switch($queryParameters->availability) {
			case 1:
				$t_query .= " AND (p.products_status != 0 AND p.products_quantity != 0) ";
				break;
			case 2:
				$t_query .= " AND p.products_quantity != 0 ";
				break;
			case 3:
				$t_query .= " AND p.products_status != 0 ";
				break;
		}

        if ( $limit  ) { $t_query .= " LIMIT ".$limit; }
        if ( $offset ) { $t_query .= " OFFSET ".$offset; }

        return $this->dataFetch($db->Execute($t_query), true);
    }

    public function getAttributes()
    {
        $attributes = array();

        return array_merge($attributes, $this->_getAllAttributesCombo());
    }

    protected function _getAllAttributesCombo()
    {
        $results = array();
        foreach($this->productAttributes as $product_id => $attributes) {
            $result = array();
            ksort($this->productAttributes[$product_id]);
            foreach ($attributes as $attribute) {
                if (in_array($attribute['products_options_type'], array("0", "2"))) {
                    $this->productAttributes[$product_id]['required'][$attribute['options_id']] = $attribute['options_id'];
                }
            }

            $this->productAttributes[$product_id] = array_merge($this->productAttributes[$product_id], array());
            for ($i = 0; $i < count($this->productAttributes[$product_id]); $i++) {
                $result = array_merge($result, $this->generate($i, array(), array(), $product_id));
            }

            $results[$product_id] = $result;
        }

        return $results;
    }


	protected function _addToQuery()
	{
		$query = array();

		//start checking if tables contain column products_id
		$this->_checkTables($this->parameters);

		foreach($this->parameters as $key => $parameter) {
			if($parameter != 'N' && $parameter !== null) {
				$temp = explode(';',$parameter);
				$query[0] .= ", $key.".$temp[1]." AS $key " ;
				$query[1] .= "
					LEFT JOIN ".$temp[0]." $key
					ON ($key.products_id = p.products_id)
				";
			}
		}

		return $query;
	}

	//function for checking if column products_id exist in tables $table
	protected function _checkTables($tables)
	{
		$db = $GLOBALS['db'];
		$output = array();

		foreach($tables as $key => $table) {
			if($table != 'N' && $table !== null) {
				$tables[$key] = "'".strtok($table, ';')."'";
			} else {
				unset ($tables[$key]);
			}
		}

		if($tables) {
			$query = ("
				SELECT DISTINCT c.column_name, c.table_name FROM information_schema.columns AS c
				WHERE table_name IN ( ".implode(',', $tables)." ) AND TABLE_SCHEMA = '$db->database'
			");

			$result = $db->Execute($query);

			while(!$result->EOF) {
				$output[$result->fields['table_name']][] = $result->fields['column_name'];
				$result->MoveNext();
			}

			foreach($output as $key_1 => $inspector) {
				if(!in_array('products_id', $inspector)) {
					foreach($this->parameters as $key_2 => $parameter) {
						if(strtok($parameter, ';') == $key_1) {
							unset($this->parameters[$key_2]);
						}
					}
				}
			}
		}
	}

    public function generate($index, $attributes, $options, $product_id)
    {
        $attributes[$this->productAttributes[$product_id][$index]['products_attributes_id']] = $this->productAttributes[$product_id][$index]['products_attributes_id'];
        $options[$this->productAttributes[$product_id][$index]['options_id']] = $this->productAttributes[$product_id][$index]['options_id'];
        $withRequired = array_diff($this->productAttributes[$product_id]['required'], $options);
        if (empty($withRequired)) {
            $combinations[] = $attributes;
        } else $combinations = array();

        for ($i = $index + 1; $i < count($this->productAttributes[$product_id])-1; $i++) {
            if ($this->productAttributes[$product_id][$index]['options_id'] != $this->productAttributes[$product_id][$i]['options_id']) {
                $combinations = array_merge($combinations, $this->generate($i, $attributes, $options, $product_id));
            }
        }

        return $combinations;
    }


    public function getProductsAttributes($ids = array(), $products_ids = array())
    {
        $db = $GLOBALS['db'];
        $query = "
            SELECT	pa.products_id AS id,
	        pov.products_options_values_name,

            pa.options_id,
            pa.options_values_price,
            pa.products_attributes_id,
            pa.price_prefix,
            pa.options_values_id,
            pa.attributes_required,
            po.products_options_type,
            pa.products_attributes_weight_prefix AS weight_prefix,
            pa.attributes_image,
            pa.products_attributes_weight,
            po.products_options_name

            FROM	".TABLE_PRODUCTS_ATTRIBUTES." pa

            LEFT JOIN ".TABLE_PRODUCTS_OPTIONS." po
            ON (po.products_options_id = pa.options_id)

            LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
            ON (pa.options_values_id = pov.products_options_values_id)
        ";

        if($ids && $products_ids) {
            $query .= ' WHERE pa.products_attributes_id IN ('.implode(',', $ids).')
                        AND pa.products_id IN ('.implode(',', $products_ids).')';
        }

		if($products_ids && !$ids) {
			$query .= ' WHERE pa.products_id IN ('.implode(',', $products_ids).')';
		}

        $resource = $db->Execute($query);

        $pAttributes = $this->dataFetch($resource);

        if (!$pAttributes) {
            $this->productAttributes = array();
        } else {
            foreach ($pAttributes as $attribute) {
                $this->productAttributes[$attribute['id']][$attribute['products_attributes_id']] = $attribute;
            }
        }

    }

	//get and analyze the shipping parameters and set priority of fields
	public function getFeedifyShippingParameters()
	{
		$db = $GLOBALS['db'];		//database

		$query = "
				SELECT configuration_key, configuration_value
				FROM ".TABLE_CONFIGURATION."
				WHERE configuration_key LIKE '%FEED_SHIPPING%'
			";

		$result = $this->dataFetch($db->Execute($query));

		foreach($result as $key => $item) {
			if(strstr($item['configuration_key'], '1') && $item['configuration_value'] != 'N' && $item['configuration_value'] !== null) {
				$this->defaultsShipping[$item['configuration_key']] = $item['configuration_value'];
			}

			if(strstr($item['configuration_key'], '2') && $item['configuration_value'] != 'N' && $item['configuration_value'] !== null) {
				$this->parameters[$item['configuration_key']] = $item['configuration_value'];
			}

			if(strstr($item['configuration_key'], '3') && $item['configuration_value'] != 'N' && $item['configuration_value'] !== null) {
				$temp = strtolower(substr($item['configuration_key'], 8, -2));
				$this->shippingAttributes[$temp] = $item['configuration_value'];
			}
		}
	}

    public function dataFetch($resource, $setIds = false)
    {
        $output = array(); //if is set parameter $setIds function store ids of fetched data to $this->productsIds
        if($resource->fields) {
            while (!$resource->EOF) {
				if($setIds === true) {
					$this->productsIds[] = $resource->fields['id'];
				}
                $output[] = $resource->fields;
                $resource->MoveNext();
            }
        } else {

            return $output;
        }

        return $output;
    }

    public function getAttributesGroups()
    {
        $db = $GLOBALS['db'];

        $query = "
            SELECT products_options_name, products_options_id
            FROM ".TABLE_PRODUCTS_OPTIONS."
        ";

        $result = $db->Execute($query);

        return $this->dataFetch($result);
    }

	//acceptable keywords format : "'key_1', 'key_2', 'key_3'" !!pay attention at brackets!!
	public function getDatabaseColumns($keywords) {
		$db = $GLOBALS['db'];

		$query = "
			SELECT DISTINCT c.column_name, c.table_name
			FROM information_schema.columns AS c
			WHERE TABLE_SCHEMA = '".$db->database."'
			AND c.table_name IN ($keywords)"
		;
		$result = $this->dataFetch($db->Execute($query));

		return $result;
	}

	protected function _iniExtraAttributesParameters()
	{
		$db = $GLOBALS['db'];

		$query = "
			SELECT configuration_key, configuration_value
			FROM ".TABLE_CONFIGURATION."
			WHERE configuration_key LIKE '%FEED_E%'
		";

		$result = $this->dataFetch($db->Execute($query));

		$fields = array(
			"FEED_EATTRIBUTES_TWIDTH" => 'TyreWidth',
			"FEED_EATTRIBUTES_TPROFILE" => 'TyreProfile',
			"FEED_EATTRIBUTES_TSPEEDINDEX" => 'TyreSpeedIndex',
			"FEED_EATTRIBUTES_TDIAMETER" => 'TyreDiameter',
			"FEED_EATTRIBUTES_TLOADINDEX" => 'TyreLoadIndex',
			"FEED_EATTRIBUTES_TSEASON" => 'TyreSeason',
			"FEED_EATTRIBUTES_TONROAD" => 'TyreOnRoad',
			"FEED_EATTRIBUTES_TOFFROAD" => 'TyreOffRoad',
			"FEED_EFIELD_CONDITON_1" => 'Condition',
			"FEED_EATTRIBUTES_DEPOSIT" => 'Deposit',
			"FEED_EFIELD_HSN_CODE" => 'hsn',
			"FEED_EFIELD_TSN_CODE" => 'tsn',
		);

		foreach($result as $item) {
			if($item['configuration_value'] != 'N' && $item['configuration_value'] !== null) {
				if(sizeof(explode(';', $item['configuration_value'])) == 2) {
					$this->parameters[$item['configuration_key']] = $item['configuration_value'];
				} else {
					$this->extraAttributes[$fields[$item['configuration_key']]]['query'] = $item['configuration_value'];
				}
			}
		}
	}

	protected function _getOrdersAttributes($id)
	{
		$db = $GLOBALS['db'];
		$query = '
            SELECT	pa.products_attributes_id,
            pa.products_id

            FROM	'.TABLE_PRODUCTS_ATTRIBUTES.' pa

            LEFT JOIN '.TABLE_ORDERS_PRODUCTS.' op
            ON (pa.products_id = op.products_id)

            LEFT JOIN '.TABLE_ORDERS_PRODUCTS_ATTRIBUTES.' opa
            ON (pa.options_id = opa.products_options_id AND pa.options_values_id = opa.products_options_values_id)

            WHERE op.orders_id = '.$id.' AND opa.orders_id = '.$id
		;

		$result = $db->Execute($query);

		return $this->dataFetch($result);
	}

	protected function _getOrdersProducts($id, $currency)
	{
		$db = $GLOBALS['db'];
		$query = "
			SELECT	op.final_price AS price,
                    op.products_quantity AS qty,
                    op.products_id AS id,

                    p.products_tax_class_id AS tax_class_id,

                    c.code

            FROM	".TABLE_ORDERS_PRODUCTS." op

            LEFT JOIN ".TABLE_CURRENCIES." c
            ON (c.currencies_id = ".$currency.")

            LEFT JOIN ".TABLE_PRODUCTS." p
            ON (op.products_id = p.products_id)

            WHERE	op.orders_id = ".$id
		;
		$result = $db->Execute($query);

		$output = $this->dataFetch($result);

		return $output;
	}

	/*
	 * function is used to initialize
	 * the shipping parameters and extra attributes
	 * shipping parameters - data from admin feed form
	 * extra attributes - fields with prefix FEEDIFY_EATTRIBUTES from admin feed form
	 */
	protected function _initParameters()
	{
		foreach($this->parameters as $key => $parameter) {
			$this->parameters[$parameter] = $this->getConfig("FEED_FIELD_".$key);
			unset($this->parameters[$key]);
		}

		$this->getFeedifyShippingParameters();
		$this->_iniExtraAttributesParameters();
        $this->setProductsOptions();
        $this->setManufactures();
        $this->setCategories();
        $this->getFeedifyFormData();
	}

    public function getFeedifyFormData(){
        $query = '
                select
                    c.configuration_value as 	configuration_value,
                    c.configuration_key as configuration_key
                from '.TABLE_CONFIGURATION.' c
                where c.configuration_key like "%FEED%"
        ';
        $db = $GLOBALS['db'];
        $temp = $this->dataFetch($db->Execute($query));
        $array = array();
        foreach ($temp as $item) {
            $array[$item['configuration_key']] = $item['configuration_value'];
        }
        $this->feedData = $array ;
    }

    public function setCategories(){
        $query = '
                    select
                        c.categories_id as categories_id ,
                        c.parent_id as parent_id ,
                        cd.categories_name as categories_name

                    from '.TABLE_CATEGORIES.' c
                    inner join '.TABLE_CATEGORIES_DESCRIPTION.' cd  on cd.categories_id=c.categories_id
        ';
        $db = $GLOBALS['db'];
        $temp = $this->dataFetch($db->Execute($query));
        $result = array();
        foreach ($temp as $item) {
            $result[$item['categories_id']] = $item ;
        }
        $this->productsCategory = $result ;
    }

    public function setTaxRate(){
        $query = 'select
                    t.tax_class_id as tax_class_id,
                    t.tax_rate as tax_rate
                  from '.TABLE_TAX_RATES.' t
        ';
        $db = $GLOBALS['db'];
        $this->tax_rate = $this->dataFetch($db->Execute($query));
    }

    public function setManufactures(){
        $query = '
                select
                    m.manufacturers_id as manufacturers_id,
                    m.manufacturers_name as manufacturers_name
                from '.TABLE_MANUFACTURERS.' m
        ';
        $db = $GLOBALS['db'];
        $manufactures  = $this->dataFetch($db->Execute($query));
        $temp = array();
        foreach ($manufactures as $key => $value) {
            $temp[$value['manufacturers_id']] = $value['manufacturers_name'];
        }
        $this->manufactures=$temp;
    }

    public function setProductsOptions(){

        $db = $GLOBALS['db'];

        $select_options = '
                    select
                        po.products_options_id as products_options_id,
                        po.products_options_name as products_options_name,
                        po.products_options_length as products_options_length,
                        po.products_options_size as products_options_size

                    from '.TABLE_PRODUCTS_OPTIONS.' po
        ';

        $result1 = $this->dataFetch($db->Execute($select_options));
        $temp = array();
        foreach ($result1 as $key=>$value) {
            $temp[$value['products_options_id']] = $value ;
        }
        $this->product_options  = $temp;

        $select_options_attributes = '
                    select
                        pov.products_options_values_id as options_values_id,
                        pov.products_options_values_name as options_values_name
                    from '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov
        ';
        $result2  = $this->dataFetch($db->Execute($select_options_attributes));
        $temp1 = array();
        foreach ($result2 as $result) {
            $temp1[$result['options_values_id']] = $result['options_values_name'] ;
        }
        $this->product_option_values = $temp1 ;
    }

//---------------------- functionality part

	/*
	 * functionality part of this function
	 * is to put in csv file all products
	 * and all combinations of product's attributes
	 * and options
	 * generate product's variants
	 */
	public function allComboFeed($result, $attributes, $fieldMap, $queryParameters, $csv_file) {
		if(array_key_exists($result['id'], $attributes)){
			unset($this->productAttributes);
			$this->productAttributes[$result['id']] = $attributes[$result['id']];
			$result['attributes_value'] = $attributes[$result['id']];
			$result['attributes_combo'] = $this->getAttributes();
			$result['attributes_combo'] = $result['attributes_combo'][0];
		}

		$temp = $this->getFeedRow($fieldMap,$result,$queryParameters->lang,$queryParameters->currency);
		fputcsv($csv_file, $temp, ';', '"');

		if( $result['attributes_combo'] && $result['attributes_value'] ) {
			$this->_attributesFeedOrPrint($result, $temp, $csv_file);
		}
	}

	/*
	 * function used for feed one product
	 */
	public function getFeedRow($fieldMap, $oArticle, $Lang, $currency)
	{
		$this->base_price = zen_get_products_base_price($oArticle['id']);
		$this->price 	= zen_get_products_actual_price($oArticle['id'] );
		$this->special	= zen_get_products_special_price($oArticle['id']);
		$this->tax_rate	= zen_get_tax_rate($oArticle['tax_class_id'], $this->taxZone['zone_country_id'], $this->taxZone['zone_id']);

		//put default tax rate if no product's tax
		if (!$this->tax_rate) {
			$this->tax_rate = $this->defaultTRate;
		}

		$row = array();
		foreach($fieldMap as $key => $value) {
			$row[$key] = str_replace(array("\r", "\r\n", "\n"), '', mb_convert_encoding($this->_getFeedColumnValue($value, $oArticle,  $Lang, $key, $currency), 'UTF-8'));
		}

		return $row;
	}

	//function returns products from one order
	//var $tracking is for activate tracking pixel product's id field change
	public function getOrdersProducts($currency, $id, $print = true, $tracking = false)
	{
		$attributesIds = array();
		$productsIds = array();

		$temp_result = array();
		$result = $this->_getOrdersProducts($id, $currency);

		$products = array();
		if(!$result) {
			echo 'Error: Order with id '.$id.' does not exist!';
			return $products;
		} else {
			$attributes = $this->_getOrdersAttributes($id);
			foreach($result as $key=>$item) {
				$temp_result[$item['id']] = $item;
				$productsIds[$item['id']] = $item['id'];
				unset ($result[$key]);
			}

			$result = $temp_result;
			unset($temp_result);

			foreach($attributes as $key=>$attribute) {
				if($attribute['products_id'] == strtok($result[$attribute['products_id']]['id'],'_')) {
					$result[$attribute['products_id']]['id'] .= '_'.$attribute['products_attributes_id'];
				}
				$attributesIds[$attribute['products_attributes_id']] = $attribute['products_attributes_id'];
				unset($attributes[$key]);
			}

			$this->getProductsAttributes($attributesIds, $productsIds);
			$combines = $this->getAttributes();

			foreach($result as $key=>$item) {

				$ids = explode('_',$item['id']);
				unset($ids[0]);

				foreach($combines as $items) {
					foreach($items as $combo) {
						if(!array_diff($ids, $combo) && sizeof(explode('_', $item['id'])) > 1) {
							$result[$key]['id'] = strtok($item['id'],'_').'_'.implode('_', $combo);
						}
					}
				}
			}

			foreach ($result as $item) {
				$product['ModelOwn']  = $item['id'];
				$product['Quantity']  = $item['qty'];
				$product['BasePrice'] = $item['price'];
				$product['Currency']  = $item['code'];
				$product['tax_class_id'] = $item['tax_class_id'];
				$products[] = $product;
			}

			if($products && $print == true) {
				print_r($products);
			} else if(!$products) {
				echo 'Error: Order with id '.$id.' does not exist!';
			}

			//if id field is not default ModelOwn, perform this block!
			$idField = $this->getConfig("FEED_TRACKING_PRODUCTS_ID");
			$enable = $this->getConfig("FEED_TRACKING_PIXEL_STATUS");
			if($enable == "Y" && $idField != "ModelOwn" && $idField !== null && $tracking) {
				foreach($result as $item) {
					$productsIds[] = $item['id'];
				}
				$temp = $this->getProductsResource(0,0,0,$productsIds);
				foreach($temp as $key => $item) {
					$temp = $this->getFeedRow(array($idField => $idField), $item,0,$currency);
                    $products[$key] = $temp[$idField];
					unset ($temp[$key]);
				}

				/*foreach($products as $key => $product) {
					$temp_2 = explode("_", $product['ModelOwn']);
					$temp_2[0] = $temp[strtok($product['ModelOwn'], '_')][$idField];
					$products[$key]['ModelOwn'] = implode('_', $temp_2);
				}*/
			}
			//end performing;

			return $products;
		}
	}

	/*
	 * initialize parameters for
	 * better usage and time economy
	 */
	public function iniParameters()
	{
		$this->_getAttributesParameters();
		$this->defaultPAvailability = $this->getConfig('FEED_FIELD_AVAILABILITY');
		$this->defaultSCost = $this->getConfig('FEED_FIELD_SHIPPING_COST');
		$this->defaultTRate = $this->getConfig('FEED_FIELD_TAX_RATE');
		$this->storePickup  = $this->getConfig('MODULE_SHIPPING_STOREPICKUP_COST');
		$this->taxZone      = $this->_getTaxZone();
		$this->perItemCost  = $this->getConfig('MODULE_SHIPPING_ITEM_COST');
		$this->deliveryTime = $this->_getDeliveryTime();
		$this->shipping     = $this->_initShipping();
		foreach( $this->shipping->modules as $key=>$module){
			$GLOBALS[substr($module, 0, strrpos($module, '.'))]->enabled = true;
		}
	}

	/*
	 * put in csv file
	 * product's variants
	 * or print it if
	 * @param $feed is false
	 */
	protected function _attributesFeedOrPrint($oArticle, $row, $csv_file, $feed = true)
	{
		$attributes = array();

		foreach($this->productAttributes as $item) {
			foreach($item as $attr) {
				$attributes[$attr['products_attributes_id']] = $attr;
			}
		}
		unset($attributes['']);

		$shippingCache = array();
		global $total_weight;

		if($oArticle['attributes_combo'] && $oArticle['attributes_value']) {
			$index = 0;
			foreach($oArticle['attributes_combo'] as $key=>$item) {
				$x = $oArticle['price'];
				$this->productsWithAttributes[$index] = $row;
				$this->productsWithAttributes[$index]['Productsprice_brut'] = round(($x)*$oArticle['currencies_value'], $oArticle['currencies_decimal_places']);
				$this->productsWithAttributes[$index]['ModelOwn'] = $row['ModelOwn'];

				if(is_numeric($row['ModelOwn'])){
					foreach($item as $combine) {

						$shallNotPass = 0; //shall not pas is for restrict the access from other non attributes fields
						foreach($this->attToFeed as $key => $att) {
							foreach($attributes as $attribute) {
								if ($attribute['options_id'] == $att && $attribute['products_attributes_id'] == $combine) {
									$this->productsWithAttributes[$index][$key] = $attribute['products_options_values_name'];
								}
							}
							$shallNotPass ++;
							if($shallNotPass == (sizeof($this->attToFeed)-2)) {
								break;
							}
						}

						$this->productsWithAttributes[$index]['ModelOwn'] .= '_'.$combine;
						$this->productsWithAttributes[$index]['ProductsVariant'] .= '_'.$attributes[$combine]['products_options_name'];

						switch($oArticle['attributes_value'][$combine]['weight_prefix']) {
							case '+':
								$this->productsWithAttributes[$index]['Weight'] += $oArticle['attributes_value'][$combine]['products_attributes_weight'];
								break;
							case '-':
								$this->productsWithAttributes[$index]['Weight'] -= $oArticle['attributes_value'][$combine]['products_attributes_weight'];
								break;
							default:
								$this->productsWithAttributes[$index]['Weight'] += $oArticle['attributes_value'][$combine]['products_attributes_weight'];
								break;
						}

						if($this->productsWithAttributes[$index]['Productspecial']) {

							switch($oArticle['attributes_value'][$combine]['price_prefix']) {
								case '+':
									$this->productsWithAttributes[$index]['Productsprice_brut'] += $oArticle['attributes_value'][$combine]['options_values_price']*$oArticle['currencies_value'];
									break;
								case '-':
									$this->productsWithAttributes[$index]['Productsprice_brut'] -= $oArticle['attributes_value'][$combine]['options_values_price']*$oArticle['currencies_value'];
									break;
								default:
									$temp = $oArticle['attributes_value'][$combine]['options_values_price']*$oArticle['currencies_value'];
									break;
							}
						} else {

							switch($oArticle['attributes_value'][$combine]['price_prefix']) {
								case '+':
									$this->productsWithAttributes[$index]['Productsprice_brut'] += $oArticle['attributes_value'][$combine]['options_values_price']*$oArticle['currencies_value'];
									break;
								case '-':
									$this->productsWithAttributes[$index]['Productsprice_brut'] -= $oArticle['attributes_value'][$combine]['options_values_price']*$oArticle['currencies_value'];
									break;
								default:
									$temp = $oArticle['attributes_value'][$combine]['options_values_price']*$oArticle['currencies_value'];
									break;
							}
						}
					}
				}

				if(isset($temp)) {
					$this->productsWithAttributes[$index]['Productsprice_brut'] += $temp;
				}

				$this->productsWithAttributes[$index]['ProductsVariant'] = substr($this->productsWithAttributes[$index]['ProductsVariant'],1);
				$this->productsWithAttributes[$index]['BasePriceRatio'] = round($this->base_price / $this->productsWithAttributes[$index]['Productsprice_brut'], 4);

				if(!$shippingCache[$this->productsWithAttributes[$index]['Weight']] && ($this->productsWithAttributes[$index]['Weight'] != $oArticle['Weight'])) {
					$this->_addToCartContent($oArticle);
					$_SESSION['cart']->total  = $this->productsWithAttributes[$index]['Productsprice_brut'];
					$_SESSION['cart']->weight = $this->productsWithAttributes[$index]['Weight'];
					$total_weight = $this->productsWithAttributes[$index]['Weight'];
					$shippingCache[$this->productsWithAttributes[$index]['Weight']] = $this->_shippingPriceCalculate($oArticle);
					$this->productsWithAttributes[$index]['Shipping'] = $shippingCache[$this->productsWithAttributes[$index]['Weight']];
				}

				if($this->defaultSCost && !$this->productsWithAttributes[$index]['Shipping']) {
					$this->productsWithAttributes[$index]['Shipping'] = $this->defaultSCost;
				}

				if($feed === true) {
					fputcsv($csv_file, $this->productsWithAttributes[$index], ';', '"');
				}
				unset($this->productsWithAttributes[$index]);
				$index++;
				unset($oArticle['attributes_combo'][$key]);
			}
		}
	}

	protected function _getAttributesParameters()
	{
		$this->attToFeed['Color']    = $this->getConfig("FEED_ATTRIBUTES_COLOR");
		$this->attToFeed['Size']     = $this->getConfig("FEED_ATTRIBUTES_SIZE");
		$this->attToFeed['Gender']   = $this->getConfig("FEED_ATTRIBUTES_GENDER");
		$this->attToFeed['Material'] = $this->getConfig("FEED_ATTRIBUTES_MATERIAL");
		$this->attToFeed = array_merge($this->attToFeed, $this->extraAttributes);
		$this->attToFeed = array_merge($this->attToFeed, $this->shippingAttributes);
		$this->attToFeed['enable_qty_0']     = $this->getConfig("FEED_PQTY_ZERO");
		$this->attToFeed['enable_pstatus_0'] = $this->getConfig("FEED_PSTATUS_ZERO");
	}

	protected function _getTaxZone()
	{
		$db = $GLOBALS['db'];
		$geoZoneId = $this->getConfig('FEED_TAX_ZONE');
		$taxZone = array();

		$zone = $db->Execute('
            SELECT zone_id, zone_country_id
            FROM '.TABLE_ZONES_TO_GEO_ZONES.'
            WHERE geo_zone_id = '.$geoZoneId
		);

		$zone = $this->dataFetch($zone);
		foreach ($zone as $item) {
			$taxZone['zone_id'] = $item['zone_id'];
			$taxZone['zone_country_id'] = $item['zone_country_id'];
		}

		return $taxZone;
	}

	protected function _getDeliveryTime()
	{
		$return = $this->getConfig('FEED_DTIME_FROM').'_'
			.$this->getConfig('FEED_DTIME_TO').'_'
			.$this->getConfig('FEED_DTIME_TYPE');

		return $return;
	}

	protected function _initShipping()
	{
		if (!isset($this->shipping)) {
			require_once (DIR_WS_CLASSES.'shipping.php');
			$this->shipping = new shipping();
		}

		return $this->shipping;
	}

	protected function _getFeedColumnValue($field, $oArticle, $Lang = null)
	{

		switch($field) {
			case 'ModelOwn':
				return $oArticle['id'];
				break;
			case 'Model':
				return $oArticle['model'];
				break;
			case 'ProductsVariant':
				return $oArticle['products_attributes_id'];
				break;
			case 'ProductsEAN':
				return $oArticle['ean'];
				break;
			case 'ProductsISBN':
				return $oArticle['isbn'];
				break;
			case 'Name':
				return $oArticle['products_name'];
				break;
			case 'Subtitle':
				return strip_tags($oArticle['subtitle']);
				break;
			case 'Description':
				return strip_tags($oArticle['products_description']);
				break;
			case 'Manufacturer':
				return $oArticle['manufacturers_name'];
				break;
			case 'Image':
				return $this->_getImage($oArticle['image']);
				break;
			case 'AdditionalInfo':
				return $this->_getLink($oArticle['id']);
				break;
			case 'Category':
				return $this->_getCategory($oArticle['id'],$Lang);
				break;
			case 'YategoCat':
				return $oArticle['yategoo'];
				break;
			case 'Productsprice_brut':
				return $this->_getBrutPrice($oArticle);
				break;
			case 'Productspecial':
				return $this->_getSpecialPrice($oArticle);
				break;
			case 'Weight':
				return $oArticle['weight'];
				break;
			case 'Productstax':
				return $this->tax_rate;
				break;
			case 'Productsprice_uvp':
				return $oArticle['uvp'];
				break;
			case 'BasePriceRatio':
                if (is_numeric($this->base_price) && is_numeric($brutPrice = $this->_getBrutPrice($oArticle)) && $brutPrice > 0){
                    return round($this->base_price/$brutPrice, 4); //4 is for precision
                }
                return '';
                break;
			case 'BasePrice':
				return $this->base_price;
				break;
			case 'BaseUnit':
				return $oArticle['base_unit'];
				break;
			case 'Currency':
				return $oArticle['currencies_code'];
				break;
			case 'Quantity':
				return $oArticle['quantity'];
				break;
			case 'DeliveryTime':
				return $this->deliveryTime;
				break;
			case 'ShippingAddition':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_ADDITION');
				break;
			case 'AvailabilityTxt':
				return $this->_getAvailability($oArticle);
				break;
			case 'Coupon':
				return $oArticle['coupon'];
				break;
			case 'Size':
				return $oArticle['size'];
				break;
			case 'Color':
				return $oArticle['color'];
				break;
			case 'Gender':
				return $oArticle['gender'];
				break;
			case 'Material':
				return $oArticle['material'];
				break;
			case 'Packet_size':
				return $oArticle['packet_size'];
				break;
			case 'Shipping':
				return $this->_checkShippingPrice($oArticle);
				break;
			case 'shipping_paypal_ost':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_PAYPAL_OST');
				break;
			case 'shipping_cod':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_COD');
				break;
			case 'shipping_credit':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_CREDIT');
				break;
			case 'shipping_paypal':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_PAYPAL');
				break;
			case 'shipping_transfer':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_TRANSFER');
				break;
			case 'shipping_debit':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_DEBIT');
				break;
			case 'shipping_account':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_ACCOUNT');
				break;
			case 'shipping_moneybookers':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_MONEYBOOKERS');
				break;
			case 'shipping_click_buy':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_CLICK_BUY');
				break;
			case 'shipping_giropay':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_GIROPAY');
				break;
			case 'shipping_comment':
				return $this->_setShipping($oArticle, 'FEED_SHIPPING_COMMENT');
				break;
			case 'TyreWidth':
                return $oArticle['tyrewidth'];
                break;
			case 'TyreProfile':
                return $oArticle['tyreprofile'];
                break;
			case 'TyreSpeedIndex':
                return $oArticle['tyrespeedindex'];
                break;
			case 'TyreDiameter':
                return $oArticle['tyrediameter'];
                break;
			case 'TyreLoadIndex':
                return $oArticle['tyreloadindex'];
                break;
			case 'TyreSeason':
                return $oArticle['tyreseason'];
                break;
			case 'TyreOnRoad':
                return $oArticle['tyreonroad'];
                break;
			case 'TyreOffRoad':
                return $oArticle['tyreoffroad'];
                break;
			case 'Condition':
				return $this->_getCondition($oArticle);
				break;
			case 'AutoManufacturer':
				return $oArticle['auto_manufacturer'];
				break;
			case 'Tecdoc':
				return $oArticle['FEED_EFIELD_TECDOC'];
				break;
			case 'HsnTsn':
				if($oArticle['FEED_EFIELD_TSN_CODE'] && $oArticle['FEED_EFIELD_HSN_CODE']) {
					return $oArticle['FEED_EFIELD_TSN_CODE'].'_'.$oArticle['FEED_EFIELD_HSN_CODE'];
				}
				break;
			case 'Deposit':
                return $oArticle['deposit'];
                break;

			default:
				if (isset($oArticle[$field])) {
					return $oArticle[$field];
				} else {
					return '';
				} break;
		}
	}

	protected function _getCondition($oArticle)
	{
		if($oArticle['FEED_EFIELD_CONDITON_2']) {

			return $oArticle['FEED_EFIELD_CONDITON_2'];
		} else {

			return $this->extraAttributes['Condition']['query'];
		}
	}

	protected function _getAvailability($oArticle)
	{
		if($this->defaultPAvailability != 'N') {

			return $this->defaultPAvailability;
		} else {

			return $oArticle['status'];
		}
	}

	//key - shipping type name ex: "SHIPPING_PAYPAL_OST"
	protected function _setShipping($oArticle, $key)
	{
		if($oArticle[$key."_2"]) {

			return $oArticle[$key."_2"];
		} else {

			return $this->defaultsShipping[$key."_1"];
		}
	}

	/**
	 *  getImage
	 *
	 * return url for product image
	 *
	 * @param $productImage
	 * @return string
	 */
	protected function _getImage($productImage)
	{
		return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/images/".$productImage;
	}

	protected function _getLink($productsId)
	{
		return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/index.php?main_page=product_info&products_id='.strtok($productsId,'_');
	}

	protected function _checkShippingPrice($row)
	{
		$check = $this->_getDeliveryCost($row);

		if($check) {
			return $check;
		} else {
			$x = $this->defaultSCost;
			$price = round(($x/100*$this->tax_rate+$x)*$row['currencies_value'],$row['currencies_decimal_places']);

			return $price;
		}
	}

	protected function _getDeliveryCost( $row )
	{
		if($row['always_free_shipping'] == '1') { return 0; }

		global $total_weight;
		$temp = $total_weight;
		$total_weight = $row[ 'weight' ];
		require_once ( DIR_WS_CLASSES.'order.php' );

		$this->_addToCartContent($row);

		if( !$this->shipping ) {
			$this->iniParameters();
		}

		if(count($this->shipping->modules) === 1 && $this->shipping->modules[0] === 'storepickup.php') {
			return $this->storePickup;
		}

		$this->order = new order();
		$this->shipping->quote();

		$price = $this->_shippingPriceCalculate($row);
		$total_weight = $temp;

		return $price;
	}

	protected function _addToCartContent($row){
		if( !$_SESSION['cart']) {
			$_SESSION['cart'] = new shoppingCart();
		}

		$_SESSION['cart']->contents = array();
		$_SESSION['cart']->contents[] = array($row['id']);
		$_SESSION['cart']->contents[$row['id']] = array('qty' => (int)1);

	}

	protected function _shippingPriceCalculate($row){
		$price = $this->shipping->cheapest();
		$tax = zen_get_tax_rate($row['tax_class_id'], $this->taxZone['zone_country_id'], $this->taxZone['zone_id']);

		if($_SESSION['customer_id']){
			$_SESSION['cart']->contents = null;
			$_SESSION['cart']->restore_contents();
		} else {
			unset($_SESSION['cart']->contents[$row['products_products_id']]);
		}
		if($price['module'] == 'item'){ return $this->perItemCost * $row['currencies_value'] + $price['cost']; }
		if($price['module'] == 'store_pickup') { return $this->storePickup * $row['currencies_value'] + $price['cost']; }

		if (isset($price['cost']) && !empty($price['cost'])) {
			$x = $price['cost'];
			$price = round(($x/100*$tax+$x)*$row['currencies_value'],$row['currencies_decimal_places']);
		} else {
			$price = 0;
		}

		return $price;
	}

	protected function _getSpecialPrice($row)
	{
		$product_special = $row['special_price'];
		if ( $product_special > 0 ) {

			return round($product_special*
			$row['currencies_value'],$row['currencies_decimal_places']);
		} else {

			return 0;
		}
	}

	protected function _getBrutPrice($row)
	{
		if(!$row['is_attribute']) {
			$row['price'] = $this->price;
		}

		return round(($row['price'])*$row['currencies_value'], $row['currencies_decimal_places']);
	}

	protected function _getCategory($productID,$langID)
	{

		$db = $GLOBALS['db'];
		$sql = "SELECT  categories_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                WHERE products_id = '" .$productID . "' AND categories_id != '0'";
		$category = $db->Execute($sql);

		return $this->_buildCategory($category->fields['categories_id'],$langID);
	}

	protected function _buildCategory($categoryId,$langID)
	{
		$db = $GLOBALS['db'];
		if (isset($this->categoryPath[$categoryId])) {

			return $this->categoryPath[$categoryId];
		} else {
			$category   = array();
			$tmpID = $categoryId;
			while ($this->_getParent($categoryId) != 0 || $categoryId != 0) {
				$cat_select = $db->Execute(
					"SELECT categories_name FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                     WHERE categories_id = '" . $categoryId . "' AND language_id='" . $langID . "'"
				);
				$categoryId = $this->_getParent($categoryId);
				$category[] = $cat_select->fields['categories_name'];
			}
			$categoryPath = '';
			for ($i = count($category); $i > 0; $i--) {
				$categoryPath .= $category[$i-1].' | ';
			}
			$this->categoryPath[$tmpID] = substr($categoryPath, 0, strlen($categoryPath)-2);

			return $this->categoryPath[$tmpID];
		}
	}

	protected function _getParent($catID)
	{
		$db = $GLOBALS['db'];
		$sql = "SELECT parent_id FROM " . TABLE_CATEGORIES . "
                WHERE categories_id = '" . $catID . "'";
		if (isset($this->categoryParent[$catID])) {
			return $this->categoryParent[$catID];
		} else {
			$parent_query = $db->Execute($sql);
			$this->categoryParent[$catID] = $parent_query->fields['parent_id'];

			return $parent_query->fields['parent_id'];
		}
	}


    function allCombinations($arrays)
    {
        $result = array();
        $arrayKeys = array_keys($arrays);
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array);
        for ($i = 0; $i < $size; $i ++)
        {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j ++)
                array_push($result[$i], current($arrays[$j]));
            for ($j = ($sizeIn -1); $j >= 0; $j --)
            {
                if (next($arrays[$j]))
                    break;
                elseif (isset ($arrays[$j]))
                    reset($arrays[$j]);
            }
        }
        $temp = array();
        foreach ($result as $key1=>$item) {
            foreach ($item as $key2=>$element) {
                $temp[$key1][$arrayKeys[$key2]] = $element ;
            }

        }

        return $temp;
    }

    public function uploadCSVfileWithCombinations($csv_file,$product,$attributes,$fieldMap, $shopConfig,$queryParameters){
        $allCombinations = $this->allCombinations($attributes[$product['products_id']]['options_list']);
        $row = array();
       /* foreach($fieldMap as $key => $value) {
            foreach ($attributes as $key1=>$value1) {
                if(array_key_exists($product['products_id'],$attributes) ){
                    foreach ($allCombinations as $combinations) {
                        $row[$key] = $this->getRowElements($value, $attributes, $product, $combinations);
                    }
                } else {
                    $row[$key] = $this->getRowElements($value, $attributes, $product);
                }
            }
        }*/

        if(array_key_exists($product['products_id'],$attributes) ){
            foreach ($allCombinations as $combinations) {
                foreach($fieldMap as $key => $field) {
                    $row[$key] = $this->getRowElements($field, $attributes, $product, $combinations, $shopConfig,$queryParameters);
                }

                var_dump($row);die;
            }
        }

    }
    public function getRowElements($field, $attributes, $product, $combinations = null , $shopConfig, $queryParameters ){
        //var_dump($combinations);die;
        //var_dump($attributes[1]['options_list']);die;


        switch($field){
            case 'ModelOwn'              : {
                return $this->getModelOwn($attributes,$product,$combinations);
            }//
            case 'Name'                  : {
                return $product['products_name'];
            }//
            case 'Subtitle'              : {
                return 1;
            }
            case 'Description'           : {
                return $product['products_description'];
            }//
            case 'AdditionalInfo'        : {
                return $product['products_url'];
            }//
            case 'Image'                 : {
                return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/images/".$product['products_image'];
            }//
            case 'Manufacturer'          : {
                return $this->manufactures[$product['manufacturers_id']];
            }//
            case 'Model'                 : {
                return $product['products_model'];
            }//
            case 'Category'              : {
                return $this->getCategory($product);
            }//
            case 'CategoriesGoogle'      : {
                return 1;
            }
            case 'CategoriesYatego'      : {
                return 1;
            }
            case 'ProductsEAN'           : {
                return 1;
            }
            case 'ProductsISBN'          : {
                return 1;
            }
            case 'Productsprice_brut'    : {
                if($attributes[$product['products_id']]){
                    foreach ($combinations as $combination) {
                        $a = $attributes[$product['products_id']]['options_values_price'][$combination];
                        $b = $attributes[$product['products_id']]['price_prefix'][$combination];
                        $expression = $b.$a;
                        eval( '$result += (' . $expression . ');' );
                        return ((($product['products_price']+$result)*$this->getProductTax($product)) / 100) + ($product['products_price']+$result);
                    }
                }
                return ((($product['products_price'])*$this->getProductTax($product)) / 100) + ($product['products_price']);

            }//
            case 'Productspecial'        : {
                return 1;
            }
            case 'Productsprice_uvp'     : {
                return 1;
            }
            case 'BasePrice'             : {
                if($attributes[$product['products_id']]){
                    foreach ($combinations as $combination) {
                        $a = $attributes[$product['products_id']]['options_values_price'][$combination];
                        $b = $attributes[$product['products_id']]['price_prefix'][$combination];
                        $expression = $b.$a;
                        eval( '$result += (' . $expression . ');' );
                        return $result + $product['products_price'] ;
                    }
                }
                return $product['products_price'];
            }//
            case 'BaseUnit'              : {
                return 1;
            }
            case 'Productstax'           : {
                return $this->getProductTax($product);
            }//
            case 'ProductsVariant'       : {
                return $this->getProductVariants($attributes,$product,$combinations);
            }//
            case 'Currency'              : {
                return $shopConfig->currency[$queryParameters->currency];
            }//
            case 'Quantity'              : {
                return $product['products_quantity'];
            }//
            case 'Weight'                : {
                if($attributes[$product['products_id']]){
                    foreach ($combinations as $combination) {
                        $a = $attributes[$product['products_id']]['attributes_weight'][$combination];
                        $b = $attributes[$product['products_id']]['attributes_weight_prefix'][$combination];
                        $expression = $b.$a;
                        eval( '$result += (' . $expression . ');' );
                        return $result + $product['products_weight'] ;
                    }
                }
                return $product['products_weight'];

            }//
            case 'AvailabilityTxt'       : {
                return $shopConfig->availability[$queryParameters->availability];
            }
            case 'Condition'             : {
                //var_dump($queryParameters, $shopConfig  );die;
                return 1;

            }
            case 'Coupon'                : {
                return 1;

            }
            case 'Gender'                : {
                return 1;

            }
            case 'Size'                  : {
                foreach ($attributes[$product['products_id']]['options_list'] as $key=>$value) {
                    if(strtolower($this->product_options[$key]['products_options_name']) == 'size' ){
                        return $this->product_option_values[$combinations[$key]];
                    }
                }
                return '';
            }//
            case 'Color'                 : {
                foreach ($attributes[$product['products_id']]['options_list'] as $key=>$value) {
                    if(strtolower($this->product_options[$key]['products_options_name']) == 'color' ){
                        return $this->product_option_values[$combinations[$key]];
                    }
                }
                return '';

            }//
            case 'Material'              : {
                foreach ($attributes[$product['products_id']]['options_list'] as $key=>$value) {
                    if(strtolower($this->product_options[$key]['products_options_name']) == 'iron' ){
                        return $this->product_option_values[$combinations[$key]];
                    }
                }
                return '';

            }//
            case 'Packet_size'           : {
                return 1;

            }
            case 'DeliveryTime'          : {
                return 1;

            }
            case 'Shipping'              : {
                return 1;
            }
            case 'ShippingAddition'      : {
                return 1;
            }
            case 'shipping_paypal_ost'   : {
                return 1;
            }
            case 'shipping_cod'          : {
                return 1;
            }
            case 'shipping_credit'       : {
                return 1;
            }
            case 'shipping_paypal'       : {
                return 1;
            }
            case 'shipping_transfer'     : {
                return 1;
            }
            case 'shipping_debit'        : {
                return 1;
            }
            case 'shipping_account'      : {
                return 1;
            }
            case 'shipping_moneybookers' : {
                return 1;
            }
            case 'shipping_giropay'      : {
                return 1;
            }
            case 'shipping_click_buy'    : {
                return 1;
            }
            case 'shipping_comment'      : {
                return 1;
            }
            default:{
                return 1;
            }
        }
    }

    public function getCategory($product){
        $query = '
                    select
                        ptc.categories_id as categories_id

                    from '.TABLE_PRODUCTS_TO_CATEGORIES.' ptc
                    where ptc.products_id='.$product["products_id"] ;

        $db = $GLOBALS['db'];
        $temp = $this->dataFetch($db->Execute($query));
        $buff = array();
        $categories = array();
        foreach ($temp as $item) {
            $buff[$item['categories_id']] = $item;
            $categories[] = $this->productsCategory[$item['categories_id']];
        }

        return $this->getCategoriesParent($categories, null);
    }

    public function getCategoriesParent($categories,$result = null){
        $temp = array();
        $response = 1;
        foreach ($categories as $item) {
            if(!is_array($item)){
                $temp[0] =  $categories ;
                $categories = $temp ;
                break;
            }
        }
        foreach ($categories as $category) {
            if( $result == null ){
                if($category['parent_id'] == 0){
                    $response = $category['categories_name'];
                } else {
                    $response = $this->getCategoriesParent($this->productsCategory[$category['parent_id']], $category['categories_name']);
                }
            } else {
                if($category['parent_id'] == 0){
                    $response = $category['categories_name'].'|'.$result;
                } else {
                    $response = $this->getCategoriesParent($this->productsCategory[$category['parent_id']], $category['categories_name'].'|'.$result);
                }
            }
        }

        return $response ;
    }

    public function getProductTax($product){
        $a = zen_get_tax_rate($product['tax_class_id'], $this->taxZone['zone_country_id'], $this->taxZone['zone_id']);
        if(!$a){
            //return 1 ; //return value from plogin from
        }
        return $a;
    }

    public function getProductVariants($attributes,$product,$combinations){
        //var_dump($this->product_options);die;
        $temp = array();
        foreach ($combinations as $combination) {
           //var_dump($attributes[$product['products_id']]['options_id'][2]);die;
            $temp[] = $this->product_options[$attributes[$product['products_id']]['options_id'][$combination]]['products_options_name'];

        }
        return  implode('|',$temp);
    }

    public function getModelOwn($attributes,$product,$combinations){
        $temp = $product['products_id'];
        $buff = array();
        foreach ($combinations as $combination) {
            $buff[] = $attributes[$temp]['options_id'][$combination].'-'.$attributes[$temp]['options_values_id'][$combination];
        }

        return $temp.'_'.implode('_',$buff);
    }


}

