<?
/*
 Copyright (C) 2015 AC SOFTWARE SP. Z O.O.
 (p.zygmunt@acsoftware.pl)

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 3
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 
 LIB VAERSION: 0.9
 
 */


class erpc_Contractor {

	var $city;
	var $country;
	var $email1;
	var $email2;
	var $mail3;
	var $houseno;
	var $invoices_last_resp_date;
	var $name;
	var $nip;
	var $orders_last_resp_date;
	var $payments_last_resp_date;
	var $postcode;
	var $region;
	var $regon;
	var $section;
	var $shortcut;
	var $street;
	var $tel1;
	var $tel2;
	var $tel3;
	var $trnlocked;
	var $uptodate;
	var $visible;
	var $www1;
	var $www2;
	var $www3;

}

class erpc_Invoice {

	var $dateofissue;
	var $number;
	var $paid;
	var $paymentmethod;
	var $remaining;
	var $shortcut;
	var $termdate;
	var $totalgross;
	var $totalnet;
	var $uptodate;
	var $visible;
	var $externalordernumber;
}

class erpc_Order {

	var $currency;
	var $dateofcomplete;
	var $dateofissue;
	var $desc;
	var $number;
	var $paymentmethod;
	var $shortcut;
	var $state;
	var $termofcontract;
	var $totalgross;
	var $totalnet;
	var $uptodate;
	var $valuerealized;
	var $discount;
	var $visible;

}

class jExt {
	static function Get($jObj, $key, $nullallowed = true, $keymustexists = true) {
		
		$value = null;
		
		if ( array_key_exists($key, $jObj) ) {
			$value = $jObj->{$key};
			
			if ( !$nullallowed 
				 && !isset($value) ) {
				throw new Exception('JSON: value of key \''.$key.'\' is null');
			}
			
		} else if ( $keymustexists ) {
			throw new Exception('JSON: unknown key \''.$key.'\'');
		}
		
		return $value;
	}
	
	static function GetBoolean($jObj, $key) {
	
		$value = jExt::Get($jObj, $key, true);
		
		if ( !isset($value) ) return false;
		
		if ( !is_bool($value) ) 
			throw new Exception('JSON: value of key \''.$key.'\' is not boolean type');
	
		return $value;
	}
	
	static function GetNumber($jObj, $key, $default = null) {
	
		$value = $default;
		
		if ( $default === null 
			 || array_key_exists($key, $jObj) ) {
			
			$value = jExt::Get($jObj, $key, true);
			
			if ( !isset($value) ) return 0;
			
			if ( !is_numeric($value) )
				throw new Exception('JSON: value of key \''.$key.'\' is not numeric type');
			
		}

		
		return $value;
	}
}

class ra_Status {
  
   var $success;
   var $code;
   var $message;
	
   function __construct($jObj) {
   	   $this->assign($jObj);
   }
   
   function assign($jObj) {
      	try {
      		
   	    	$jObj = jExt::Get($jObj, "status");
   	    	$this->success = jExt::GetBoolean($jObj, "success");
   	    	$this->code = jExt::Get($jObj, "code");
   	    	$this->message = jExt::Get($jObj, "message");
   	    	
     	} catch (Exception $e) {
   		   $this->assign_err($e);
    	}
   }
   
   function assign_err($exception) {
   	   $this->success = false;
   	   $this->code = -1000;
   	   $this->message = $exception->getMessage();
   }
}

class _ra_BaseResult {
	var $status = null;
}

class ra_BaseResult extends _ra_BaseResult {
	
    function __construct($jObj) {
    	   $this->assign($jObj);
    }

    function assign($jObj) {
		$this->status = new ra_Status($jObj);
	}
}

class ra_HelloResult extends _ra_BaseResult {

	var $erp_name;
	var $erp_mfr;
	var $drv_mfr;
	var $drv_ver;
	var $ver_major;
	var $ver_minor;
	var $offline_valitidytime;
	var $online_validitytime;
	var $cap;
	var $auth_type;
	var $dev_regstate;
	var $dev_accessgranted;
	var $srv_instanceid;

    function __construct($jObj) {
    	   $this->assign($jObj);
    }

