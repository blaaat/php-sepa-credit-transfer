<?php 
	/**
	* Sepa_credit_XML_Transfer_initation Example
	* @author Sander Backus
	*/
	
	include("Sepa_credit_XML_Transfer_initation.class.php"); 
	
	header ("Content-Type:text/xml");  

	$test 		= new Sepa_credit_XML_Transfer_initation("TEST-1"); 	// batch name
	$test->setOrganizationName("TEST BV"); 								// your accountname
	$test->setOrganizationIBAN("NL45INGB001");							// your IBAN
	$test->setOrganizationBIC("INGBNL2A");								// your BIC
	
	// add 3 test transactions
	$test_transaction		= new Sepa_credit_XML_Transfer_initation_Transaction("ML Backus",0.01,"NL94INGB0008746467","INGBNL2A","Test payment"); 
	$test_transaction2		= new Sepa_credit_XML_Transfer_initation_Transaction("ML Backus",0.01,"NL94INGB0008746467","INGBNL2A","Test payment"); 
	$test_transaction3		= new Sepa_credit_XML_Transfer_initation_Transaction("ML Backus",0.01,"NL94INGB0008746467","INGBNL2A","Test payment"); 
	
	// add the first to payment group 'a', second and third to 'b'
	$test->addTransaction($test_transaction,'a');
	$test->addTransaction($test_transaction2,'b');
	$test->addTransaction($test_transaction3,'b');
	
	$test->build();
	print($test->getXML())	
	
	
