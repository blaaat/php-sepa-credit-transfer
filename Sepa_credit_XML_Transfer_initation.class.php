<?php 
	class Sepa_credit_XML_Transfer_initation_Transaction
	{
		public $amount;
		public $bic;
		public $iban;
		public $name;
		public $descr;
		
		public function __construct($name, $amount,$iban,$bic,$descr='')
		{
			$this->amount		= $amount;
			$this->bic			= $bic;
			$this->iban			= $iban;
			$this->name			= $name;
			$this->descr		= $descr;
		}
	}
	
	class Sepa_credit_XML_Transfer_initation
	{
		private $_xml;
		private $_transactions = array();
		private $_msgid;
		private $_transid;
		
		private $_org_name 			= null;
		private $_org_iban 			= null;
		private $_org_bic			= null;
		
		private $_batchBooking		= null;
		
		static function validateIBAN($str)
		{
			$iban_land			= array(); 
			$iban_land['NL']	= 18; 
			$iban_land['DE']	= 22;
			$iban_land['BE']	= 16;
			$iban_land['AD'] 	= 24;
			$iban_land['BA'] 	= 20;
			$iban_land['BG'] 	= 22;
			$iban_land['CY'] 	= 28;
			$iban_land['DK'] 	= 18;
			$iban_land['EE']	= 20;
			$iban_land['FO'] 	= 18;
			$iban_land['FI']	= 18;
			$iban_land['FR']	= 27;
			$iban_land['GE']	= 22;
			$iban_land['GI'] 	= 23;
			$iban_land['GR']	= 27;
			$iban_land['GL'] 	= 18;
			$iban_land['HU'] 	= 28;
			$iban_land['IE'] 	= 22;
			$iban_land['IS'] 	= 26;
			$iban_land['IL'] 	= 22;
			$iban_land['IT'] 	= 27;
			$iban_land['HR']	= 21;
			$iban_land['LV'] 	= 21;
			$iban_land['LB'] 	= 28;
			$iban_land['Ll'] 	= 21;
			$iban_land['LT'] 	= 20;
			$iban_land['LU'] 	= 20;
			$iban_land['MK'] 	= 19;
			$iban_land['MT'] 	= 31;
			$iban_land['MC'] 	= 27;
			$iban_land['ME'] 	= 22;
			$iban_land['NO'] 	= 15;
			$iban_land['AT'] 	= 20;
			$iban_land['PL'] 	= 28;
			$iban_land['PT'] 	= 25;
			$iban_land['RO'] 	= 24;
			$iban_land['SM'] 	= 27;
			$iban_land['SA'] 	= 24;
			$iban_land['RS'] 	= 22;
			$iban_land['SK'] 	= 24;
			$iban_land['Sl'] 	= 19;
			$iban_land['ES'] 	= 24;
			$iban_land['CZ'] 	= 24;
			$iban_land['TR'] 	= 26;
			$iban_land['TN'] 	= 24;
			$iban_land['GB'] 	= 22;
			$iban_land['AE'] 	= 23;
			$iban_land['SE'] 	= 24;
			$iban_land['CH'] 	= 21;	
				
			$regexvalid			= preg_match("/^[A-Z]{2,2}[0-9]{2,2}[a-zA-Z0-9]{1,30}$/", $str);
			if(!$regexvalid)
			{
				return false;
			}
			
			// validate country code & length
			$country			= substr($str, 0, 2);
			$check_digits		= substr($str, 2, 2); 
			if(!isset($iban_land[$country]))
			{
				return false;
			}
			
			if(strlen($str) != $iban_land[$country])
			{
				return false;
			}
			
			// calculate IBAN check digits
			$checkstr			= substr($str, 4).substr($str, 0, 2)."00";
			// replace A-Z by numbers
			for($char='A',$num=10;$num<=35;$char++,$num++)
			{
				$checkstr 		= str_replace($char, $num, $checkstr);
			}
			// remove leading
			$checkstr			= ltrim($checkstr,'0');
			// calculate iban mod 97 without using large integers
			$N 					= substr($checkstr, 0, 9);
			$D 					= substr($checkstr, 9); 
			$mod 				= 0; 
			while(1)
			{
				$mod 			= ((int)$N) % 97; 
				
				if(strlen($D) > 0)
				{
					$N 			= $mod.substr($D, 0, 7); 
					$D 			= substr($D, 7); 
				}
				else
				{
					break;
				}
			}
			
			// check digit is 98 - iban mod 97
			$calc_check_digit	= 98-$mod;
			
			// check if check digits match
			if($calc_check_digit != $check_digits)
			{
				return false;
			}
						
			return true;
		}
		static function validateBIC($str)
		{
			$regexvalid			= preg_match("/^[A-Z]{6,6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3,3}){0,1}$/", $str);
			return $regexvalid;
		}

		public function __construct($msgid,$transid=null)
		{
			$this->_xml 			= simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
																<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
																	<CstmrCdtTrfInitn>
																	</CstmrCdtTrfInitn>
																</Document>');
																
			$this->_msgid			= $msgid;
			if($transid)
			{
				$this->_transid		= $transid;
			}
			else
			{
				$this->_transid		= $msgid;
			}
		}
		
		public function setOrganizationName($name)
		{
			$this->_org_name		= $name;
		}
		public function setOrganizationIBAN($iban)
		{
			$this->_org_iban		= $iban;
		}
		public function setOrganizationBIC($bic)
		{
			$this->_org_bic			= $bic;
		}
		public function enableBatchBooking()
		{
			$this->_batchBooking	= true;
		}
		public function disableBatchBooking()
		{
			$this->_batchBooking	= false;
		}
		
		public function addTransaction(Sepa_credit_XML_Transfer_initation_Transaction $t,$group=0)
		{
			if(!isset($this->_transactions[$group])) 
			{
				$this->_transactions[$group] = array();
			}
			
			$this->_transactions[$group][]	= $t;
		}
		
		private function addGroupHeader()
		{
			$header 				= $this->_xml->CstmrCdtTrfInitn->addChild('GrpHdr');
			
			$header->addChild('MsgId',$this->_msgid);
			$header->addChild('CreDtTm',strftime("%FT%T"));
			$NbOfTxs 				= 0;
			foreach($this->_transactions as $trans)
			{
				$NbOfTxs			+= count($trans); 
			}
			$header->addChild('NbOfTxs',$NbOfTxs);
			$InitgPty				= $header->addChild('InitgPty');
			$InitgPty->addChild('Nm',$this->_org_name);
		}
		
		private function addTransactions()
		{
			foreach($this->_transactions as $group => $trans)
			{
				$pmtinf 				= $this->_xml->CstmrCdtTrfInitn->addChild('PmtInf');
				$pmtinf->addChild('PmtInfId',$this->_transid);
				$pmtinf->addChild('PmtMtd','TRF');
				
				if($this->_batchBooking !== null)
				{
					$pmtinf->addChild('BtchBookg',$this->_batchBooking===true?'true':'false');
				}
				
				$pmtinf->addChild('NbOfTxs',count($trans));
				
				$pmtinf->addCHild('ReqdExctnDt',strftime("%F"));
				
				$Dbtr					= $pmtinf->addChild('Dbtr');
				$DbtrAcct				= $pmtinf->addChild('DbtrAcct');
				$DbtrAgt				= $pmtinf->addChild('DbtrAgt');
				
				$Dbtr->addChild('Nm',$this->_org_name); 
				$DbtrAcct->addChild('Id')->addChild('IBAN',$this->_org_iban);
				$DbtrAcct->addChild('Ccy','EUR');
				
				$DbtrAgt->addChild('FinInstnId')->addChild('BIC',$this->_org_bic);
			
				foreach($trans as $key => $t)
				{
					$CdtTrfTxInf			= $pmtinf->addChild('CdtTrfTxInf');
					$CdtTrfTxInf->addChild('PmtId')->addChild('EndToEndId',$this->_transid."-".$group."-".$key);
					$CdtTrfTxInf->addChild('Amt')->addChild('InstdAmt',$t->amount)->addAttribute('Ccy','EUR');
					$CdtTrfTxInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC',$t->bic);
					
					$Cdtr					= $CdtTrfTxInf->addChild('Cdtr');				
					$Cdtr->addChild('Nm',$t->name);
					
					$CdtrAcct				= $CdtTrfTxInf->addChild('CdtrAcct');
					$CdtrAcct->addChild('Id')->addChild('IBAN',$t->iban);
					
					if(strlen($t->descr) > 0)
					{
						$RmtInf					= $CdtTrfTxInf->addChild('RmtInf');
						$RmtInf->addChild('Ustrd',substr($t->descr,0,140)); // maximale lengte 140 karakters
					}
				}
			}
		}
		
		public function build()
		{
			$this->addGroupHeader();
			$this->addTransactions();
		}
		
		public function getXML()
		{
			return $this->_xml->asXML(); 
		}
	}