    function assign($jObj) {
			
		$this->status = new ra_Status($jObj);
		$this->cap = 0;
		$this->dev_regstate = erpc_RemoteAction::STATE_UNREGISTERED;
			
		if ( $this->status->success == true ) {

			try {
				$subObj = jExt::Get($jObj, "erp");
				$this->erp_name = jExt::Get($subObj, "name");
				$this->erp_mfr = jExt::Get($subObj, "mfr");
					
				$subObj = jExt::Get($jObj, "drv");
				$this->drv_mfr = jExt::Get($subObj, "mfr");
				$this->drv_ver = jExt::Get($subObj, "ver");
					
				$subObj = jExt::Get($jObj, "cap");
					
				if ( jExt::GetBoolean($subObj, "RegisterDevice") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_REGISTERDEVICE;
				if ( jExt::GetBoolean($subObj, "Login") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_LOGIN;
				if ( jExt::GetBoolean($subObj, "FetchRecordsFromResult") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_FETCHRECORDSFROMRESULT;
				if ( jExt::GetBoolean($subObj, "FetchDocumentFromResult") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_FETCHDOCUMENTFROMRESULT;
				if ( jExt::GetBoolean($subObj, "Customer_SimpleSearch") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_CUSTOMERSIMPLESEARCH;
				if ( jExt::GetBoolean($subObj, "InvoiceById") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_INVOICEBYID;
				if ( jExt::GetBoolean($subObj, "Invoices") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_INVOICES;
				if ( jExt::GetBoolean($subObj, "Invoice_Items") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_INVOICEITEMS;
				if ( jExt::GetBoolean($subObj, "Invoice_DOC") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_INVOICEDOC;
				if ( jExt::GetBoolean($subObj, "OutstandingPayments") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_OUTSTANDINGPAYMENTS;
				if ( jExt::GetBoolean($subObj, "OrderById") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_ORDERBYID;
				if ( jExt::GetBoolean($subObj, "Orders") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_ORDERS;
				if ( jExt::GetBoolean($subObj, "Order_Items") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_ORDERITEMS;
				if ( jExt::GetBoolean($subObj, "Order_DOC") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_ORDERDOC;
				if ( jExt::GetBoolean($subObj, "IndividualPrices") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_INDIVIDUALPRICES;
				if ( jExt::GetBoolean($subObj, "Articles") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_ARTICLES;
				if ( jExt::GetBoolean($subObj, "Article_SimpleSearch") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_ARTICLESIMPLESEARCH;
				if ( jExt::GetBoolean($subObj, "AddContractor") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_ADDCONTRACTOR;
				if ( jExt::GetBoolean($subObj, "NewInvoice") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_NEWINVOICE;
				if ( jExt::GetBoolean($subObj, "NewOrder") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_NEWORDER;
				if ( jExt::GetBoolean($subObj, "GetDictionary") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_GETDICTIONARY;
				if ( jExt::GetBoolean($subObj, "GetLimits") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_GETLIMITS;
				if ( jExt::GetBoolean($subObj, "GetUserDetails") == true ) $this->cap|=erpc_RemoteAction::SRVCAP_GETUSERDETAILS;

				$subObj = jExt::Get($jObj, "auth");
				$this->auth_type = jExt::Get($subObj, "type", "");

				$subObj = jExt::Get($jObj, "version");
				$this->ver_major = jExt::GetNumber($subObj, "major");
				$this->ver_minor = jExt::GetNumber($subObj, "minor");
					
				$subObj = jExt::Get($jObj, "server");
				$this->srv_instanceid = jExt::Get($subObj, "InstanceId");
					
				$subObj = jExt::Get($jObj, "device");
				$rs = jExt::Get($subObj, "regstate");
				$this->dev_accessgranted = jExt::GetBoolean($subObj, "accessgranted");
				
				$subObj = jExt::Get($jObj, "datavaliditytime");
				$this->online_validitytime = jExt::GetNumber($subObj, "online", 10);
				$this->offline_valitidytime = jExt::GetNumber($subObj, "offline", 1440);
				
					
				if ( $this->ver_major == erpc_RemoteAction::VERSION_MAJOR
						&& $this->ver_minor == erpc_RemoteAction::VERSION_MINOR ) {
							 
							if ( rs == "waiting" ) {
								$this->dev_regstate = erpc_RemoteAction::STATE_WAITING;
							} else if ( rs == "registered" ) {
								$this->dev_regstate = erpc_RemoteAction::STATE_REGISTERED;
							} else {
								$this->dev_regstate = erpc_RemoteAction::STATE_UNREGISTERED;
							}
							 
						} else {
							$this->dev_regstate = erpc_RemoteAction::STATE_VERSIONERROR;
						}
						 
						
							
			} catch (Exception $e) {
				$this->status->assign_err($e);
			}
		}			
	}
}

class ra_UserDetails {
	
	var $name;
	var $defaultwarehouse;

    function __construct($jObj) {
    	   $this->assign($jObj);
    }

	function assign($jObj) {
		$jObj = jExt::Get($jObj, "userdetails");
		$this->name = jExt::Get($jObj, "name");
		$this->defaultwarehouse = jExt::Get($jObj, "defaultwarehouse");
	}
}

class ra_LoginResult extends _ra_BaseResult {

	var $userdetails = null;

    function __construct($jObj) {
    	   $this->assign($jObj);
    }

    function assign($jObj) {
		$this->status = new ra_Status($jObj);
		if ( $this->status->success ) {

			if ( array_key_exists("userdetails", $jObj) ) {
				try {
					$this->userdetails = new ra_UserDetails($jObj);
				} catch (Exception $e) {
					$this->status->assign_err($e);
				}
			} else {
				$this->userdetails = null;
			}
				
		}
	}
}

abstract class ra_DataResult {

	var $name;
	var $resultID;
	var $rowCount;
	var $colCount;
	var $totalRowCount;
	var $position;

    protected $record = array();

	abstract protected function jsonToRecord($jObj) ;

	function recordCount() {
		return $this->record === null || !is_array($this->record) ? 0 : count($this->record);
	}

	function getRecord($idx) {
		return $this->record[$idx];
	}

	function assign($jObj) {

		$this->record = array();
		 
		$this->name = jExt::Get($jObj, "name", true, false);
		$this->position = jExt::GetNumber($jObj, "position", 0);
		$this->rowCount = jExt::GetNumber($jObj, "rowcount", 0);
		$this->colCount = jExt::GetNumber($jObj, "colcount", 0);
		$this->totalRowCount = jExt::GetNumber($jObj, "totalrowcount", 0);
		$this->resultID = jExt::Get($jObj, "resultid", true, false);
			
		if ( array_key_exists("content", $jObj) ) {
			$jArr = $jObj->{"content"};
			foreach ($jArr as $jItem) {
				$this->record[] = $this->jsonToRecord($jItem);
			}
		}		 
	}
}

abstract class ra_DataResults extends _ra_BaseResult {

	private $result = array();
	private $all_count = 0;

	abstract protected function jsonToResult($jObj, $Name);

	function assign($jObj) {
	
		$this->result = array();
		$this->all_count = 0;
		$this->status = new ra_Status($jObj);
	
		if ( $this->status->success ) {
			
			try {
			
				$jArr = jExt::Get($jObj, "results");
				
				foreach($jArr as $jItem) {
						$r = $this->jsonToResult($jItem, jExt::Get($jItem, "name", true, false));
						
						if ( $r !== null ) {
							$this->result[] = $r;
							$this->all_count+=$r->recordCount();
						}

				}
				
			} catch (Exception $e) {
				$this->status->assign_err($e);
			}

		}
	}
				
	function recordCount($fullScope = false) {
		if ( $fullScope === true ) {
			return $this->all_count;
		} else {
			$r = $this->getResult();
			return $r === null ? 0 : $r->recordCount();
		}
	}

	function getRecord($idx, $fullScope = false) {
			
		$r = null;
		foreach ($this->result as $result) 
			if ( $result === null ) {
				break;
			} else {
				if ( $idx < $result->recordCount() ) {
					$r = $result->getRecord($idx);
				} else {
					$idx-=$result->recordCount();
				}
			
				if ( $r !== null ) break;
			}
	
		return $r;
	}

	
	function getResult($idx = 0) {
		return $this->result != null && count($this->result) > 0 ? $this->result[$idx] : null;
	}
	
	function getResultById($Id) {
	
		foreach ($this->result as $val)
			if ( $val == null ) {
				break;
			} else {
				if ( $val->resultID == $Id ) {
					return $val;
				}
			}
	
			return null;
	}
				

}

class ra_DocResult extends _ra_BaseResult {
	
	var $totalsize = 0;
	var $resultID;
	var $data;
	
	function __construct($jObj) {
		$this->assign($jObj);
	}
	
	function assign($jObj) {
		$this->status = new ra_Status($jObj);
		
		if ( $this->status->success ) {
	
			try {
				$this->resultID = jExt::Get($jObj, "resultid");
				$this->totalsize = jExt::GetNumber($jObj, "totalsize");
				$this->data = Base64_Decode(jExt::Get($jObj, "DOC"));
			} catch (Exception $e) {
				$this->assign_err($e);
			}
	
		}
	}
}

class ra_ContractorResult extends ra_DataResult {

	protected function jsonToRecord($jObj) {
			
		$c = new erpc_Contractor();
			
		$c->shortcut = jExt::Get($jObj, "Id", false);
		$c->name = jExt::Get($jObj, "Name");
		$c->nip = jExt::Get($jObj, "VATid");
		$c->regon = jExt::Get($jObj, "Regon");
		$c->region = jExt::Get($jObj, "Region");
		$c->country = jExt::Get($jObj, "Country");
		$c->postcode = jExt::Get($jObj, "PostCode");
		$c->city = jExt::Get($jObj, "City");
		$c->street = jExt::Get($jObj, "Street");
		$c->houseno = jExt::Get($jObj, "StNo");
		$c->tel1 = jExt::Get($jObj, "Phone1");
		$c->tel2 = jExt::Get($jObj, "Phone2");
		$c->tel3 = jExt::Get($jObj, "Phone3");
		$c->email1 = jExt::Get($jObj, "Email1");
		$c->email2 = jExt::Get($jObj, "Email2");
		$c->www1 = jExt::Get($jObj, "WWW1");
		$c->www2 = jExt::Get($jObj, "WWW2");
		$c->www3 = jExt::Get($jObj, "WWW3");
		$Lck = jExt::Get($jObj, "TrnLocked");
		$c->trnlocked = $Lck == "Yes" || $Lck == "1" || $Lck == "Tak";
			
		return $c;
	}
}


class ra_InvoiceResult extends ra_DataResult {

	function jsonToRecord($jObj) {

		$i = new erpc_Invoice();
			
		$i->shortcut = jExt::Get($jObj, "Id", false);
		$i->number = jExt::Get($jObj, "Number");
		$i->dateofissue = jExt::GetNumber($jObj, "DateOfIssue");
		$i->totalnet = jExt::GetNumber($jObj, "TotalNet");
		$i->totalgross = jExt::GetNumber($jObj, "TotalGross");
		$i->remaining = jExt::GetNumber($jObj, "Remaining");
		$i->paid = jExt::GetBoolean($jObj, "Paid");
		$i->paymentmethod = jExt::Get($jObj, "PaymentMethod");
		$i->termdate = jExt::GetNumber($jObj, "PaymentDeadline");
		$i->externalordernumber = jExt::Get($jObj, "ExternalOrderNumber", true, false);

		return $i;
	}


}

class ra_OrderResult extends ra_DataResult {

	function jsonToRecord($jObj) {

		$o = new erpc_Order();

		$o->shortcut = jExt::Get($jObj, "Id", false);
		$o->number = jExt::Get($jObj, "Number");
		$o->dateofissue = jExt::GetNumber($jObj, "DateOfIssue");
		$o->totalnet = jExt::GetNumber($jObj, "TotalNet");
		$o->totalgross = jExt::GetNumber($jObj, "TotalGross");
		$o->paymentmethod = jExt::Get($jObj, "PaymentMethod");
		$o->dateofcomplete = jExt::GetNumber($jObj, "DateOfComplete");
		$o->termofcontract = jExt::GetNumber($jObj, "TermOfContract");
		$o->state  = jExt::Get($jObj, "State");
		$o->desc = jExt::Get($jObj, "Description");
		$o->valuerealized = jExt::GetNumber($jObj, "ValueRealized");

		return $o;

	}

}

class ra_ObjectResults extends ra_DataResults {

	private $class_name;
	var $name;

	function jsonToResult($jObj, $Name) {
			
		if ( $Name == $this->name ) {
			$r = new $this->class_name();
			$r->assign($jObj);
			return $r;
		}
			
		return null;
	}


	function __construct($jObj, $name, $class_name = "unknown") {
		$this->class_name = $class_name;
		$this->name = $name;
		$this->assign($jObj);
	}

}

class ra_FetchedResult extends ra_BaseResult {
	var $jObj;

	function __construct($jObj) {
		$this->assign($jObj);
	}

	function assign($jObj) {
		$this->status = new ra_Status($jObj);
		$this->jObj = null;
			
		if ( $this->status->success ) {
			$this->jObj = $jObj;
		};
	}
}


class erpc_RemoteAction {
	
	const SRVCAP_REGISTERDEVICE             =  0x0000001;
	const SRVCAP_LOGIN                      =  0x0000002;
	const SRVCAP_FETCHRECORDSFROMRESULT     =  0x0000004;
	const SRVCAP_FETCHDOCUMENTFROMRESULT    =  0x0000008;
	const SRVCAP_CUSTOMERSIMPLESEARCH       =  0x0000010;
	const SRVCAP_INVOICEBYID                =  0x0000020;
	const SRVCAP_INVOICES                   =  0x0000040;
	const SRVCAP_INVOICEITEMS               =  0x0000080;
	const SRVCAP_INVOICEDOC                 =  0x0000100;
	const SRVCAP_OUTSTANDINGPAYMENTS        =  0x0000200;
	const SRVCAP_ORDERBYID                  =  0x0000400;
	const SRVCAP_ORDERS                     =  0x0000800;
	const SRVCAP_ORDERITEMS                 =  0x0001000;
	const SRVCAP_ORDERDOC                   =  0x0002000;
	const SRVCAP_INDIVIDUALPRICES           =  0x0004000;
	const SRVCAP_ARTICLES                   =  0x0008000;
	const SRVCAP_ARTICLESIMPLESEARCH        =  0x0010000;
	const SRVCAP_ADDCONTRACTOR              =  0x0020000;
	const SRVCAP_NEWINVOICE                 =  0x0040000;
	const SRVCAP_NEWORDER                   =  0x0080000;
	const SRVCAP_GETDICTIONARY              =  0x0100000;
	const SRVCAP_GETLIMITS                  =  0x0200000;
	const SRVCAP_GETUSERDETAILS             =  0x0400000;
	
	const DICTTYPE_CONTRACTOR_COUNTRY         =  1;
	const DICTTYPE_CONTRACTOR_REGION          =  2;
	const DICTTYPE_CONTRACTOR_PAYMENTMETHODS  =  3;
	const DICTTYPE_NEWORDER_STATE             =  4;
	
	const IPRESULT_ERROR               = 0;
	const IPRESULT_ITEMNOTEXISTS       = 1;
	const IPRESULT_CONTRACTORERROR     = 2;
	const IPRESULT_UNKNOWNPRICE        = 3;
	const IPRESULT_OK                  = 4;
	
	const SRESULTCODE_NONE                                = 0;
	const SRESULTCODE_OK                                  = 1;
	const SRESULTCODE_INTERNAL_SERVER_ERROR               = 2;
	const SRESULTCODE_PARAM_ERROR                         = 3;
	const SRESULTCODE_INVALID_ACCESSKEY                   = 4;
	const SRESULTCODE_LOGIN_INCORRECT                     = 5;
	const SRESULTCODE_INSUFF_ACCESS_RIGHTS                = 6;
	const SRESULTCODE_INVALID_PASSWD_RETYPE               = 7;
	const SRESULTCODE_EMAIL_SEND_ERROR                    = 8;
	const SRESULTCODE_INVALID_ADDRESS_OR_ID               = 9;
	const SRESULTCODE_INVALID_KEY                         = 10;
	const SRESULTCODE_TEMPORARILY_UNAVAILABLE             = 11;
	const SRESULTCODE_NOTEXISTS_OR_INSUFF_ACCESS_RIGHTS   = 12;
	const SRESULTCODE_NOTEXISTS                           = 13;
	const SRESULTCODE_OPERATION_NOT_ALLOWED               = 14;
	const SRESULTCODE_ERROR                               = 15;
	const SRESULTCODE_SERVICEUNAVAILABLE                  = 16;
	const SRESULTCODE_ACCESSDENIED                        = 17;
	const SRESULTCODE_UNKNOWN_ACTION                      = 18;
	const SRESULTCODE_WAIT_FOR_REGISTER                   = 19;
	const SRESULTCODE_ACTION_NOT_AVAILABLE                = 20;
	const SRESULTCODE_CONFIRMATION_NEEDED                 = 21;
	const SRESULTCODE_RESULT_NOT_READY                    = 22;
	
	const VERSION_MAJOR = 3;
	const VERSION_MINOR = 6;
	
	const STATE_UNREGISTERED = 0;
	const STATE_REGISTERED   = 1;
	const STATE_WAITING      = 2;
	const STATE_VERSIONERROR = 3;
	
	private $UDID;
	private $Server;
	private $User;
	private $Password;
	private $Sign;
	private $Name;
	private $verify_ssl = FALSE;
	
	function __construct($Server, $Login, $Password, $UDID = "", $Name = "PHP-CLIENT", $verify_ssl = FALSE) {
		
		if ( strlen($UDID) < 8 ) {
			
			//trigger_error("UDID is not set!", E_USER_WARNING);

			$this->UDID = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', 
					mt_rand(0, 65535),                                                       
					mt_rand(0, 65535), 
					mt_rand(0, 65535), 
					mt_rand(16384, 20479), 
					mt_rand(32768, 49151), 
					mt_rand(0, 65535), 
					mt_rand(0, 65535), 
					mt_rand(0, 65535));

			
		} else {
			$this->UDID = $UDID;
		}
		
		$this->Sign = hash_hmac ("md5", $UDID , "{649EC9FEE0B9}");
		$this->Server = $Server;
		$this->Login = $this->base64url_encode($Login);
		$this->Password = $this->base64url_encode($Password);
		$this->Name = $Name;
		$this->verify_ssl = $verify_ssl;
	}
	
	static function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
	
	function httpPost($Action, $Params = null) {
	
		$p = array(
				'Compress' => 'true',
				'Namespace' => 'erpConnector',
				'UDID' => $this->UDID,
				'Sign' => $this->Sign,
				'Login' => $this->Login,
				'Password' =>	$this->Password,
				'Action' => $Action );
		
		if ( is_array($Params) ) {
			$p = array_merge($p, $Params);
		}
				
		$postdata = http_build_query($p);

	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://' . $this->Server . '/pzWebservice.dll/json');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		
		if ( $this->verify_ssl === FALSE ) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);			
		}

		$content = curl_exec($ch);
		$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		
		curl_close ($ch);
	
		if ( $contentType == 'application/x-compress' ) {
			$content = zlib_decode($content);
		}
	
		//file_put_contents('/tmp/result', $content);		
		return json_decode($content);
	}
	
	function Hello() {
		return new ra_HelloResult($this->httpPost("Hello"));
	}
	
	function Login() {
		return new ra_LoginResult($this->httpPost("Login"));
	}
	
	function RegisterDevice() {
		$p = array("DeviceCaption"=>$this->base64url_encode($this->Name));
		return new ra_BaseResult($this->httpPost("RegisterDevice", $p));
	}
	
	function CustomerSearch($txt, $maxCount = -1, $onlyByShortcut = false) {
		return new ra_ObjectResults($this->httpPost("Customer_SimpleSearch", array('Text'=>$this->base64url_encode($txt), 'MaxCount'=>intval($maxCount, -1), 'OnlyID'=>($onlyByShortcut === TRUE ? "1" : "0"))), "Customers", "ra_ContractorResult");
	}
	
	function fetchRecordsFromResult($ResultId, $From, $Count) {
		return new ra_FetchedResult($this->httpPost("FetchRecordsFromResult", array('ResultID'=>$ResultId,'From'=>intval($From, 0),'MaxCount'=>intval($Count, 1))));
	}
	
	function ra_fetchCustomersFromResult($ResultId, $From, $Count) {
	
		$fr = $this->ra_fetchRecordsFromResult($ResultId, $From, $Count);
		if ( $fr->status->success ) {
			$cr = new ra_ContractorResults($fr->jObj, "");
			if ( $cr->status->success ) {
				return $cr->getResultById($ResultId);
			}
		}
	
		return null;
	}
	
	function Orders($CustomerShortcut, $FromDate = 0, $maxCount = -1) {	
		return new ra_ObjectResults($this->httpPost("Orders", array('CID'=>$this->base64url_encode($CustomerShortcut), 'FromDate'=>intval($FromDate, 0), 'MaxCount'=>intval($maxCount, 0))), "Orders", "ra_OrderResult");
	}
	
	function Invoices($CustomerShortcut, $FromDate = 0, $maxCount = -1) {
		return new ra_ObjectResults($this->httpPost("Invoices", array('CID'=>$this->base64url_encode($CustomerShortcut), 'FromDate'=>intval($FromDate, 0), 'MaxCount'=>intval($maxCount))), "Invoices", "ra_InvoiceResult");
	}
	
	function DOC($Action, $DocType, $DocID, $maxBytesCount = -1) {
		return new ra_DocResult($this->httpPost($Action, array('DocType'=>$DocType,'DocID'=>$this->base64url_encode($DocID), 'MaxBytesCount'=>intval($maxBytesCount))));
	}
	
	function InvoiceDOC($DocID, $maxBytesCount = -1) {
		return $this->DOC("Invoice_DOC", "pdf", $DocID, $maxBytesCount);
	}
	
	
	

};

?>